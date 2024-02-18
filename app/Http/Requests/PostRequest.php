<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'lat' => 'required|numeric',
            'lang' => 'required|numeric',
            'reward' => 'nullable|numeric',
            'type' => 'required|in:REQUEST,FOUND',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:64',
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096'
        ];
    }
}
