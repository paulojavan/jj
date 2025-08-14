<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FluxoCaixaRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $user = Auth::user();
        
        // Regras básicas
        $rules = [];

        // Se for relatório geral
        if ($this->routeIs('fluxo-caixa.relatorio-geral')) {
            if ($user && $user->nivel === 'administrador') {
                $rules = [
                    'data_inicio' => 'nullable|date|before_or_equal:today',
                    'data_fim' => 'nullable|date|after_or_equal:data_inicio|before_or_equal:today',
                ];
            }
        }

        // Se for relatório individualizado
        if ($this->routeIs('fluxo-caixa.relatorio-individualizado')) {
            $rules = [
                'data_inicio' => 'required|date|before_or_equal:today',
                'data_fim' => 'required|date|after_or_equal:data_inicio|before_or_equal:today',
                'vendedor_id' => 'required|integer|exists:users,id',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'data_inicio.required' => 'A data inicial é obrigatória.',
            'data_inicio.date' => 'A data inicial deve ser uma data válida.',
            'data_inicio.before_or_equal' => 'A data inicial não pode ser maior que hoje.',
            
            'data_fim.required' => 'A data final é obrigatória.',
            'data_fim.date' => 'A data final deve ser uma data válida.',
            'data_fim.after_or_equal' => 'A data final deve ser maior ou igual à data inicial.',
            'data_fim.before_or_equal' => 'A data final não pode ser maior que hoje.',
            
            'vendedor_id.required' => 'É necessário selecionar um vendedor.',
            'vendedor_id.integer' => 'Vendedor inválido.',
            'vendedor_id.exists' => 'Vendedor não encontrado.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::user();
            
            // Validação adicional para vendedor individualizado
            if ($this->routeIs('fluxo-caixa.relatorio-individualizado')) {
                $vendedorId = $this->input('vendedor_id');
                
                // Se não for administrador, só pode ver seus próprios dados
                if ($user && $user->nivel !== 'administrador' && $user->id != $vendedorId) {
                    $validator->errors()->add('vendedor_id', 'Você não tem permissão para visualizar dados deste vendedor.');
                }
            }

            // Validação de período máximo (opcional - evitar consultas muito pesadas)
            $dataInicio = $this->input('data_inicio');
            $dataFim = $this->input('data_fim');
            
            if ($dataInicio && $dataFim) {
                $inicio = \Carbon\Carbon::parse($dataInicio);
                $fim = \Carbon\Carbon::parse($dataFim);
                $diasDiferenca = $inicio->diffInDays($fim);
                
                // Limite de 1 ano para não sobrecarregar o sistema
                if ($diasDiferenca > 365) {
                    $validator->errors()->add('data_fim', 'O período selecionado não pode ser maior que 1 ano.');
                }
            }
        });
    }

    /**
     * Get the validated data from the request with defaults.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        $user = Auth::user();
        
        // Aplicar defaults baseados no nível do usuário
        if ($user && $user->nivel !== 'administrador') {
            // Vendedores só veem o dia atual
            $hoje = now()->format('Y-m-d');
            $validated['data_inicio'] = $hoje;
            $validated['data_fim'] = $hoje;
        } else {
            // Administradores podem usar defaults ou valores informados
            $validated['data_inicio'] = $validated['data_inicio'] ?? now()->format('Y-m-d');
            $validated['data_fim'] = $validated['data_fim'] ?? now()->format('Y-m-d');
        }
        
        return $validated;
    }
}