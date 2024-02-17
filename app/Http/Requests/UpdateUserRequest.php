<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $current_authenticated_user = Auth::user();

        return $current_authenticated_user->is_admin || $this->route()->parameter('user') == $current_authenticated_user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route()->parameter('user');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'. $user->id .'|max:255',
            'nickname' => 'nullable|string|max:255',
            'is_admin' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'address' => 'nullable|regex:/^[a-zA-Z\d\s\-\,\#\.\+]+$/|min:10|max:255',
            'phone' => 'nullable|min:3|max:16',
            'description' => 'nullable|max:255'
        ];
    }
}
