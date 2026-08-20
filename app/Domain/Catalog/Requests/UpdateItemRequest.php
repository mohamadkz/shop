<?php

namespace App\Domain\Catalog\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->tokenCan('items:write') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'min:1', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('items', 'slug')->ignore($itemId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999999.99'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', 'in:draft,active,archived'],
            'image' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:2048'],
        ];
    }
}
