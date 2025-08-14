@extends('layouts.base')

@section('content')
<div class="content">
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
            <h1 class="text-3xl font-bold text-red-600">Configurar Multas e Juros</h1>
        </div>
        <p class="text-gray-600">Configure as taxas de multa, juros e prazos para cobrança de pagamentos em atraso</p>
    </div>

    <x-alert />

    <div class="form-container max-w-4xl mx-auto">
        <form action="{{ route('multa-configuracao.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Cards de Configuração -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Taxa de Multa -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-xl border-2 border-red-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-400 p-3 rounded-full mr-3">
                            <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-600">Taxa de Multa</h3>
                            <p class="text-sm text-gray-600">Percentual de multa por atraso</p>
                        </div>
                    </div>
                    <label for="taxa_multa" class="form-label text-red-600 mb-2">Percentual de Multa (%):</label>
                    <input type="number" name="taxa_multa" id="taxa_multa" 
                           class="form-input border-red-200 focus:border-red-400 focus:ring-red-200" 
                           value="{{ old('taxa_multa', $multaConfiguracao->taxa_multa) }}" 
                           min="0" max="100" step="0.01" required
                           placeholder="Ex: 2.00">
                    @error('taxa_multa')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Taxa de Juros -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl border-2 border-orange-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-orange-400 p-3 rounded-full mr-3">
                            <i class="fas fa-percentage text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-orange-600">Taxa de Juros</h3>
                            <p class="text-sm text-gray-600">Juros mensais sobre atraso</p>
                        </div>
                    </div>
                    <label for="taxa_juros" class="form-label text-orange-600 mb-2">Percentual de Juros (%):</label>
                    <input type="number" name="taxa_juros" id="taxa_juros" 
                           class="form-input border-orange-200 focus:border-orange-400 focus:ring-orange-200" 
                           value="{{ old('taxa_juros', $multaConfiguracao->taxa_juros) }}" 
                           min="0" max="100" step="0.01" required
                           placeholder="Ex: 1.00">
                    @error('taxa_juros')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dias para Cobrança -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border-2 border-blue-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-400 p-3 rounded-full mr-3">
                            <i class="fas fa-calendar-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-blue-600">Dias para Cobrança</h3>
                            <p class="text-sm text-gray-600">Quantidade total de dias em que os juros são cobrados</p>
                        </div>
                    </div>
                    <label for="dias_cobranca" class="form-label text-blue-600 mb-2">Dias para Cobrança:</label>
                    <input type="number" name="dias_cobranca" id="dias_cobranca" 
                           class="form-input border-blue-200 focus:border-blue-400 focus:ring-blue-200" 
                           value="{{ old('dias_cobranca', $multaConfiguracao->dias_cobranca) }}" 
                           min="1" max="365" required
                           placeholder="Ex: 30">
                    @error('dias_cobranca')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dias de Carência -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border-2 border-green-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-400 p-3 rounded-full mr-3">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-green-600">Dias de Carência</h3>
                            <p class="text-sm text-gray-600">Prazo antes de aplicar multa</p>
                        </div>
                    </div>
                    <label for="dias_carencia" class="form-label text-green-600 mb-2">Dias de Carência:</label>
                    <input type="number" name="dias_carencia" id="dias_carencia" 
                           class="form-input border-green-200 focus:border-green-400 focus:ring-green-200" 
                           value="{{ old('dias_carencia', $multaConfiguracao->dias_carencia) }}" 
                           min="0" max="90" required
                           placeholder="Ex: 5">
                    @error('dias_carencia')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Informações Importantes -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-yellow-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h4 class="text-lg font-semibold text-yellow-800 mb-2">Informações Importantes</h4>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <li>• A taxa de multa é aplicada uma única vez quando o pagamento atrasa</li>
                            <li>• A taxa de juros é aplicada mensalmente sobre o valor em atraso</li>
                            <li>• Os dias de carência devem ser menores ou iguais aos dias para cobrança</li>
                            <li>• Durante o período de carência, não são aplicadas multas nem juros</li>
                            <li>• Use 0 nos dias de carência para aplicar multa imediatamente após o vencimento</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-200">
                <a href="{{ route('dashboard') }}" class="btn-yellow order-2 sm:order-1">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard
                </a>
                
                <div class="flex gap-3 order-1 sm:order-2">
                    <button type="reset" class="px-6 py-3 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition-all duration-300 font-semibold shadow-md">
                        <i class="fas fa-undo mr-2"></i>Limpar
                    </button>
                    <button type="submit" class="btn-red">
                        <i class="fas fa-save mr-2"></i>Atualizar Configuração
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const taxaMultaInput = document.getElementById('taxa_multa');
    const taxaJurosInput = document.getElementById('taxa_juros');
    const diasCobrancaInput = document.getElementById('dias_cobranca');
    const diasCarenciaInput = document.getElementById('dias_carencia');
    const form = document.querySelector('form');

    // Validação em tempo real para taxas (0-100)
    [taxaMultaInput, taxaJurosInput].forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) {
                this.value = 0;
            } else if (value > 100) {
                this.value = 100;
            }
        });
    });

    // Validação para dias de cobrança (1-365)
    diasCobrancaInput.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value < 1) {
            this.value = 1;
        } else if (value > 365) {
            this.value = 365;
        }
        // Revalidar dias de carência quando dias de cobrança mudar
        validateCarencia();
    });

    // Validação para dias de carência (0-90 e <= dias_cobranca)
    diasCarenciaInput.addEventListener('input', function() {
        validateCarencia();
    });

    function validateCarencia() {
        const diasCobranca = parseInt(diasCobrancaInput.value) || 0;
        const diasCarencia = parseInt(diasCarenciaInput.value) || 0;
        
        if (diasCarencia < 0) {
            diasCarenciaInput.value = 0;
        } else if (diasCarencia > 90) {
            diasCarenciaInput.value = 90;
        } else if (diasCarencia > diasCobranca) {
            diasCarenciaInput.value = diasCobranca;
            showValidationMessage('Os dias de carência não podem ser maiores que os dias para cobrança.');
        }
    }

    // Efeitos visuais ao focar nos inputs
    const allInputs = document.querySelectorAll('input[type="number"]');
    allInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-opacity-50');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-opacity-50');
        });
    });

    // Validação antes do envio do formulário
    form.addEventListener('submit', function(e) {
        const diasCobranca = parseInt(diasCobrancaInput.value) || 0;
        const diasCarencia = parseInt(diasCarenciaInput.value) || 0;
        
        if (diasCarencia > diasCobranca) {
            e.preventDefault();
            showValidationMessage('Os dias de carência não podem ser maiores que os dias para cobrança.');
            diasCarenciaInput.focus();
            return false;
        }
    });

    // Função para mostrar mensagens de validação
    function showValidationMessage(message) {
        // Remove mensagem anterior se existir
        const existingMessage = document.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Cria nova mensagem
        const messageDiv = document.createElement('div');
        messageDiv.className = 'validation-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        messageDiv.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
        
        // Insere a mensagem no topo do formulário
        const formContainer = document.querySelector('.form-container');
        formContainer.insertBefore(messageDiv, formContainer.firstChild);
        
        // Remove a mensagem após 5 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endsection