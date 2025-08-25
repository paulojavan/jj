# Design Document - Negativação de Clientes

## Overview

A funcionalidade de negativação de clientes será implementada como um módulo administrativo que permite identificar, processar e gerenciar clientes com parcelas em atraso significativo. O sistema utilizará a estrutura existente do Laravel com controllers, models e views, seguindo os padrões já estabelecidos no projeto JJ Calçados.

## Architecture

### Controller Structure
- `NegativacaoController` - Controller principal para gerenciar todas as operações de negativação
- Métodos principais:
  - `index()` - Lista clientes elegíveis para negativação
  - `show($cliente)` - Detalhes do cliente elegível
  - `negativar($cliente)` - Processa a negativação
  - `negativados()` - Lista clientes já negativados
  - `showNegativado($cliente)` - Detalhes do cliente negativado
  - `retornarParcelas($cliente)` - Reverte parcelas pagas
  - `removerNegativacao($cliente)` - Remove negativação completa

### Route Structure
```php
Route::middleware(['auth', 'admin'])->prefix('negativacao')->group(function () {
    Route::get('/', [NegativacaoController::class, 'index'])->name('negativacao.index');
    Route::get('/cliente/{cliente}', [NegativacaoController::class, 'show'])->name('negativacao.show');
    Route::post('/negativar/{cliente}', [NegativacaoController::class, 'negativar'])->name('negativacao.negativar');
    Route::get('/negativados', [NegativacaoController::class, 'negativados'])->name('negativacao.negativados');
    Route::get('/negativado/{cliente}', [NegativacaoController::class, 'showNegativado'])->name('negativacao.show-negativado');
    Route::post('/retornar-parcelas/{cliente}', [NegativacaoController::class, 'retornarParcelas'])->name('negativacao.retornar-parcelas');
    Route::post('/remover/{cliente}', [NegativacaoController::class, 'removerNegativacao'])->name('negativacao.remover');
});
```

### Middleware Requirements
- Autenticação obrigatória (`auth`)
- Middleware customizado `admin` para verificar se o usuário tem nível de administrador

## Components and Interfaces

### 1. Identificação de Clientes Elegíveis

**Query Logic:**
```sql
SELECT DISTINCT c.* FROM clientes c
INNER JOIN parcelas p ON c.id = p.id_cliente
LEFT JOIN tickets t ON c.id = t.id_cliente
WHERE p.status = 'aguardando pagamento'
AND p.data_vencimento < DATE_SUB(NOW(), INTERVAL 60 DAY)
AND (t.spc IS NULL OR t.spc = false)
AND c.status != 'inativo'
```

**Service Method:**
```php
public function getClientesElegiveis()
{
    return Cliente::whereHas('parcelas', function ($query) {
        $query->where('status', 'aguardando pagamento')
              ->where('data_vencimento', '<', Carbon::now()->subDays(60));
    })
    ->whereDoesntHave('tickets', function ($query) {
        $query->where('spc', true);
    })
    ->where('status', '!=', 'inativo')
    ->with(['parcelas' => function ($query) {
        $query->where('status', 'aguardando pagamento')
              ->where('data_vencimento', '<', Carbon::now()->subDays(60));
    }])
    ->get();
}
```

### 2. Cálculo de Valores com Juros e Multa

**Reutilização da Lógica Existente:**
Baseado no `PagamentoController`, será criado um service para centralizar o cálculo:

```php
class CalculoParcelaService
{
    public function calcularValorComJurosMulta(Parcela $parcela): float
    {
        $multaConfig = MultaConfiguracao::first();
        $today = Carbon::today();
        $dataVencimento = Carbon::parse($parcela->data_vencimento);
        $diffDias = $today->diffInDays($dataVencimento, false);
        
        $diasAtraso = $diffDias < 0 ? abs($diffDias) : 0;
        $valorAPagar = $parcela->valor_parcela;
        
        if ($diasAtraso > $multaConfig->dias_carencia) {
            $taxaJurosDiaria = $multaConfig->taxa_juros / 30;
            $diasParaJuros = min($diasAtraso, $multaConfig->dias_cobranca);
            $valorJuros = ($parcela->valor_parcela * ($taxaJurosDiaria / 100)) * $diasParaJuros;
            $valorMulta = $parcela->valor_parcela * ($multaConfig->taxa_multa / 100);
            $valorAPagar += $valorMulta + $valorJuros;
        }
        
        return round($valorAPagar, 2);
    }
}
```

### 3. Processo de Negativação

**Transaction Logic:**
```php
public function processarNegativacao(Cliente $cliente): bool
{
    DB::beginTransaction();
    try {
        // 1. Buscar parcelas aguardando pagamento
        $parcelas = $cliente->parcelas()
            ->where('status', 'aguardando pagamento')
            ->get();
        
        // 2. Buscar tickets relacionados
        $ticketsIds = $parcelas->pluck('ticket')->unique();
        
        // 3. Atualizar campo spc nos tickets
        Ticket::whereIn('ticket', $ticketsIds)->update(['spc' => true]);
        
        // 4. Atualizar status do cliente
        $cliente->update([
            'status' => 'inativo',
            'obs' => 'cliente negativado'
        ]);
        
        DB::commit();
        return true;
    } catch (Exception $e) {
        DB::rollback();
        throw $e;
    }
}
```

### 4. Middleware de Administrador

```php
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->nivel !== 'administrador') {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta funcionalidade.');
        }
        
        return $next($request);
    }
}
```

## Data Models

### Existing Models Usage

