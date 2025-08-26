# Design Document

## Overview

A funcionalidade de devolução de compras no crediário será implementada como uma extensão do sistema existente de histórico de compras. O design seguirá o padrão MVC do Laravel, integrando-se com os modelos existentes (Ticket, Parcela, Cliente) e utilizando a estrutura atual de controllers e views.

## Architecture

### High-Level Architecture

```
Frontend (Blade View)
    ↓
ClienteController (New Method)
    ↓
CreditReturnService (New Service)
    ↓
Models (Ticket, Parcela, Sales Tables, Stock Tables)
    ↓
Database (Multiple Tables)
```

### Component Interaction Flow

1. **User Interface**: Botão de devolução no histórico de compras
2. **Controller**: Novo método no ClienteController para processar devoluções
3. **Service Layer**: CreditReturnService para lógica de negócio
4. **Model Layer**: Interação com Ticket, Parcela e tabelas dinâmicas
5. **Database Layer**: Transações para garantir consistência

## Components and Interfaces

### Frontend Components

#### 1. Purchase History View Enhancement
- **File**: `resources/views/cliente/historico-compras.blade.php`
- **Enhancement**: Adicionar botão "Devolução" ao lado do botão "Ver Compra Completa"
- **Logic**: 
  - Verificar se alguma parcela foi paga usando `$ticket->parcelasRelacao->where('status', '!=', 'aguardando pagamento')->count() > 0`
  - Desabilitar botão se houver parcelas pagas
  - Adicionar modal de confirmação com JavaScript

#### 2. Confirmation Modal
- **Component**: Modal JavaScript com SweetAlert2 (seguindo padrão existente)
- **Content**: 
  - Título: "Confirmar Devolução"
  - Detalhes: Ticket, valor total, número de parcelas
  - Botões: Confirmar/Cancelar

### Backend Components

#### 1. Controller Method
- **File**: `app/Http/Controllers/ClienteController.php`
- **New Method**: `processarDevolucao(Request $request, $clienteId, $ticketNumber)`
- **Responsibilities**:
  - Validar permissões
  - Validar se devolução é possível
  - Chamar service para processar devolução
  - Retornar resposta JSON

#### 2. Service Class
- **File**: `app/Services/CreditReturnService.php` (New)
- **Methods**:
  - `canReturn(string $ticketNumber): bool`
  - `processReturn(string $ticketNumber): array`
  - `updateParcelas(string $ticketNumber): void`
  - `updateSalesRecords(string $ticketNumber): void`
  - `updateInventory(string $ticketNumber): void`

#### 3. Model Enhancements
- **Ticket Model**: Adicionar método `canBeReturned()`
- **Parcela Model**: Método já existe `isDevolucao()`

### Database Interactions

#### 1. Parcelas Table Update
```sql
UPDATE parcelas 
SET status = 'devolucao' 
WHERE ticket = ? AND status != 'pago'
```

#### 2. Sales Tables Update (Dynamic)
```sql
UPDATE vendas_{cidade} 
SET data_estorno = CURRENT_DATE, baixa_fiscal = 1 
WHERE ticket = ?
```

#### 3. Stock Tables Update (Dynamic)
```sql
UPDATE estoque_{cidade} 
SET quantidade = quantidade + ? 
WHERE id_produto = ?
```

## Data Models

### Existing Models (No Changes Required)

#### Ticket Model
- **Table**: `tickets`
- **Key Fields**: `id_ticket`, `ticket`, `id_cliente`, `valor`, `entrada`, `parcelas`
- **New Method**: `canBeReturned(): bool`

#### Parcela Model
- **Table**: `parcelas`
- **Key Fields**: `ticket`, `status`, `data_pagamento`
- **Existing Method**: `isDevolucao(): bool`

#### Sales Tables (Dynamic)
- **Tables**: `vendas_{cidade}` (e.g., `vendas_princesa`, `vendas_tabira`)
- **Key Fields**: `ticket`, `data_estorno`, `baixa_fiscal`, `id_produto`, `quantidade`

#### Stock Tables (Dynamic)
- **Tables**: `estoque_{cidade}` (e.g., `estoque_princesa`, `estoque_tabira`)
- **Key Fields**: `id_produto`, `quantidade`

### Data Flow

