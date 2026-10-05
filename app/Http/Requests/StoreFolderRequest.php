<?php

namespace App\Http\Requests;

use App\Models\Folder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Folder::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:folders,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique among the folders that share the same parent (NULL = root level).
                Rule::unique('folders', 'name')
                    ->where(fn ($query) => $query->where('parent_id', $this->input('parent_id'))),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A folder with this name already exists here.',
        ];
    }
}
