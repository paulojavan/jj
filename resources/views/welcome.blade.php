@extends('layouts.base')
@section('content')

<div class="content">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-center text-red-600 mb-2">Dashboard - JJ Calçados</h1>
        <p class="text-center text-gray-600">Sistema de Gestão de Vendas</p>
    </div>
    
    <x-alert />
	

     <!-- Seção Funcionalidades -->
     <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-semibold text-red-600">Funcionalidades</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card">
                <a href="{{ route('mensagens-aviso.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-bell text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Lista de<br>Mensagens</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('fluxo-caixa.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                    <i class="fas fa-cash-register mr-1"></i>
                    </div>
                    <h3 class="dashboard-title">Fluxo de<br>Caixa</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('carrinho.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    </div>
                    <h3 class="dashboard-title">Carrinho</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('baixa_fiscal.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                    <i class="fas fa-file-invoice mr-1"></i>
                    </div>
                    <h3 class="dashboard-title">Baixa fiscal</h3>
                </a>
            </div>

        </div>
    </div>

    <!-- Seção Funcionários -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-users text-red-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-semibold text-red-600">Funcionários</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card">
                <a href="{{ route('funcionario.cadastro') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-user-plus text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Cadastrar<br>Funcionário</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('funcionario.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-list text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Listar<br>Funcionários</h3>
                </a>
            </div>
        </div>
    </div>



    <!-- Seção Clientes -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-user-friends text-red-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-semibold text-red-600">Clientes</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card">
                <a href="{{ route('clientes.create') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-user-plus text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Cadastrar<br>Cliente</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('clientes.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-address-book text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Listar<br>Clientes</h3>
                </a>
            </div>
        </div>
    </div>

    <!-- Seção Produtos -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-shoe-prints text-red-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-semibold text-red-600">Produtos</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card">
                <a href="{{ route('produtos.create') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-plus-circle text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Cadastrar<br>Produto</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('produtos.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-boxes text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Listar<br>Produtos</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('marcas.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-tags text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Listar<br>Marcas</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('grupos.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-layer-group text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Listar<br>Grupos</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('subgrupos.index') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-sitemap text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Listar<br>Subgrupos</h3>
                </a>
            </div>
        </div>
    </div>

    <!-- Seção Despesas -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-money-bill-wave text-red-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-semibold text-red-600">Despesas</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card">
                <a href="{{ route('despesas.create') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-file-invoice-dollar text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Cadastrar<br>Despesa</h3>
                </a>
            </div>
        </div>
    </div>

    <!-- Seção Configurações -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-cogs text-red-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-semibold text-red-600">Configurações</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="dashboard-card">
                <a href="{{ route('horarios.edit') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-clock text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Horários de<br>Funcionamento</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('descontos.edit', ['desconto' => 1]) }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-percentage text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Configurar<br>Descontos</h3>
                </a>
            </div>

            <div class="dashboard-card">
                <a href="{{ route('multa-configuracao.edit') }}" class="dashboard-link">
                    <div class="dashboard-icon">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                    </div>
                    <h3 class="dashboard-title">Configurar<br>Multas e Juros</h3>
                </a>
            </div>
        </div>
    </div>

    <!-- Seção de Acesso Rápido -->
    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-red-600 mb-2">Acesso Rápido</h3>
                <p class="text-red-700">Vá direto para as funcionalidades mais usadas</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('produtos.procurar') }}" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-md">
                    <i class="fas fa-search mr-2"></i>Buscar Produtos
                </a>
                <a href="{{ route('carrinho.index') }}" class="bg-white hover:bg-gray-100 text-red-600 px-6 py-3 rounded-lg font-semibold transition-colors shadow-md border-2 border-red-600">
                    <i class="fas fa-shopping-cart mr-2"></i>Ver Carrinho
                </a>
            </div>
        </div>
    </div>
    
</div>

@endsection
