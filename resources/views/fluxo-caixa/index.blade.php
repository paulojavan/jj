@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-cash-register mr-2"></i>Fluxo de Caixa
            </h1>
            <a href="{{ route('fluxo-caixa.individualizado') }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-user mr-2"></i>Fluxo Individual
            </a>
        </div>

        <!-- Filtros de Período -->
        @include('fluxo-caixa.partials.filtros-periodo')

        <!-- Resultados -->
        @if(isset($dados))
            <div class="mt-8">
                <!-- Resumo Geral (apenas para administradores) -->
                @if($user->nivel === 'administrador' && isset($dados['resumo_geral']))
                    @include('fluxo-caixa.partials.resumo-geral', ['resumo' => $dados['resumo_geral']])
                @endif

                <!-- Dados por Cidade -->
                @if(isset($dados['cidades']) && count($dados['cidades']) > 0)
                    @foreach($dados['cidades'] as $cidadeData)
                        @include('fluxo-caixa.partials.resumo-cidade', ['cidadeData' => $cidadeData])
                    @endforeach
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <span class="text-yellow-800">Nenhum dado encontrado para o período selecionado.</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Mostrar/ocultar detalhes dos vendedores
    document.querySelectorAll('.toggle-vendedor').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (target.classList.contains('hidden')) {
                target.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                target.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
});
</script>
@endpush
@endsection