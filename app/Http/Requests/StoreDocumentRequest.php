<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Document::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'folder_id' => ['required', 'integer', 'exists:folders,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'file' => [
                'required',
                'file',
                'max:'.Document::MAX_SIZE_KB,
                // "mimes" inspects the real content, "extensions" checks the name the user gave the file.
                'mimes:'.implode(',', Document::ALLOWED_EXTENSIONS),
                'extensions:'.implode(',', Document::ALLOWED_EXTENSIONS),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'folder_id' => 'folder',
            'department_id' => 'department',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.max' => 'The file may not be larger than '.(Document::MAX_SIZE_KB / 1024).' MB.',
            'file.mimes' => 'Allowed file types: '.implode(', ', Document::ALLOWED_EXTENSIONS).'.',
            'file.extensions' => 'Allowed file types: '.implode(', ', Document::ALLOWED_EXTENSIONS).'.',
        ];
    }
}
