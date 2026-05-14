<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPhotoRequest;
use App\Services\PhotoUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PhotoUploadController extends Controller
{
    public function __construct(private readonly PhotoUploadService $service)
    {
    }

    public function show(): View
    {
        return view('photos.upload');
    }

    public function store(UploadPhotoRequest $request): JsonResponse
    {
        try {
            $photo = $this->service->store(
                $request->file('photo'),
                $request->ip() ?? '',
                $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Nu am putut salva fotografia. Încearcă din nou.',
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Eroare de server. Încearcă din nou.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'id' => $photo->id,
            'filename' => $photo->stored_name,
        ], 201);
    }
}
