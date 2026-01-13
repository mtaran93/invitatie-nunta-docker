<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class WrittenGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'attendance' => 'required|string',
            'kids' => 'required|string',
            'answer' => 'required|boolean',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'attendance.required' => 'Attendance is required',
            'kids.required' => 'Kids is required',
            'name.max' => 'You bastard!',
            'answer.required' => 'Answer is required',
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Va rugam incercati mai tarziu',
            'errors' => $validator->errors(),
        ], 422));
    }
}
