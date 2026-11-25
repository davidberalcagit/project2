<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookCatalogFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'library_id' => ['nullable', 'integer', 'exists:libraries,id'],
            'min_rating' => ['nullable', 'numeric', 'between:1,5'],
            'sort' => ['nullable', 'in:rating,copies'],
        ];
    }
}
