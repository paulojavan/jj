@extends('layouts.base')

@section('content')
<div class="content">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-center text-red-600 mb-2">Mensagens de Aviso</h1>
        <p class="text-center text-gray-600">Lista de clientes com parcelas vencidas</p>
    </div>

    <x-alert />

    @if(isset($clientes) && $clientes->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nome
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($clientes as $cliente)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $cliente->nome }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form class="enviar-mensagem-form" data-action="{{ route('mensagens-aviso.enviar-mensagem', $cliente->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                            <i class="fab fa-whatsapp mr-1"></i> Enviar Mensagem
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Nenhuma mensagem de aviso</h3>
            <p class="text-gray-500">Não há clientes com parcelas vencidas que atendam aos critérios.</p>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar evento de submit para os formulários de envio de mensagem
        document.querySelectorAll('.enviar-mensagem-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const action = this.getAttribute('data-action');
                
                fetch(action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Abrir o link do WhatsApp em uma nova aba
                    window.open(data.url, '_blank');
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao enviar a mensagem.');
                });
            });
        });
    });
</script>
@endsection