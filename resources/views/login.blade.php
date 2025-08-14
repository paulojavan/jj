<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJ Calçados - Login</title>
    <link rel="icon" href="{{ asset('storage/uploads/sistema/icon.png') }}">
    @vite('resources/css/app.css')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-yellow-400 via-yellow-500 to-red-500 flex items-center justify-center p-4">
    <!-- Container Principal -->
    <div class="w-full max-w-md">
        <!-- Card de Login -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border-4 border-red-600">
            <!-- Header com Logo -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 text-center relative">
                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2">
                    <br><br>
                    <div class="bg-white rounded-full p-3 shadow-lg border-4 border-yellow-400">
                        <img class="h-16 w-16 object-contain"
                             src="{{ asset('storage/uploads/sistema/login.png') }}"
                             alt="JJ Calçados Logo"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="h-16 w-16 bg-yellow-400 rounded-full flex items-center justify-center text-red-600 font-bold text-2xl" style="display: none;">

                        </div>
                    </div>
                </div><br><br><BR>
                <div class="mt-8">
                    <h1 class="text-2xl font-bold text-white mb-1">JJ Calçados</h1>
                    <p class="text-yellow-200 text-sm">Sistema de Gestão</p>
                </div>
            </div>

            <!-- Formulário -->
            <div class="p-8">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-semibold text-red-600 mb-2">Acesso ao Sistema</h2>
                    <p class="text-gray-600 text-sm">Digite suas credenciais para continuar</p>
                </div>

                <x-alert />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Campo Usuário -->
                    <div class="space-y-2">
                        <label for="login" class="block text-sm font-semibold text-red-600">
                            <i class="fas fa-user mr-1"></i>Usuário
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-yellow-600"></i>
                            </div>
                            <input type="text"
                                   name="login"
                                   id="login"
                                   placeholder="Digite seu usuário"
                                   value="{{ old('login') }}"
                                   class="w-full pl-10 pr-4 py-3 border-2 border-yellow-300 rounded-lg focus:border-yellow-500 focus:ring focus:ring-yellow-200 focus:outline-none transition-colors @error('login') border-red-500 @enderror"
                                   required />
                        </div>
                        @error('login')
                            <p class="text-red-500 text-sm mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Campo Senha -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-red-600">
                            <i class="fas fa-lock mr-1"></i>Senha
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-yellow-600"></i>
                            </div>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   placeholder="Digite sua senha"
                                   class="w-full pl-10 pr-12 py-3 border-2 border-yellow-300 rounded-lg focus:border-yellow-500 focus:ring focus:ring-yellow-200 focus:outline-none transition-colors @error('password') border-red-500 @enderror"
                                   required />
                            <button type="button"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword()">
                                <i id="password-icon" class="fas fa-eye text-gray-400 hover:text-yellow-600 transition-colors"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Botão de Login -->
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <i class="fas fa-sign-in-alt mr-2"></i>ENTRAR
                    </button>
                </form>

                <!-- Informações Adicionais -->
                <div class="mt-6 text-center">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600">
                            <i class="fas fa-info-circle text-yellow-600 mr-1"></i>
                            Sistema exclusivo para funcionários autorizados
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 border-t">
                <div class="flex items-center justify-center text-xs text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Acesso seguro e protegido
                </div>
            </div>
        </div>

        <!-- Informações de Contato -->
        <div class="mt-6 text-center">
            <p class="text-white text-sm opacity-90">
                <i class="fas fa-phone mr-1"></i>
                Problemas com acesso? Entre em contato com o administrador
            </p>
        </div>
    </div>

    <script>
        // Função para mostrar/ocultar senha
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const loginCard = document.querySelector('.bg-white');
            loginCard.style.opacity = '0';
            loginCard.style.transform = 'translateY(20px)';

            setTimeout(() => {
                loginCard.style.transition = 'all 0.6s ease-out';
                loginCard.style.opacity = '1';
                loginCard.style.transform = 'translateY(0)';
            }, 100);
        });

        // Alertas personalizados
        @if(session('horario_error'))
            Swal.fire({
                icon: 'error',
                title: 'Acesso Negado',
                text: '{{ session('horario_error') }}',
                confirmButtonColor: '#dc2626',
                background: '#fef2f2',
                color: '#dc2626',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#059669',
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#dc2626'
            });
        @endif
    </script>
</body>
</html>
