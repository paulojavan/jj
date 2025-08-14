@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Baixa Fiscal de Produtos</h1>
        
        @if(count($vendasPorCidade) > 0)
            @foreach($vendasPorCidade as $nomeCidade => $vendas)
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">{{ $nomeCidade }}</h2>
                    
                    @if(count($vendas) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-2 px-4 border-b text-left">Produto</th>
                                        <th class="py-2 px-4 border-b text-left">Código</th>
                                        <th class="py-2 px-4 border-b text-left">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendas as $venda)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border-b">{{ $venda->nome_produto }}</td>
                                            <td class="py-2 px-4 border-b">{{ $venda->codigo_produto }}</td>
                                            <td class="py-2 px-4 border-b">
                                                <button 
                                                    class="btn-dar-baixa bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm"
                                                    data-cidade="{{ $nomeCidade }}"
                                                    data-id="{{ $venda->id }}"
                                                    data-codigo="{{ $venda->codigo_produto }}"
                                                >
                                                    Dar Baixa
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">Nenhuma venda pendente de baixa fiscal.</p>
                    @endif
                </div>
            @endforeach
        @else
            <div class="text-center py-8">
                <p class="text-gray-500 text-lg">Nenhuma cidade ativa encontrada ou nenhuma venda pendente de baixa fiscal.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de confirmação -->
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Confirmar Baixa Fiscal</h3>
        <p class="mb-6">Tem certeza que deseja dar baixa fiscal no produto <span id="codigoProduto"></span>?</p>
        <div class="flex justify-end space-x-3">
            <button id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Cancelar
            </button>
            <button id="confirmBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const confirmBtn = document.getElementById('confirmBtn');
        const codigoProduto = document.getElementById('codigoProduto');
        let vendaParaBaixa = null;

        // Adicionar evento aos botões de dar baixa
        document.querySelectorAll('.btn-dar-baixa').forEach(button => {
            button.addEventListener('click', function() {
                vendaParaBaixa = {
                    cidade: this.getAttribute('data-cidade'),
                    id: this.getAttribute('data-id'),
                    codigo: this.getAttribute('data-codigo')
                };
                
                // Atualizar texto do modal com o código do produto
                codigoProduto.textContent = vendaParaBaixa.codigo;
                
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        // Cancelar
        cancelBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            vendaParaBaixa = null;
        });

        // Confirmar baixa
        confirmBtn.addEventListener('click', function() {
            if (vendaParaBaixa) {
                // Fazer requisição AJAX para dar baixa
                fetch(`/baixa-fiscal/${vendaParaBaixa.cidade}/${vendaParaBaixa.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remover a linha da tabela
                        document.querySelector(`[data-id="${vendaParaBaixa.id}"]`).closest('tr').remove();
                        // Exibir mensagem de sucesso com SweetAlert
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        // Exibir mensagem de erro com SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Erro ao realizar baixa fiscal: ' + data.message,
                            confirmButtonText: 'OK'
                        });
                    }
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    vendaParaBaixa = null;
                })
                .catch(error => {
                    console.error('Erro:', error);
                    // Exibir mensagem de erro com SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao realizar baixa fiscal.',
                        confirmButtonText: 'OK'
                    });
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    vendaParaBaixa = null;
                });
            }
        });
    });
</script>
@endsection