# Design Document

## Overview

O sistema de histórico de compras do cliente será implementado como uma extensão do módulo de clientes existente. A funcionalidade utilizará as tabelas `tickets` e `parcelas` para exibir informações completas sobre as compras realizadas por cada cliente, incluindo análise de comportamento de pagamento e perfil comercial.

## Architecture

### Componentes Principais

1. **Controller**: `ClienteController` (extensão) - Gerenciará as rotas e lógica de negócio
2. **Views**: Blade templates para exibição do histórico e detalhes
3. **Models**: Eloquent models para `Ticket` e `Parcela`
4. **Services**: Classe de serviço para cálculos de perfil de pagamento

### Fluxo de Dados

```
Cliente Search Page → Histórico Button → Purchase History Page → Purchase Details Page
                                      ↓
                                 Payment Profile Analysis
```

## Components and Interfaces

### 1. Routes (web.php)

```php
// Novas rotas para histórico de compras
Route::get('/clientes/{id}/historico', [ClienteController::class, 'historico'])->name('clientes.historico');
Route::get('/clientes/{id}/compra/{ticket}', [ClienteController::class, 'detalhesCompra'])->name('clientes.compra');
Route::get('/clientes/{id}/duplicata/{ticket}', [ClienteController::class, 'gerarDuplicata'])->name('clientes.duplicata');
Route::get('/clientes/{id}/carne/{ticket}', [ClienteController::class, 'gerarCarne'])->name('clientes.carne');
Route::post('/clientes/{id}/mensagem/{ticket}', [ClienteController::class, 'enviarMensagem'])->name('clientes.mensagem');
```

### 2. Controller Methods (ClienteController)

```php
public function historico($id, Request $request)
{
    // Buscar cliente
    // Buscar tickets paginados (20 por página)
    // Calcular perfil de pagamento
    // Retornar view com dados
}

public function detalhesCompra($id, $ticket)
{
    // Buscar detalhes da compra específica
    // Buscar produtos da compra
    // Retornar view de detalhes
}

public function gerarDuplicata($id, $ticket) { /* Lógica para duplicata */ }
public function gerarCarne($id, $ticket) { /* Lógica para carnê */ }
public function enviarMensagem($id, $ticket, Request $request) { /* Lógica para mensagem */ }
```

### 3. Models

#### Ticket Model
```php
class Ticket extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id_ticket';
    
    protected $fillable = [
        'id_cliente', 'ticket', 'data', 'valor', 'entrada', 'parcelas', 'spc'
    ];
    
    protected $casts = [
        'data' => 'datetime',
        'valor' => 'decimal:2',
        'entrada' => 'decimal:2'
    ];
    
    public function cliente() { return $this->belongsTo(Cliente::class, 'id_cliente'); }
    public function parcelas() { return $this->hasMany(Parcela::class, 'ticket', 'ticket'); }
}
```

#### Parcela Model
```php
class Parcela extends Model
{
    protected $table = 'parcelas';
    protected $primaryKey = 'id_parcelas';
    
    protected $fillable = [
        'ticket', 'id_cliente', 'numero', 'data_vencimento', 'data_pagamento',
        'valor_parcela', 'valor_pago', 'status'
    ];
    
    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor_parcela' => 'decimal:2',
        'valor_pago' => 'decimal:2'
    ];
    
    public function ticket() { return $this->belongsTo(Ticket::class, 'ticket', 'ticket'); }
    public function getStatusColorAttribute() { /* Lógica para cores */ }
    public function isVencida() { /* Verificar se está vencida */ }
}
```

### 4. Service Class

#### PaymentProfileService
```php
class PaymentProfileService
{
    public function calculateProfile($clienteId)
    {
        // Analisar padrão de pagamento (atrasado/no dia/adiantado)
        // Calcular taxa de devolução
        // Calcular total comprado (excluindo devoluções)
        // Determinar regularidade de compras
        // Encontrar primeira compra
        
        return [
            'payment_behavior' => 'atrasado|no_dia|adiantado',
            'return_rate' => 0.15, // 15%
            'total_purchased' => 15000.00,
            'first_purchase' => '2023-01-15',
            'purchase_frequency' => 'regular|irregular'
        ];
    }
}
```

