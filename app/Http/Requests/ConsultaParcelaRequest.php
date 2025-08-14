<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultaParcelaRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'cpf' => [
                'required',
                'string',
                'min:14',
                'max:14',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'cpf.required' => 'CPF é obrigatório',
            'cpf.string' => 'CPF deve ser um texto válido',
            'cpf.min' => 'CPF deve estar no formato XXX.XXX.XXX-XX',
            'cpf.max' => 'CPF deve estar no formato XXX.XXX.XXX-XX',
            'cpf.regex' => 'CPF deve estar no formato XXX.XXX.XXX-XX'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'cpf' => 'CPF'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Garante que o CPF mantenha a formatação
        if ($this->has('cpf')) {
            $cpf = $this->input('cpf');
            
            // Remove caracteres não numéricos
            $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
            
            // Reaplica a máscara se necessário
            if (strlen($cpfLimpo) === 11) {
                $cpfFormatado = substr($cpfLimpo, 0, 3) . '.' . 
                               substr($cpfLimpo, 3, 3) . '.' . 
                               substr($cpfLimpo, 6, 3) . '-' . 
                               substr($cpfLimpo, 9, 2);
                
                $this->merge(['cpf' => $cpfFormatado]);
            }
        }
    }

    /**
     * Valida se o CPF é válido usando algoritmo oficial
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('cpf') && !$this->validarCPF($this->input('cpf'))) {
                $validator->errors()->add('cpf', 'CPF inválido');
            }
        });
    }

    /**
     * Valida CPF usando algoritmo oficial brasileiro
     */
    private function validarCPF(string $cpf): bool
    {
        // Remove caracteres especiais
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Calcula primeiro dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += intval($cpf[$i]) * (10 - $i);
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        
        // Verifica primeiro dígito
        if (intval($cpf[9]) !== $digito1) {
            return false;
        }
        
        // Calcula segundo dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += intval($cpf[$i]) * (11 - $i);
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        
        // Verifica segundo dígito
        return intval($cpf[10]) === $digito2;
    }
}