**Cliente Model:**
- Campos utilizados: `id`, `nome`, `cpf`, `foto`, `telefone`, `status`, `obs`
- Relacionamentos: `parcelas()`, `tickets()`, `autorizados()`

**Parcela Model:**
- Campos utilizados: `id_parcelas`, `ticket`, `id_cliente`, `id_autorizado`, `data_vencimento`, `valor_parcela`, `status`, `data_pagamento`, `hora`, `valor_pago`, `dinheiro`, `pix`, `cartao`, `metodo`, `id_vendedor`, `ticket_pagamento`
- Relacionamentos: `cliente()`, `autorizado()`, `ticket()`

**Ticket Model:**
- Campos utilizados: `id_ticket`, `ticket`, `id_cliente`, `spc`, `data`, `valor`
- Relacionamentos: `cliente()`, `parcelasRelacao()`

**Autorizado Model:**
- Campos utilizados: `id`, `idCliente`, `nome`, `cpf`, `foto`
- Relacionamentos: `cliente()`

### New Model Extensions

Não são necessárias alterações nos models existentes, apenas adição de métodos auxiliares:

```php
// Cliente Model - Métodos adicionais
public function isElegivelNegativacao(): bool
{
    return $this->parcelas()
        ->where('status', 'aguardando pagamento')
        ->where('data_vencimento', '<', Carbon::now()->subDays(60))
        ->exists() && 
        !$this->tickets()->where('spc', true)->exists() &&
        $this->status !== 'inativo';
}

public function isNegativado(): bool
{
    return $this->status === 'inativo' && $this->obs === 'cliente negativado';
}
```

## Error Handling

### Exception Types
1. **ValidationException** - Dados inválidos ou cliente não elegível
2. **DatabaseException** - Erros de transação no banco
3. **AuthorizationException** - Usuário sem permissão de administrador
4. **BusinessLogicException** - Regras de negócio violadas

### Error Response Strategy
```php
try {
    // Operação de negativação
} catch (ValidationException $e) {
    return redirect()->back()->with('error', 'Dados inválidos: ' . $e->getMessage());
} catch (DatabaseException $e) {
    Log::error('Erro na negativação: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro interno. Tente novamente.');
} catch (Exception $e) {
    Log::error('Erro inesperado na negativação: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro inesperado. Contate o suporte.');
}
```

## Testing Strategy

### Unit Tests
1. **CalculoParcelaServiceTest** - Testa cálculos de juros e multa
2. **NegativacaoServiceTest** - Testa lógica de negócio
3. **ClienteModelTest** - Testa métodos auxiliares do model

### Feature Tests
1. **NegativacaoControllerTest** - Testa todas as rotas e fluxos
2. **AdminMiddlewareTest** - Testa controle de acesso
3. **NegativacaoIntegrationTest** - Testa fluxo completo end-to-end

### Test Data Setup
```php
// Factory para cliente com parcelas vencidas
$cliente = Cliente::factory()->create();
$parcelas = Parcela::factory()->count(3)->create([
    'id_cliente' => $cliente->id,
    'status' => 'aguardando pagamento',
    'data_vencimento' => Carbon::now()->subDays(70)
]);
```

## User Interface Design

### Page Structure

**1. Lista de Clientes Elegíveis (`negativacao/index.blade.php`)**
- Layout: Tabela responsiva com Tailwind CSS
- Colunas: Foto, Nome, CPF, Parcelas em Atraso, Valor Total, Ações
- Filtros: Busca por nome/CPF, ordenação por valor/data
- Paginação: 20 itens por página

**2. Detalhes do Cliente Elegível (`negativacao/show.blade.php`)**
- Seção superior: Foto, dados pessoais, botão WhatsApp
- Seção parcelas titular: Tabela com parcelas, valores calculados
- Seção parcelas autorizados: Agrupadas por autorizado
- Seção inferior: Resumo total, botão negativar com confirmação

**3. Lista de Clientes Negativados (`negativacao/negativados.blade.php`)**
- Similar à lista de elegíveis, com status visual diferenciado
- Coluna adicional: Data da negativação
- Filtros específicos para negativados

**4. Detalhes do Cliente Negativado (`negativacao/show-negativado.blade.php`)**
- Layout similar ao detalhamento de elegível
- Botões: WhatsApp, Retornar Parcelas, Remover Negativação
- Indicadores visuais de status negativado

### Component Reusability
- `@include('components.cliente-card')` - Card de informações do cliente
- `@include('components.parcelas-table')` - Tabela de parcelas
- `@include('components.whatsapp-button')` - Botão WhatsApp
- `@include('components.confirmation-modal')` - Modal de confirmação

### Navigation Integration
Adicionar ao menu administrativo existente:
```php
// resources/views/layouts/admin-nav.blade.php
<li class="nav-item">
    <a class="nav-link" href="{{ route('negativacao.index') }}">
        <i class="fas fa-exclamation-triangle"></i>
        Negativação SPC
    </a>
    <ul class="nav-submenu">
        <li><a href="{{ route('negativacao.index') }}">Clientes para Negativar</a></li>
        <li><a href="{{ route('negativacao.negativados') }}">Clientes Negativados</a></li>
    </ul>
</li>
```

## Security Considerations

### Access Control
- Middleware `admin` obrigatório em todas as rotas
- Verificação dupla no controller para nível de administrador
- CSRF protection em todos os formulários

### Data Protection
- Logs de auditoria para todas as operações de negativação
- Backup automático antes de operações críticas
- Validação rigorosa de dados de entrada

### Business Rules Validation
- Verificação de elegibilidade antes de cada operação
- Prevenção de negativação duplicada
- Validação de integridade referencial antes de alterações