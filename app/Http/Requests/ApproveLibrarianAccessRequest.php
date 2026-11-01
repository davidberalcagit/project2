<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveLibrarianAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
    }

    public function rules(): array
    {
        $accessRequest = $this->route('librarianAccessRequest');

        return [
            'library_id' => [
                Rule::requiredIf(
                    $accessRequest->request_type === 'existing'
                    && $accessRequest->requested_library_id === null
                ),
                'nullable',
                'integer',
                'exists:libraries,id',
            ],
        ];
    }
}
