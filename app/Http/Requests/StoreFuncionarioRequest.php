<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuncionarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login',
            'password' => 'required|string|min:6',
            'cidade' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cadastro_produtos' => 'nullable|boolean',
            'ajuste_estoque' => 'nullable|boolean',
            'vendas_crediario' => 'nullable|boolean',
            'limite' => 'nullable|boolean',
            'recebimentos' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            
            'login.required' => 'O login é obrigatório.',
            'login.string' => 'O login deve ser um texto válido.',
            'login.max' => 'O login não pode ter mais de 255 caracteres.',
            'login.unique' => 'Este login já está em uso por outro funcionário.',
            
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser um texto válido.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            
            'cidade.required' => 'A cidade é obrigatória.',
            'cidade.string' => 'A cidade deve ser um texto válido.',
            'cidade.max' => 'A cidade não pode ter mais de 255 caracteres.',
            
            'image.required' => 'A foto do funcionário é obrigatória.',
            'image.image' => 'O arquivo deve ser uma imagem válida.',
            'image.mimes' => 'A imagem deve ser do tipo: JPEG, PNG, JPG ou GIF.',
            'image.max' => 'A imagem não pode ser maior que 2MB.',
        ];
    }
}
