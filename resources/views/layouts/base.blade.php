<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>Joécio calçados</title>
    <link rel="icon" href="{{ asset('storage/uploads/sistema/icon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="main-container">
        <!-- Header responsivo -->
        <header class="header shadow-lg">
            <div class="content-header justify-between">
                <h2 class="title-logo">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <span class="text-2xl font-bold">JJ</span>
                        <span class="hidden sm:inline text-sm">Calçados</span>
                    </a>
                </h2>

                <!-- Menu desktop -->
                <nav class="hidden lg:flex list-nav-link">
                    <!-- Funcionalidades Dropdown -->
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-th-large mr-1"></i>Funcionalidades
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-yellow-200">
                            <a href="{{ route('mensagens-aviso.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-bell mr-2"></i>Mensagens
                            </a>
                            <a href="{{ route('fluxo-caixa.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-cash-register mr-2"></i>Fluxo de Caixa
                            </a>
                            <a href="{{ route('carrinho.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100 relative">
                                <i class="fas fa-shopping-cart mr-2"></i>Carrinho
                                @if(Session::has('carrinho') && count(Session::get('carrinho')) > 0)
                                    <span class="absolute top-1 right-3 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ count(Session::get('carrinho')) }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('baixa_fiscal.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-file-invoice mr-2"></i>Baixa Fiscal
                            </a>
                        </div>
                    </div>

                    <!-- Funcionários Dropdown -->
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-users mr-1"></i>Funcionários
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-yellow-200">
                            <a href="{{ route('funcionario.cadastro') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-user-plus mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('funcionario.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-list mr-2"></i>Listar
                            </a>
                        </div>
                    </div>

                    <!-- Clientes Dropdown -->
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-user-friends mr-1"></i>Clientes
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-yellow-200">
                            <a href="{{ route('clientes.create') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-user-plus mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('clientes.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-address-book mr-2"></i>Listar
                            </a>
                            @if(Auth::check() && (Auth::user()->nivel === 'admin' || Auth::user()->limite))
                            <a href="{{ route('verificacao-limite.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-credit-card mr-2"></i>Verificar Limite
                            </a>
                            @endif
                            @if(Auth::check() && Auth::user()->nivel === 'administrador')
                            <div class="border-t border-yellow-300 my-1"></div>
                            <a href="{{ route('negativacao.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Negativação SPC
                            </a>
                            <a href="{{ route('negativacao.negativados') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-ban mr-2"></i>Clientes Negativados
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Produtos Dropdown -->
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-shoe-prints mr-1"></i>Produtos
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-yellow-200">
                            <a href="{{ route('produtos.procurar') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-search mr-2"></i>Buscar
                            </a>
                            <a href="{{ route('produtos.create') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-plus-circle mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('produtos.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-boxes mr-2"></i>Listar
                            </a>
                            <a href="{{ route('marcas.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-tags mr-2"></i>Marcas
                            </a>
                            <a href="{{ route('grupos.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-layer-group mr-2"></i>Grupos
                            </a>
                            <a href="{{ route('subgrupos.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-sitemap mr-2"></i>Subgrupos
                            </a>
                        </div>
                    </div>

                    <!-- Despesas Dropdown -->
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-money-bill-wave mr-1"></i>Despesas
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-yellow-200">
                            <a href="{{ route('despesas.create') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('despesas.create.fixa') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-file-invoice mr-2"></i>Despesas Fixas
                            </a>
                            <a href="{{ route('despesas.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-search-dollar mr-2"></i>Verificar
                            </a>
                        </div>
                    </div>

                    <!-- Configurações Dropdown -->
                    <div class="relative group">
                        <button class="nav-link flex items-center">
                            <i class="fas fa-cogs mr-1"></i>Configurações
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 border border-yellow-200">
                            <a href="{{ route('horarios.edit') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-clock mr-2"></i>Horários
                            </a>
                            <a href="{{ route('descontos.edit', ['desconto' => 1]) }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-percentage mr-2"></i>Descontos
                            </a>
                            <a href="{{ route('multa-configuracao.edit') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-yellow-100">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Multas e Juros
                            </a>
                        </div>
                    </div>

                    <!-- Sair -->
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="nav-link flex items-center">
                                <i class="fas fa-sign-out-alt mr-1"></i>Sair
                            </button>
                        </form>
                    </li>
                </nav>

                <!-- Menu mobile -->
                <div class="lg:hidden">
                    <button id="mobile-menu-button" class="nav-link">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Menu mobile dropdown -->
            <div id="mobile-menu" class="lg:hidden hidden bg-yellow-400 border-t border-yellow-500">
                <div class="px-2 py-2 space-y-1">
                    <!-- Funcionalidades Dropdown -->
                    <div class="relative group">
                        <button class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center justify-between">
                            <span><i class="fas fa-th-large mr-2"></i>Funcionalidades</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="ml-4 mt-1 space-y-1 bg-yellow-300 rounded hidden">
                            <a href="{{ route('mensagens-aviso.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-bell mr-2"></i>Mensagens
                            </a>
                            <a href="{{ route('fluxo-caixa.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-cash-register mr-2"></i>Fluxo de Caixa
                            </a>
                            <a href="{{ route('carrinho.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded relative">
                                <i class="fas fa-shopping-cart mr-2"></i>Carrinho
                                @if(Session::has('carrinho') && count(Session::get('carrinho')) > 0)
                                    <span class="absolute top-1 right-3 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                        {{ count(Session::get('carrinho')) }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('baixa_fiscal.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-file-invoice mr-2"></i>Baixa Fiscal
                            </a>
                        </div>
                    </div>

                    <!-- Funcionários Dropdown -->
                    <div class="relative group">
                        <button class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center justify-between">
                            <span><i class="fas fa-users mr-2"></i>Funcionários</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="ml-4 mt-1 space-y-1 bg-yellow-300 rounded hidden">
                            <a href="{{ route('funcionario.cadastro') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-user-plus mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('funcionario.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-list mr-2"></i>Listar
                            </a>
                        </div>
                    </div>

                    <!-- Clientes Dropdown -->
                    <div class="relative group">
                        <button class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center justify-between">
                            <span><i class="fas fa-user-friends mr-2"></i>Clientes</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="ml-4 mt-1 space-y-1 bg-yellow-300 rounded hidden">
                            <a href="{{ route('clientes.create') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-user-plus mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('clientes.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-address-book mr-2"></i>Listar
                            </a>
                            @if(Auth::check() && (Auth::user()->nivel === 'admin' || Auth::user()->limite))
                            <a href="{{ route('verificacao-limite.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-credit-card mr-2"></i>Verificar Limite
                            </a>
                            @endif
                            @if(Auth::check() && Auth::user()->nivel === 'administrador')
                            <a href="{{ route('negativacao.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Negativação SPC
                            </a>
                            <a href="{{ route('negativacao.negativados') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-ban mr-2"></i>Clientes Negativados
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Produtos Dropdown -->
                    <div class="relative group">
                        <button class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center justify-between">
                            <span><i class="fas fa-shoe-prints mr-2"></i>Produtos</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="ml-4 mt-1 space-y-1 bg-yellow-300 rounded hidden">
                            <a href="{{ route('produtos.procurar') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-search mr-2"></i>Buscar
                            </a>
                            <a href="{{ route('produtos.create') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-plus-circle mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('produtos.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-boxes mr-2"></i>Listar
                            </a>
                            <a href="{{ route('marcas.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-tags mr-2"></i>Marcas
                            </a>
                            <a href="{{ route('grupos.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-layer-group mr-2"></i>Grupos
                            </a>
                            <a href="{{ route('subgrupos.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-sitemap mr-2"></i>Subgrupos
                            </a>
                        </div>
                    </div>

                    <!-- Despesas Dropdown -->
                    <div class="relative group">
                        <button class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center justify-between">
                            <span><i class="fas fa-money-bill-wave mr-2"></i>Despesas</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="ml-4 mt-1 space-y-1 bg-yellow-300 rounded hidden">
                            <a href="{{ route('despesas.create') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>Cadastrar
                            </a>
                            <a href="{{ route('despesas.create.fixa') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-file-invoice mr-2"></i>Despesas Fixas
                            </a>
                            <a href="{{ route('despesas.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-search-dollar mr-2"></i>Verificar
                            </a>
                        </div>
                    </div>

                    <!-- Configurações Dropdown -->
                    <div class="relative group">
                        <button class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center justify-between">
                            <span><i class="fas fa-cogs mr-2"></i>Configurações</span>
                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                        </button>
                        <div class="ml-4 mt-1 space-y-1 bg-yellow-300 rounded hidden">
                            <a href="{{ route('horarios.edit') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-clock mr-2"></i>Horários
                            </a>
                            <a href="{{ route('descontos.edit', ['desconto' => 1]) }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-percentage mr-2"></i>Descontos
                            </a>
                            <a href="{{ route('multa-configuracao.edit') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Multas e Juros
                            </a>
                        </div>
                    </div>

                    <!-- Sair -->
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded flex items-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>Sair
                        </button>
                    </form>
                </div>
            </div>
        </header>
        <!-- Conteúdo principal -->
        <main class="flex-grow p-4">
            @yield('content')
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Menu mobile toggle
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');

                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', function() {
                        mobileMenu.classList.toggle('hidden');
                    });
                }

                // Dropdowns do menu mobile
                const mobileDropdowns = document.querySelectorAll('#mobile-menu .relative.group');
                
                mobileDropdowns.forEach((dropdown) => {
                    const button = dropdown.querySelector('button');
                    const menu = dropdown.querySelector('div');
                    // Seleciona apenas o ícone de dropdown (último i com classe fa-chevron-down)
                    const dropdownIcon = button ? button.querySelector('i.fa-chevron-down') : null;
                    
                    if (button && menu && dropdownIcon) {
                        // Adiciona classe para identificar dropdowns no mobile
                        dropdown.classList.add('mobile-dropdown');
                        
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Verifica se o menu está visível
                            const isHidden = menu.classList.contains('hidden');
                            
                            // Fecha todos os dropdowns
                            document.querySelectorAll('#mobile-menu .relative.group div').forEach((el) => {
                                el.classList.add('hidden');
                            });
                            
                            // Muda todos os ícones de dropdown para chevron-down
                            document.querySelectorAll('#mobile-menu .relative.group button i.fa-chevron-down, #mobile-menu .relative.group button i.fa-chevron-up').forEach((el) => {
                                el.classList.remove('fa-chevron-up');
                                el.classList.add('fa-chevron-down');
                            });
                            
                            // Se o menu estava oculto, mostra ele e muda o ícone
                            if (isHidden) {
                                menu.classList.remove('hidden');
                                if (dropdownIcon) {
                                    dropdownIcon.classList.remove('fa-chevron-down');
                                    dropdownIcon.classList.add('fa-chevron-up');
                                }
                            }
                        });
                    }
                });

                // CPF mask functionality
                const cpfInput = document.getElementById('cpf');
                if (cpfInput) {
                    cpfInput.addEventListener('input', function(e) {
                        let value = e.target.value;

                        // Remove todos os caracteres não numéricos
                        value = value.replace(/\D/g, '');

                        // Limita a 11 dígitos (CPF brasileiro)
                        if (value.length > 11) {
                            value = value.slice(0, 11);
                        }

                        // Aplica a máscara de CPF (000.000.000-00)
                        if (value.length > 0) {
                            // Adiciona o primeiro ponto após 3 dígitos
                            if (value.length > 3) {
                                value = value.substring(0, 3) + '.' + value.substring(3);
                            }
                            // Adiciona o segundo ponto após 7 caracteres (incluindo o primeiro ponto)
                            if (value.length > 7) {
                                value = value.substring(0, 7) + '.' + value.substring(7);
                            }
                            // Adiciona o hífen após 11 caracteres (incluindo os dois pontos)
                            if (value.length > 11) {
                                value = value.substring(0, 11) + '-' + value.substring(11);
                            }
                        }

                        e.target.value = value;
                    });

                    // Validação ao enviar o formulário
                    const form = cpfInput.closest('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            const cpfValue = cpfInput.value.replace(/\D/g, '');
                            if (cpfValue.length !== 11) {
                                alert('CPF deve conter 11 dígitos numéricos.');
                                e.preventDefault();
                                cpfInput.focus();
                            }
                        });
                    }
                }
            });
        </script>

    </div>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
