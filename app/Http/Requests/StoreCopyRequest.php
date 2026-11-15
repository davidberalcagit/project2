<?php

namespace App\Http\Requests;

use App\Models\Copie;
use Illuminate\Foundation\Http\FormRequest;

class StoreCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Copie::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'barcode' => ['required', 'string', 'max:255', 'unique:copies,barcode'],
        ];
    }
}