1. **Input**: Ticket number from frontend
2. **Validation**: Check if return is possible (no paid installments)
3. **Transaction Start**
4. **Update Parcelas**: Set status to 'devolucao'
5. **Update Sales**: Set data_estorno and baixa_fiscal
6. **Update Stock**: Increment quantities
7. **Transaction Commit/Rollback**
8. **Response**: Success/error message

## Error Handling

### Validation Errors
- **Invalid Ticket**: Ticket não encontrado
- **Already Returned**: Compra já devolvida
- **Has Payments**: Não é possível devolver compra com parcelas pagas
- **Permission Denied**: Usuário sem permissão

### Database Errors
- **Transaction Failure**: Rollback automático
- **Table Not Found**: Tabela de vendas/estoque não existe
- **Constraint Violation**: Violação de integridade referencial

### Error Response Format
```json
{
    "success": false,
    "message": "Mensagem de erro específica",
    "code": "ERROR_CODE"
}
```

## Testing Strategy

### Unit Tests
- **CreditReturnService**: Testar cada método isoladamente
- **Model Methods**: Testar `canBeReturned()` e validações
- **Controller**: Testar validações e respostas

### Integration Tests
- **Full Return Process**: Testar fluxo completo de devolução
- **Database Transactions**: Testar rollback em caso de erro
- **Multi-table Updates**: Verificar consistência entre tabelas

### Test Data Setup
- **Test Cities**: Usar cidades de teste (test_city)
- **Test Tables**: Criar tabelas temporárias para testes
- **Mock Data**: Tickets, parcelas e vendas de teste

### Test Scenarios
1. **Successful Return**: Devolução bem-sucedida
2. **Return with Paid Installments**: Deve falhar
3. **Already Returned**: Deve falhar
4. **Invalid Ticket**: Deve falhar
5. **Database Error**: Deve fazer rollback

## Security Considerations

### Authentication
- **User Session**: Verificar se usuário está logado
- **Permission Check**: Verificar se usuário tem permissão para devoluções

### Authorization
- **Role-based**: Apenas funcionários autorizados podem processar devoluções
- **Client Access**: Verificar se usuário tem acesso ao cliente

### Data Validation
- **Input Sanitization**: Validar ticket number format
- **SQL Injection Prevention**: Usar prepared statements
- **CSRF Protection**: Token CSRF em formulários

### Audit Trail
- **Log Operations**: Registrar todas as devoluções processadas
- **User Tracking**: Registrar qual usuário processou a devolução
- **Timestamp**: Registrar data/hora da operação

## Performance Considerations

### Database Optimization
- **Indexes**: Garantir índices em campos de busca (ticket, id_cliente)
- **Transaction Size**: Manter transações pequenas e rápidas
- **Connection Pooling**: Usar pool de conexões do Laravel

### Caching Strategy
- **No Caching**: Dados de devolução devem ser sempre atuais
- **Cache Invalidation**: Invalidar cache de histórico após devolução

### Scalability
- **Batch Processing**: Para múltiplas devoluções (futuro)
- **Queue Jobs**: Para operações pesadas (futuro)
- **Database Sharding**: Considerar para múltiplas cidades

## Integration Points

### Existing Systems
- **Purchase History**: Integração com histórico existente
- **Payment System**: Verificação de pagamentos
- **Inventory System**: Atualização de estoque
- **Fiscal System**: Atualização de baixa fiscal

### External Dependencies
- **SweetAlert2**: Para modais de confirmação
- **Laravel Framework**: Controllers, Models, Migrations
- **Database**: MySQL/SQLite para persistência

### API Endpoints
- **POST** `/clientes/{id}/devolucao/{ticket}`: Processar devolução
- **GET** `/clientes/{id}/historico-compras`: Visualizar histórico (existing)

## Deployment Considerations

### Database Changes
- **No Migrations Required**: Usando estrutura existente
- **Index Verification**: Verificar performance de queries

### Code Deployment
- **New Service Class**: Adicionar ao autoloader
- **Controller Enhancement**: Método adicional
- **View Updates**: Modificações no template

### Rollback Plan
- **Code Rollback**: Reverter alterações de código
- **Data Consistency**: Verificar integridade após rollback
- **User Communication**: Informar sobre indisponibilidade temporária