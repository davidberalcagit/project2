<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLibrarianAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $accessRequest = $user?->librarianAccessRequest;

        return $user !== null
            && ! $user->can('access-library')
            && (! $accessRequest || $accessRequest->status === 'rejected');
    }

    public function rules(): array
    {
        $isExistingLibraryRequest = $this->input('request_type') === 'existing';
        $isNewLibraryRequest = $this->input('request_type') === 'new';

        return [
            'request_type' => ['required', Rule::in(['existing', 'new'])],
            'library_id' => [
                Rule::requiredIf($isExistingLibraryRequest),
                Rule::prohibitedIf($isNewLibraryRequest),
                'nullable',
                'integer',
                'exists:libraries,id',
            ],
            'proposed_library_name' => [
                Rule::requiredIf($isNewLibraryRequest),
                Rule::prohibitedIf($isExistingLibraryRequest),
                'nullable',
                'string',
                'max:255',
            ],
            'proposed_library_location' => [
                Rule::requiredIf($isNewLibraryRequest),
                Rule::prohibitedIf($isExistingLibraryRequest),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
