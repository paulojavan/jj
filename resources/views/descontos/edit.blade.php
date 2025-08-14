@extends('layouts.base')

@section('content')
<div class="content">
    <div class="mb-6">
        <div class="flex items-center mb-4">
            <i class="fas fa-percentage text-red-600 text-2xl mr-3"></i>
            <h1 class="text-3xl font-bold text-red-600">Configurar Descontos</h1>
        </div>
        <p class="text-gray-600">Configure os percentuais de desconto para cada forma de pagamento</p>
    </div>

    <x-alert />

    <div class="form-container max-w-4xl mx-auto">
        <form action="{{ route('descontos.update', $desconto->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Cards de Desconto -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- À Vista -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-xl border-2 border-yellow-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-400 p-3 rounded-full mr-3">
                            <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-600">À Vista</h3>
                            <p class="text-sm text-gray-600">Pagamento em dinheiro</p>
                        </div>
                    </div>
                    <label for="avista" class="form-label text-red-600 mb-2">Percentual de Desconto (%):</label>
                    <input type="number" name="avista" id="avista" 
                           class="form-input" 
                           value="{{ old('avista', $desconto->avista) }}" 
                           min="0" max="100" step="0.01" required
                           placeholder="Ex: 5.00">
                    @error('avista')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PIX -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border-2 border-green-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-400 p-3 rounded-full mr-3">
                            <i class="fas fa-mobile-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-green-600">PIX</h3>
                            <p class="text-sm text-gray-600">Pagamento instantâneo</p>
                        </div>
                    </div>
                    <label for="pix" class="form-label text-green-600 mb-2">Percentual de Desconto (%):</label>
                    <input type="number" name="pix" id="pix" 
                           class="form-input border-green-200 focus:border-green-400 focus:ring-green-200" 
                           value="{{ old('pix', $desconto->pix) }}" 
                           min="0" max="100" step="0.01" required
                           placeholder="Ex: 3.00">
                    @error('pix')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Débito -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border-2 border-blue-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-400 p-3 rounded-full mr-3">
                            <i class="fas fa-credit-card text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-blue-600">Cartão de Débito</h3>
                            <p class="text-sm text-gray-600">Débito em conta</p>
                        </div>
                    </div>
                    <label for="debito" class="form-label text-blue-600 mb-2">Percentual de Desconto (%):</label>
                    <input type="number" name="debito" id="debito" 
                           class="form-input border-blue-200 focus:border-blue-400 focus:ring-blue-200" 
                           value="{{ old('debito', $desconto->debito) }}" 
                           min="0" max="100" step="0.01" required
                           placeholder="Ex: 2.00">
                    @error('debito')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Crédito -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border-2 border-purple-200">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-400 p-3 rounded-full mr-3">
                            <i class="fas fa-credit-card text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-purple-600">Cartão de Crédito</h3>
                            <p class="text-sm text-gray-600">Crédito parcelado</p>
                        </div>
                    </div>
                    <label for="credito" class="form-label text-purple-600 mb-2">Percentual de Desconto (%):</label>
                    <input type="number" name="credito" id="credito" 
                           class="form-input border-purple-200 focus:border-purple-400 focus:ring-purple-200" 
                           value="{{ old('credito', $desconto->credito) }}" 
                           min="0" max="100" step="0.01" required
                           placeholder="Ex: 1.00">
                    @error('credito')
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
                            <li>• Os descontos são aplicados automaticamente no carrinho</li>
                            <li>• Valores devem ser inseridos em percentual (ex: 5.00 para 5%)</li>
                            <li>• Descontos maiores incentivam formas de pagamento específicas</li>
                            <li>• Use 0 para desabilitar desconto em uma forma de pagamento</li>
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
                        <i class="fas fa-save mr-2"></i>Atualizar Descontos
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona validação em tempo real
    const inputs = document.querySelectorAll('input[type="number"]');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) {
                this.value = 0;
            } else if (value > 100) {
                this.value = 100;
            }
        });
        
        // Adiciona efeito visual ao focar
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-opacity-50');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-opacity-50');
        });
    });
});
</script>
@endsection
