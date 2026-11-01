<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCopyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('manage-copies') ?? false)
            && $this->user()?->library_id !== null;
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'barcode' => ['required', 'string', 'max:255', 'unique:copies,barcode'],
            'copy_status_id' => ['required', 'integer', 'exists:copy_statuses,id'],
        ];
    }
}
