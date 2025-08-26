@extends('layouts.base')
@section('content')

<div class="content text-center">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Clientes Ociosos</h1>
        <p class="text-gray-600">Clientes que não interagem há mais de 150 dias</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Container para a visualização em tabela (telas médias e maiores) --}}
    <div class="hidden md:block table-container">
        <table class="table w-full">
            <thead class="table-header-group">
                <tr class="table-header">
                    <th class="table-header w-1/3 text-center">Nome</th>
                    <th class="table-header w-1/6 text-center">Data Ociosidade</th>
                    <th class="table-header w-1/6 text-center">Dias Ociosos</th>
                    <th class="table-header w-1/6 text-center">Telefone</th>
                    <th class="table-header w-1/6 text-center">Ações</th>
                </tr>
            </thead>
            <tbody class="table-body">
                @forelse ($clientes as $cliente)
                    <tr class="table-row">
                        <td class="table-cell align-middle text-center">
                            <div class="font-semibold">{{ $cliente->nome }}</div>
                            <div class="text-sm text-gray-500">{{ $cliente->cpf }}</div>
                        </td>
                        <td class="table-cell align-middle text-center">
                            {{ $cliente->ociosidade ? \Carbon\Carbon::parse($cliente->ociosidade)->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="table-cell align-middle text-center">
                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                                {{ $cliente->dias_ociosos }} dias
                            </span>
                        </td>
                        <td class="table-cell align-middle text-center">
                            {{ $cliente->telefone ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $cliente->telefone) : 'N/A' }}
                        </td>
                        <td class="table-actions align-middle text-center">
                            @if($cliente->telefone)
                                <button onclick="enviarMensagemOcioso({{ $cliente->id }})" 
                                        class="btn-green inline-flex items-center">
                                    <i class="fab fa-whatsapp mr-2"></i>Enviar Mensagem
                                </button>
                            @else
                                <span class="text-gray-400 text-sm">Sem telefone</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="table-row">
                        <td colspan="5" class="table-cell text-center py-8">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-600 mb-2">Nenhum cliente ocioso encontrado</h3>
                                <p class="text-gray-500">Todos os clientes estão ativos ou foram contatados recentemente.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Container para a visualização em cards (telas pequenas) --}}
    <div class="md:hidden space-y-4">
        @forelse ($clientes as $cliente)
            <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                <div class="flex flex-col space-y-3">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800">{{ $cliente->nome }}</h3>
                            <p class="text-sm text-gray-500">{{ $cliente->cpf }}</p>
                        </div>
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">
                            {{ $cliente->dias_ociosos }} dias
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-500">Ociosidade:</span>
                            <div class="font-medium">
                                {{ $cliente->ociosidade ? \Carbon\Carbon::parse($cliente->ociosidade)->format('d/m/Y') : 'N/A' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500">Telefone:</span>
                            <div class="font-medium">
                                {{ $cliente->telefone ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $cliente->telefone) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-2 border-t border-gray-100">
                        @if($cliente->telefone)
                            <button onclick="enviarMensagemOcioso({{ $cliente->id }})" 
                                    class="btn-green w-full inline-flex items-center justify-center">
                                <i class="fab fa-whatsapp mr-2"></i>Enviar Mensagem
                            </button>
                        @else
                            <div class="text-center text-gray-400 text-sm py-2">
                                Sem telefone cadastrado
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-600 mb-2">Nenhum cliente ocioso encontrado</h3>
                <p class="text-gray-500">Todos os clientes estão ativos ou foram contatados recentemente.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    @if($clientes->hasPages())
        <div class="mt-6">
            {{ $clientes->links() }}
        </div>
    @endif
</div>

{{-- Modal de confirmação --}}
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4">
        <div class="flex items-center mb-4">
            <i class="fab fa-whatsapp text-green-500 text-2xl mr-3"></i>
            <h3 class="text-lg font-semibold">Confirmar Envio</h3>
        </div>
        <p class="text-gray-600 mb-6">
            Deseja enviar mensagem de reativação para este cliente? 
            O campo de ociosidade será atualizado automaticamente.
        </p>
        <div class="flex justify-end space-x-3">
            <button onclick="fecharModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancelar
            </button>
            <button id="confirmarEnvio" class="btn-green">
                <i class="fab fa-whatsapp mr-2"></i>Confirmar
            </button>
        </div>
    </div>
</div>

<script>
let clienteIdSelecionado = null;

function enviarMensagemOcioso(clienteId) {
    clienteIdSelecionado = clienteId;
    document.getElementById('confirmModal').classList.remove('hidden');
    document.getElementById('confirmModal').classList.add('flex');
}

function fecharModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.getElementById('confirmModal').classList.remove('flex');
    clienteIdSelecionado = null;
}

document.getElementById('confirmarEnvio').addEventListener('click', function() {
    if (!clienteIdSelecionado) return;
    
    // Mostrar loading
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
    this.disabled = true;
    
    // Fazer requisição AJAX com proteção CSRF
    fetch(`/clientes/${clienteIdSelecionado}/mensagem-ocioso`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            fecharModal();
            
            // Mostrar sucesso
            Swal.fire({
                title: 'Sucesso!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // Abrir WhatsApp em nova aba
                window.open(data.whatsapp_url, '_blank');
                
                // Recarregar página para atualizar lista
                window.location.reload();
            });
        } else {
            // Mostrar erro
            Swal.fire({
                title: 'Erro!',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            
            // Resetar botão
            document.getElementById('confirmarEnvio').innerHTML = '<i class="fab fa-whatsapp mr-2"></i>Confirmar';
            document.getElementById('confirmarEnvio').disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            title: 'Erro!',
            text: 'Erro ao processar solicitação',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        
        // Resetar botão
        document.getElementById('confirmarEnvio').innerHTML = '<i class="fab fa-whatsapp mr-2"></i>Confirmar';
        document.getElementById('confirmarEnvio').disabled = false;
    });
});

// Fechar modal ao clicar fora
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});
</script>

@endsection