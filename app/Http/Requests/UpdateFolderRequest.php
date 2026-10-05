<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('folder'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $folder = $this->route('folder');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders', 'name')
                    ->where(fn ($query) => $query->where('parent_id', $folder->parent_id))
                    ->ignore($folder->id),
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
