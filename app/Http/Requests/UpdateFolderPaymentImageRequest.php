<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFolderPaymentImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) user_is_staff_portal($this->user());
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
