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
                    <li><a class="nav-link" href="{{ route('funcionario.cadastro') }}">
                        <i class="fas fa-user-plus mr-1"></i>Funcionário
                    </a></li>
                    <li><a class="nav-link" href="{{ route('funcionario.index') }}">
                        <i class="fas fa-users mr-1"></i>Listar
                    </a></li>
                    <li><a class="nav-link" href="{{ route('produtos.procurar') }}">
                        <i class="fas fa-shoe-prints mr-1"></i>Produtos
                    </a></li>
                    <li><a class="nav-link relative" href="{{ route('carrinho.index') }}">
                        <i class="fas fa-shopping-cart mr-1"></i>Carrinho
                        @if(Session::has('carrinho') && count(Session::get('carrinho')) > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ count(Session::get('carrinho')) }}
                            </span>
                        @endif
                    </a></li>
                    <li><a class="nav-link" href="{{ route('parcelas.index') }}">
                        <i class="fas fa-file-invoice-dollar mr-1"></i>Parcelas
                    </a></li>
                    <li><a class="nav-link" href="{{ route('fluxo-caixa.index') }}">
                        <i class="fas fa-cash-register mr-1"></i>Fluxo de Caixa
                    </a></li>
                    <li><a class="nav-link" href="{{ route('mensagens-aviso.index') }}">
                        <i class="fas fa-bell mr-1"></i>Mensagens
                    </a></li>
                    <li><a class="nav-link" href="{{ route('baixa_fiscal.index') }}">
                        <i class="fas fa-file-invoice mr-1"></i>Baixa Fiscal
                    </a></li>
                    @if(Auth::check() && (Auth::user()->nivel === 'admin' || Auth::user()->limite))
                    <li><a class="nav-link" href="{{ route('verificacao-limite.index') }}">
                        <i class="fas fa-credit-card mr-1"></i>Verificar Limite
                    </a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="nav-link">
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
                <div class="px-4 py-2 space-y-2">
                    <a href="{{ route('funcionario.cadastro') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-user-plus mr-2"></i>Cadastrar Funcionário
                    </a>
                    <a href="{{ route('funcionario.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-users mr-2"></i>Listar Funcionários
                    </a>
                    <a href="{{ route('produtos.procurar') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-shoe-prints mr-2"></i>Produtos
                    </a>
                    <a href="{{ route('carrinho.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded relative">
                        <i class="fas fa-shopping-cart mr-2"></i>Carrinho
                        @if(Session::has('carrinho') && count(Session::get('carrinho')) > 0)
                            <span class="absolute top-1 right-3 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ count(Session::get('carrinho')) }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('parcelas.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Acompanhar Parcelas
                    </a>
                    <a href="{{ route('fluxo-caixa.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-cash-register mr-2"></i>Fluxo de Caixa
                    </a>
                    <a href="{{ route('mensagens-aviso.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-bell mr-2"></i>Mensagens de Aviso
                    </a>
                    <a href="{{ route('baixa_fiscal.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-file-invoice mr-2"></i>Baixa Fiscal
                    </a>
                    @if(Auth::check() && (Auth::user()->nivel === 'admin' || Auth::user()->limite))
                    <a href="{{ route('verificacao-limite.index') }}" class="block py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
                        <i class="fas fa-credit-card mr-2"></i>Verificar Limite
                    </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full text-left py-2 px-3 text-red-600 hover:bg-yellow-500 rounded">
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