## Data Models

### Database Tables Used

1. **tickets**
   - `id_ticket` (PK)
   - `id_cliente` (FK)
   - `ticket` (string, 60 chars)
   - `data` (datetime)
   - `valor` (decimal 15,2)
   - `entrada` (decimal 15,2)
   - `parcelas` (integer)
   - `spc` (string, nullable)

2. **parcelas**
   - `id_parcelas` (PK)
   - `ticket` (FK to tickets.ticket)
   - `id_cliente` (FK)
   - `numero` (string)
   - `data_vencimento` (date)
   - `data_pagamento` (date, nullable)
   - `valor_parcela` (decimal 15,2)
   - `valor_pago` (decimal 15,2, nullable)
   - `status` (string)

### Status Color Mapping

- **Verde**: `status` indica pagamento realizado E `data_pagamento` não é null
- **Preto**: `status` não indica pagamento E `data_vencimento` >= hoje
- **Vermelho**: `status` não indica pagamento E `data_vencimento` < hoje
- **Amarelo**: `status` = 'devolucao'

## Error Handling

### Validation Rules

1. **Cliente ID**: Deve existir na tabela clientes
2. **Ticket**: Deve existir e pertencer ao cliente
3. **Paginação**: Validar parâmetros de página

### Error Scenarios

1. **Cliente não encontrado**: Redirect com mensagem de erro
2. **Sem compras**: Exibir mensagem informativa
3. **Ticket inválido**: Redirect para histórico com erro
4. **Erro de banco**: Log error e exibir mensagem genérica

## Testing Strategy

### Unit Tests

1. **PaymentProfileService**
   - Teste cálculo de comportamento de pagamento
   - Teste cálculo de taxa de devolução
   - Teste determinação de regularidade

2. **Models**
   - Teste relationships entre Ticket e Parcela
   - Teste métodos de status e cores
   - Teste casts de data e decimal

### Feature Tests

1. **Histórico de Compras**
   - Teste exibição de lista paginada
   - Teste filtros e ordenação
   - Teste perfil de pagamento

2. **Detalhes da Compra**
   - Teste exibição de parcelas
   - Teste cores de status
   - Teste botões de ação

### Integration Tests

1. **Fluxo Completo**
   - Teste navegação desde pesquisa de cliente
   - Teste expansão de cards
   - Teste geração de documentos

## UI/UX Design

### Layout Structure

```
┌─────────────────────────────────────┐
│ Header: Cliente Info + Breadcrumb   │
├─────────────────────────────────────┤
│ Purchase Cards (Expandable)         │
│ ┌─────────────────────────────────┐ │
│ │ Ticket | Data | Valor | Entrada │ │
│ │ [Expandir para ver parcelas]    │ │
│ └─────────────────────────────────┘ │
├─────────────────────────────────────┤
│ Pagination Controls                 │
├─────────────────────────────────────┤
│ Payment Profile Analysis            │
└─────────────────────────────────────┘
```

### Responsive Design

- **Desktop**: Cards em grid 2 colunas
- **Tablet**: Cards em coluna única
- **Mobile**: Cards compactos com informações essenciais

### Color Scheme

- **Verde (#10B981)**: Parcelas pagas
- **Preto (#374151)**: Parcelas em dia
- **Vermelho (#EF4444)**: Parcelas vencidas
- **Amarelo (#F59E0B)**: Devoluções

## Performance Considerations

### Database Optimization

1. **Indexes**: Criar índices em `tickets.id_cliente` e `parcelas.ticket`
2. **Pagination**: Usar Laravel pagination para limitar resultados
3. **Eager Loading**: Carregar relacionamentos para evitar N+1 queries

### Caching Strategy

1. **Profile Cache**: Cache perfil de pagamento por 1 hora
2. **Static Data**: Cache dados que não mudam frequentemente

### Query Optimization

```sql
-- Otimizar consulta principal
SELECT t.*, COUNT(p.id_parcelas) as total_parcelas
FROM tickets t
LEFT JOIN parcelas p ON t.ticket = p.ticket
WHERE t.id_cliente = ?
GROUP BY t.id_ticket
ORDER BY t.data DESC
LIMIT 20 OFFSET ?
```