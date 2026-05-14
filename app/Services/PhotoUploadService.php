<?php

namespace App\Services;

use App\Jobs\GenerateThumbnailJob;
use App\Models\UploadedPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PhotoUploadService
{
    private const DISK = 'local';
    private const DIRECTORY = 'photos';
    private const MAX_TOTAL_PHOTOS = 5000;

    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    public function store(UploadedFile $file, string $ip, ?string $userAgent): UploadedPhoto
    {
        if (UploadedPhoto::count() >= self::MAX_TOTAL_PHOTOS) {
            throw new RuntimeException('Album capacity reached.');
        }

        $mime = $file->getMimeType();
        if (! isset(self::MIME_EXTENSIONS[$mime])) {
            throw new RuntimeException('Unsupported mime type.');
        }
        $extension = self::MIME_EXTENSIONS[$mime];
        $storedName = Str::uuid()->toString().'.'.$extension;

        $path = Storage::disk(self::DISK)->putFileAs(
            self::DIRECTORY,
            $file,
            $storedName
        );

        if ($path === false) {
            throw new RuntimeException('Failed to store uploaded file.');
        }

        $absolutePath = Storage::disk(self::DISK)->path($path);

        if (! $this->isValidImage($absolutePath, $extension)) {
            Storage::disk(self::DISK)->delete($path);
            throw new RuntimeException('Uploaded file is not a valid image.');
        }

        $photo = UploadedPhoto::create([
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'stored_name' => $storedName,
            'disk' => self::DISK,
            'path' => $path,
            'mime' => $mime,
            'size' => $file->getSize() ?: 0,
            'ip' => $ip,
            'user_agent' => $userAgent ? Str::limit($userAgent, 250, '') : null,
            'status' => 'pending',
        ]);

        GenerateThumbnailJob::dispatch($photo->id);

        return $photo;
    }

    private function isValidImage(string $absolutePath, string $extension): bool
    {
        if (! is_file($absolutePath) || filesize($absolutePath) === 0) {
            return false;
        }

        // HEIC/HEIF cannot be sniffed by getimagesize on most PHP builds; check ISOBMFF ftyp brand.
        if (in_array($extension, ['heic', 'heif'], true)) {
            return $this->hasHeifFtyp($absolutePath);
        }

        $info = @getimagesize($absolutePath);

        return is_array($info) && ! empty($info[0]) && ! empty($info[1]);
    }

    private function hasHeifFtyp(string $absolutePath): bool
    {
        $fh = @fopen($absolutePath, 'rb');
        if (! $fh) {
            return false;
        }
        $header = fread($fh, 32);
        fclose($fh);

        if ($header === false || strlen($header) < 12 || substr($header, 4, 4) !== 'ftyp') {
            return false;
        }

        $brands = ['heic', 'heix', 'heim', 'heis', 'hevc', 'hevx', 'mif1', 'msf1', 'heif'];
        foreach ($brands as $brand) {
            if (str_contains($header, $brand)) {
                return true;
            }
        }
        return false;
    }
}
