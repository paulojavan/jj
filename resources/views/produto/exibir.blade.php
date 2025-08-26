@extends('layouts.base')
@section('content')

    <div class="content p-6">
        <x-alert />
        <div class="flex flex-col md:flex-row gap-6">
            {{-- Coluna da Imagem --}}
            <div class="md:w-1/3">
                {{-- Assumindo que a imagem está disponível em $produto->imagem_url ou similar --}}
                {{-- Se a imagem for salva localmente, ajuste o src --}}
                <img src="{{  asset('storage/uploads/produtos/'.$produto->foto) }}" alt="Imagem do Produto {{ $produto->nome ?? 'Produto' }}" class="w-full h-auto object-cover rounded shadow-lg">
                {{-- Adicione aqui a logo da loja se necessário, posicionada sobre a imagem --}}
            </div>

            {{-- Coluna de Detalhes --}}
            <div class="md:w-2/3 space-y-3 text-gray-700">
                <h1 class="text-3xl font-bold text-gray-800">{{ $produto->produto  }}</h1>
                <p><span class="font-semibold">Marca:</span> {{ $produto->marca ?? 'Marca não disponível' }}</p>
                <p><span class="font-semibold">Gênero:</span> {{ ucfirst($produto->genero ?? 'Gênero não disponível') }}</p>
                <p><span class="font-semibold">Grupo:</span> {{ $produto->grupo ?? 'Grupo não disponível' }}</p>
                <p><span class="font-semibold">Sub-Grupo:</span> {{ $produto->subgrupo ?? 'Sub-Grupo não disponível' }}</p>
                <p><span class="font-semibold">Código:</span> {{ $produto->codigo ?? 'Código não disponível' }}</p>
                <p><span class="font-semibold">Quantidade:</span> {{ $produto->quantidade ?? '0' }}</p>
                <p class="text-2xl font-semibold text-green-600"><span class="font-semibold">Preço:</span> R$ {{ number_format($produto->preco ?? 0, 2, ',', '.') }}</p>

                @php
                // Busca o nome da cidade usando o ID da cidade do usuário
                $numeracoes = collect(); // Inicializa como uma coleção vazia por padrão
                if (Auth::check() && isset(Auth::user()->cidade)) { // Verifica se o usuário está logado e tem cidade definida
                    $cidadeDoUsuario = DB::table('cidades')->where('id', Auth::user()->cidade)->first();
                    if ($cidadeDoUsuario) {
                        $nomeCidadeFormatado = strtolower(str_replace(' ', '_', $cidadeDoUsuario->cidade));
                        $nomeTabelaEstoque = 'estoque_' . $nomeCidadeFormatado;
                        try {
                             $numeracoes = DB::table($nomeTabelaEstoque)
                                ->where('id_produto', $produto->id)
                                ->where('quantidade', '>', 0)
                                ->select('numero', 'quantidade')
                                ->orderBy('numero')
                                ->get();
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Opcional: Logar o erro, ex: \Log::error("Erro ao buscar estoque para produto {$produto->id} na tabela {$nomeTabelaEstoque}: " . $e->getMessage());
                            $numeracoes = collect(); // Garante que $numeracoes é uma coleção vazia em caso de erro
                        }
                    }
                }
                @endphp

                @if($numeracoes->count() > 0)
                    @if (Auth::check() && isset(Auth::user()->nivel) && Auth::user()->nivel === 'administrador')
                        <div class="text-red-600 font-bold mb-4">
                            Administrador não pode realizar vendas, saia do sistema e entre em sua conta de funcionário.
                        </div>
                    @else
                        <form action="{{ route('carrinho.adicionar', $produto->id) }}" method="POST">
                            @csrf
                            <div class="mt-4">
                                <label for="numeracao" class="block font-semibold mb-2">Selecione a Numeração:</label>
                                <select id="numeracao" name="numeracao" class="w-full md:w-1/3 p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Escolha uma numeração</option>
                                    @foreach($numeracoes as $numeracao)
                                        <option value="{{ $numeracao->numero }}">Número {{ $numeracao->numero }} ({{ $numeracao->quantidade }} disponíveis)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn-green w-full md:w-auto">Adicionar ao Carrinho</button>
                            </div>
                        </form>
                    @endif
                @else
                    <p class="mt-4 text-gray-600">Este produto não possui numerações disponíveis no momento para sua cidade ou ocorreu um problema ao buscar o estoque.</p>
                @endif
            </div>
        </div>

        {{-- Sales History Section --}}
        @if(Auth::check() && $salesHistory->count() > 0)
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Últimas 30 Vendas</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dinheiro</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIX</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cartão</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numeração</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($salesHistory as $sale)
                                <tr class="{{ $sale->is_returned ? 'bg-red-100' : '' }}" data-sale-id="{{ $sale->id }}">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sale->vendedor_nome }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($sale->data_venda)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R$ {{ number_format($sale->valor_dinheiro ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R$ {{ number_format($sale->valor_pix ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        R$ {{ number_format($sale->valor_cartao ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sale->numeracao }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(!$sale->is_returned)
                                            <div class="flex space-x-2">
                                                {{-- Return Button --}}
                                                <button onclick="processReturn({{ $sale->id }})" 
                                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                                                    Devolução
                                                </button>
                                                
                                                {{-- Exchange Button and Select --}}
                                                <div class="flex items-center space-x-1">
                                                    <select id="exchange-size-{{ $sale->id }}" class="text-xs border border-gray-300 rounded px-2 py-1">
                                                        <option value="">Trocar para...</option>
                                                        @foreach($availableSizes as $size)
                                                            @if($size->numero != $sale->numeracao)
                                                                <option value="{{ $size->numero }}">{{ $size->numero }} ({{ $size->quantidade }})</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <button onclick="processExchange({{ $sale->id }})" 
                                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                                                        Trocar
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-red-600 text-xs font-semibold">DEVOLVIDO</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(Auth::check())
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Histórico de Vendas</h2>
                <p class="text-gray-600">Nenhuma venda encontrada nos últimos 30 registros para este produto em sua cidade.</p>
            </div>
        @endif

    </div>

@endsection

@push('scripts')
<script>
// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
console.log('CSRF Token found:', csrfToken);

if (!csrfToken) {
    console.error('CSRF token not found! Make sure meta tag is present.');
}

// Process return
function processReturn(saleId) {
    console.log('processReturn called with saleId:', saleId);
    console.log('CSRF Token:', csrfToken);
    
    // Validate sale ID
    if (!saleId || saleId <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'ID da venda inválido'
        });
        return;
    }

    // Mostrar loading enquanto busca informações
    Swal.fire({
        title: 'Carregando informações...',
        text: 'Por favor, aguarde',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Buscar informações do operador e vendedor
    fetch(`/produtos/venda-info/${saleId}`)
        .then(response => response.json())
        .then(data => {
            const operadorInfo = data.success ? data.operador_caixa : 'Não identificado';
            const vendedorInfo = data.success ? data.vendedor_atendente : 'Não identificado';

            // Show confirmation dialog with sale info
            Swal.fire({
                title: 'Confirmar Devolução',
                html: `
                    <div class="text-left">
                        <p><strong>ID da Venda:</strong> ${saleId}</p>
                        <br>
                        <p><strong>Operador de Caixa:</strong> ${operadorInfo}</p>
                        <p><strong>Vendedor Atendente:</strong> ${vendedorInfo}</p>
                        <br>
                        <p class="text-red-600">⚠️ Esta ação não pode ser desfeita!</p>
                        <p>Tem certeza que deseja processar a devolução deste item?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sim, devolver',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processReturnConfirmed(saleId);
                }
            });
        })
        .catch(error => {
            console.error('Erro ao buscar informações da venda:', error);
            
            // Exibir modal mesmo com erro
            Swal.fire({
                title: 'Confirmar Devolução',
                html: `
                    <div class="text-left">
                        <p><strong>ID da Venda:</strong> ${saleId}</p>
                        <br>
                        <p><strong>Operador de Caixa:</strong> Erro ao carregar</p>
                        <p><strong>Vendedor Atendente:</strong> Erro ao carregar</p>
                        <br>
                        <p class="text-red-600">⚠️ Esta ação não pode ser desfeita!</p>
                        <p>Tem certeza que deseja processar a devolução deste item?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sim, devolver',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processReturnConfirmed(saleId);
                }
            });
        });
}

// Process return after confirmation
function processReturnConfirmed(saleId) {
    // Show loading alert
    Swal.fire({
        title: 'Processando devolução...',
        text: 'Por favor, aguarde',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("produtos.processReturn") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            sale_id: saleId
        })
    })
    .then(response => {
        console.log('Return response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Return response data:', data);
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            });
            
            // Update the row to show as returned
            const row = document.querySelector(`tr[data-sale-id="${saleId}"]`);
            row.classList.add('bg-red-100');
            
            // Replace actions with "DEVOLVIDO" text
            const actionsCell = row.querySelector('td:last-child');
            actionsCell.innerHTML = '<span class="text-red-600 text-xs font-semibold">DEVOLVIDO</span>';
            
            // Refresh available sizes for other exchanges
            refreshAvailableSizes();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'Erro ao processar devolução';
        
        // Try to get more specific error message
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: errorMessage
        });
    });
}

// Process exchange
function processExchange(saleId) {
    console.log('processExchange called with saleId:', saleId);
    console.log('CSRF Token:', csrfToken);
    
    // Validate sale ID
    if (!saleId || saleId <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'ID da venda inválido'
        });
        return;
    }

    const sizeSelect = document.getElementById(`exchange-size-${saleId}`);
    if (!sizeSelect) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Seletor de numeração não encontrado'
        });
        return;
    }

    const newSize = sizeSelect.value;
    
    if (!newSize || newSize.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: 'Por favor, selecione uma numeração para troca'
        });
        return;
    }

    // Show confirmation dialog
    Swal.fire({
        title: 'Confirmar Troca',
        text: `Tem certeza que deseja trocar para a numeração ${newSize}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, trocar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        
        processExchangeConfirmed(saleId, newSize);
    });
}

// Process exchange after confirmation
function processExchangeConfirmed(saleId, newSize) {
    // Show loading alert
    Swal.fire({
        title: 'Processando troca...',
        text: 'Por favor, aguarde',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("produtos.processExchange") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            sale_id: saleId,
            new_size: newSize
        })
    })
    .then(response => {
        console.log('Exchange response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Exchange response data:', data);
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            });
            
            // Update the numeracao in the row
            const row = document.querySelector(`tr[data-sale-id="${saleId}"]`);
            const numeracaoCell = row.querySelector('td:nth-child(6)');
            numeracaoCell.textContent = newSize;
            
            // Refresh available sizes for all exchanges
            refreshAvailableSizes();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'Erro ao processar troca';
        
        // Try to get more specific error message
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: errorMessage
        });
    });
}

// Refresh available sizes for all exchange selects
function refreshAvailableSizes() {
    fetch(`{{ route("produtos.availableSizes", $produto->id) }}`)
    .then(response => response.json())
    .then(sizes => {
        // Update all exchange selects
        document.querySelectorAll('[id^="exchange-size-"]').forEach(select => {
            const currentSaleRow = select.closest('tr');
            const currentNumeracao = currentSaleRow.querySelector('td:nth-child(6)').textContent.trim();
            
            // Clear current options except the first one
            select.innerHTML = '<option value="">Trocar para...</option>';
            
            // Add available sizes (excluding current size)
            sizes.forEach(size => {
                if (size.numero != currentNumeracao) {
                    const option = document.createElement('option');
                    option.value = size.numero;
                    option.textContent = `${size.numero} (${size.quantidade})`;
                    select.appendChild(option);
                }
            });
        });
    })
    .catch(error => {
        console.error('Error refreshing sizes:', error);
    });
}


</script>
@endpush
