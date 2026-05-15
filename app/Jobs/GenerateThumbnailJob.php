<?php

namespace App\Jobs;

use App\Models\UploadedPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    private const THUMB_DIR = 'photos/thumbs';
    private const MAX_DIMENSION = 1200;

    public function __construct(public int $photoId)
    {
    }

    public function handle(): void
    {
        $photo = UploadedPhoto::find($this->photoId);

        if (! $photo) {
            return;
        }

        $disk = Storage::disk($photo->disk);
        $absolute = $disk->path($photo->path);

        if (! is_file($absolute)) {
            return;
        }

        if (in_array($photo->mime, ['image/heic', 'image/heif'], true)) {
            $absolute = $this->convertHeicToJpeg($photo, $absolute, $disk);
            if (! $absolute) {
                return;
            }
        }

        $info = @getimagesize($absolute);
        if (! is_array($info)) {
            return;
        }

        [$srcW, $srcH, $type] = $info;

        $src = $this->createImage($absolute, $type);
        if (! $src) {
            return;
        }

        if ($type === IMAGETYPE_JPEG) {
            $src = $this->applyExifOrientation($src, $absolute);
            $srcW = imagesx($src);
            $srcH = imagesy($src);
        }

        $photo->update(['width' => $srcW, 'height' => $srcH]);

        $ratio = min(self::MAX_DIMENSION / $srcW, self::MAX_DIMENSION / $srcH, 1);
        $dstW = (int) round($srcW * $ratio);
        $dstH = (int) round($srcH * $ratio);

        $dst = imagecreatetruecolor($dstW, $dstH);
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        $thumbPath = self::THUMB_DIR.'/'.$photo->stored_name;
        $disk->makeDirectory(self::THUMB_DIR);
        $this->writeImage($dst, $disk->path($thumbPath), $type);

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function convertHeicToJpeg(UploadedPhoto $photo, string $absolute, $disk): ?string
    {
        if (! extension_loaded('imagick')) {
            return null;
        }

        try {
            $im = new \Imagick($absolute);
            $im->setImageFormat('jpeg');
            $im->setImageCompressionQuality(90);
            $im->stripImage();

            $newStoredName = pathinfo($photo->stored_name, PATHINFO_FILENAME).'.jpg';
            $newPath = pathinfo($photo->path, PATHINFO_DIRNAME).'/'.$newStoredName;
            $newAbsolute = $disk->path($newPath);

            $im->writeImage($newAbsolute);
            $im->clear();
            $im->destroy();
        } catch (\ImagickException $e) {
            report($e);
            return null;
        }

        @unlink($absolute);

        $photo->update([
            'stored_name' => $newStoredName,
            'path' => $newPath,
            'mime' => 'image/jpeg',
        ]);

        return $newAbsolute;
    }

    private function createImage(string $path, int $type): \GdImage|false
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function applyExifOrientation(\GdImage $img, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $img;
        }

        $exif = @exif_read_data($path);
        $orientation = is_array($exif) && isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;

        $rotated = match ($orientation) {
            3 => @imagerotate($img, 180, 0),
            6 => @imagerotate($img, -90, 0),
            8 => @imagerotate($img, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($img);
            return $rotated;
        }
        return $img;
    }

    private function writeImage(\GdImage $img, string $path, int $type): void
    {
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($img, $path, 82),
            IMAGETYPE_PNG => imagepng($img, $path, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($img, $path, 82) : imagejpeg($img, $path, 82),
            default => null,
        };
    }
}
