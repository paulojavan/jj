@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Pesquisar Cliente - Venda Crediário</h1>
            
            <!-- Formulário de Pesquisa -->
            <form method="POST" action="{{ route('carrinho.pesquisar-cliente') }}" class="mb-6">
                @csrf
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ old('search', $searchTerm ?? '') }}"
                            placeholder="Digite nome, apelido, RG ou CPF do cliente..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required
                        >
                    </div>
                    <button 
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Pesquisar
                    </button>
                </div>
            </form>

            <!-- Mensagens de Erro/Sucesso -->
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Resultados da Pesquisa -->
            @if(isset($clientesComStatus) && $clientesComStatus->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Apelido</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RG</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limite</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($clientesComStatus as $cliente)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex-shrink-0 h-16 w-16">
                                            @if($cliente->foto)
                                                @php $pasta = $cliente->pasta ?? $cliente->cpf; @endphp
                                                @if(file_exists(public_path('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto)))
                                                    <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-200" 
                                                         src="{{ asset('storage/uploads/clientes/' . $pasta . '/' . $cliente->foto) }}" 
                                                         alt="Foto de {{ $cliente->nome }}">
                                                @else
                                                    <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center border-2 border-gray-200">
                                                        <i class="fas fa-user text-gray-500 text-xl"></i>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center border-2 border-gray-200">
                                                    <i class="fas fa-user text-gray-500 text-xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $cliente->nome }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $cliente->apelido ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $cliente->rg }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $cliente->cpf }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $cliente->status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($cliente->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        R$ {{ number_format($cliente->limite, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($cliente->action_type === 'selecionar')
                                            <a href="{{ route('carrinho.selecionar-cliente', $cliente->id) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                {{ $cliente->action_text }}
                                            </a>
                                        @elseif($cliente->action_type === 'verificar')
                                            <a href="{{ route('clientes.edit', $cliente->id) }}" 
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                {{ $cliente->action_text }}
                                            </a>
                                        @else
                                            <button 
                                                disabled
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-400 cursor-not-allowed">
                                                {{ $cliente->action_text }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Botão Voltar -->
            <div class="mt-6">
                <a href="{{ route('carrinho.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    ← Voltar ao Carrinho
                </a>
            </div>
        </div>
    </div>
</div>
@endsection