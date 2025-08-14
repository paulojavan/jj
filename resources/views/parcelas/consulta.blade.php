<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJ Calçados - Acompanhar Parcelas</title>
    <link rel="icon" href="{{ asset('storage/uploads/sistema/icon.png') }}">
    @vite('resources/css/app.css')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-yellow-400 via-yellow-500 to-red-500 flex items-center justify-center p-4">
    <!-- Container Principal -->
    <div class="w-full max-w-md">
        <!-- Card de Consulta -->
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
                            JJ
                        </div>
                    </div>
                </div><br><br><BR>
                <div class="mt-8">
                    <h1 class="text-2xl font-bold text-white mb-1">JJ Calçados</h1>
                    <p class="text-yellow-200 text-sm">Acompanhamento de Parcelas</p>
                </div>
            </div>

            <!-- Formulário -->
            <div class="p-8">
                <div class="mb-6 text-center">
                    <h2 class="text-xl font-semibold text-red-600 mb-2">Consulte suas Parcelas</h2>
                    <p class="text-gray-600 text-sm">Digite seu CPF para consultar suas parcelas pendentes</p>
                </div>

                <!-- Mensagens de erro -->
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('parcelas.consultar') }}" id="consultaForm" class="space-y-6">
                    @csrf

                    <!-- Campo CPF -->
                    <div class="space-y-2">
                        <label for="cpf" class="block text-sm font-semibold text-red-600">
                            <i class="fas fa-id-card mr-1"></i>CPF
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-yellow-600"></i>
                            </div>
                            <input type="text"
                                   name="cpf"
                                   id="cpf"
                                   placeholder="000.000.000-00"
                                   value="{{ old('cpf') }}"
                                   maxlength="14"
                                   class="w-full pl-10 pr-4 py-3 border-2 border-yellow-300 rounded-lg focus:border-yellow-500 focus:ring focus:ring-yellow-200 focus:outline-none transition-colors text-center @error('cpf') border-red-500 @enderror"
                                   required
                                   aria-describedby="cpf-help"
                                   autocomplete="off" />
                        </div>
                        @error('cpf')
                            <p class="text-red-500 text-sm mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Botão de Consulta -->
                    <button type="submit"
                            id="consultarBtn"
                            class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <i class="fas fa-search mr-2"></i>CONSULTAR PARCELAS
                    </button>
                </form>

                <!-- Informações Adicionais -->
                <div class="mt-6 text-center">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600" id="cpf-help">
                            <i class="fas fa-info-circle text-yellow-600 mr-1"></i>
                            Digite seu CPF completo para consultar suas parcelas em aberto
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 border-t">
                <div class="flex items-center justify-center text-xs text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Consulta segura e protegida
                </div>
            </div>
        </div>

        <!-- Link para voltar ao sistema -->
        <div class="mt-6 text-center">
            <a href="{{ route('dashboard') }}" class="text-white text-sm opacity-90 hover:opacity-100 transition-opacity">
                <i class="fas fa-arrow-left mr-1"></i>
                Voltar ao Sistema
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cpfInput = document.getElementById('cpf');
            const consultarBtn = document.getElementById('consultarBtn');
            const form = document.getElementById('consultaForm');
            
            // Animação de entrada
            const consultaCard = document.querySelector('.bg-white');
            consultaCard.style.opacity = '0';
            consultaCard.style.transform = 'translateY(20px)';

            setTimeout(() => {
                consultaCard.style.transition = 'all 0.6s ease-out';
                consultaCard.style.opacity = '1';
                consultaCard.style.transform = 'translateY(0)';
            }, 100);
            
            // Aplicar máscara de CPF
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value;
                
                // Remove todos os caracteres não numéricos
                value = value.replace(/\D/g, '');
                
                // Limita a 11 dígitos
                if (value.length > 11) {
                    value = value.slice(0, 11);
                }
                
                // Aplica a máscara
                if (value.length > 0) {
                    if (value.length > 3) {
                        value = value.substring(0, 3) + '.' + value.substring(3);
                    }
                    if (value.length > 7) {
                        value = value.substring(0, 7) + '.' + value.substring(7);
                    }
                    if (value.length > 11) {
                        value = value.substring(0, 11) + '-' + value.substring(11);
                    }
                }
                
                e.target.value = value;
            });
            
            // Validação básica de CPF
            function validarCPF(cpf) {
                cpf = cpf.replace(/\D/g, '');
                
                if (cpf.length !== 11) return false;
                
                // Verifica se todos os dígitos são iguais
                if (/^(\d)\1{10}$/.test(cpf)) return false;
                
                return true;
            }
            
            // Validação no envio do formulário
            form.addEventListener('submit', function(e) {
                const cpfValue = cpfInput.value;
                
                if (!validarCPF(cpfValue)) {
                    e.preventDefault();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'CPF Inválido',
                        text: 'Verifique o formato do CPF e tente novamente.',
                        confirmButtonColor: '#dc2626',
                        background: '#fef2f2',
                        color: '#dc2626'
                    });
                    
                    cpfInput.focus();
                    return false;
                }
                
                // Mostra loading no botão
                consultarBtn.disabled = true;
                consultarBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>CONSULTANDO...';
            });
            
            // Foco automático no campo CPF
            cpfInput.focus();
        });

        // Alertas personalizados para mensagens de sessão
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