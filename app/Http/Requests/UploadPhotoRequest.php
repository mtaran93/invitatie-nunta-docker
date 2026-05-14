<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',
                'max:3072',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Selectează o fotografie.',
            'photo.file' => 'Fișier invalid.',
            'photo.mimetypes' => 'Format neacceptat. Acceptăm JPG, PNG, WEBP sau HEIC.',
            'photo.max' => 'Fotografia depășește 3 MB.',
            'photo.dimensions' => 'Rezoluție prea mare.',
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first('photo') ?: 'Fișier invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
