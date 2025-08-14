# Design Document

## Overview

The finalize purchase feature extends the existing CarrinhoController to process shopping cart items and create sales records in city-specific MySQL tables. The system leverages the existing session-based cart management and user authentication to create comprehensive sales records that track payment methods, pricing, discounts, and user relationships.

The implementation builds upon the existing cart infrastructure, utilizing the current session management for cart items, discount calculations, and payment method distribution. The feature integrates with the existing user-city relationship system to determine the appropriate sales table for each transaction.

## Architecture

### High-Level Flow
1. User clicks "finalizar compra" button from cart view
2. System validates user authentication and cart contents
3. System determines target sales table based on user's city
4. System processes each cart item and creates individual sales records
5. System uses database transactions to ensure data integrity
6. System clears cart session data upon successful completion
7. System provides user feedback and redirects appropriately

### Integration Points
- **CarrinhoController**: Extends existing controller with finalizarCompra method
- **User Model**: Utilizes existing cidade field for table determination
- **Session Management**: Leverages existing cart, discount, and payment session data
- **Database**: Creates records in existing vendas_tabira and vendas_princesa tables

## Components and Interfaces

### CarrinhoController Enhancement

#### finalizarCompra Method
```php
public function finalizarCompra(Request $request)
```

**Responsibilities:**
- Validate user authentication and cart contents
- Determine target sales table based on user city
- Process cart items and create sales records
- Handle database transactions for data integrity
- Clear session data upon success
- Provide user feedback

**Input:** HTTP Request (POST)
**Output:** Redirect response with success/error messages

#### Supporting Private Methods

##### determineSalesTable()
```php
private function determineSalesTable(User $user): string
```
- Maps user's cidade field to appropriate sales table name
- Validates city configuration
- Returns table name or throws exception for invalid cities

##### createSalesRecord()
```php
private function createSalesRecord(array $cartItem, string $salesTable, array $paymentData): void
```
- Creates individual sales record for cart item
- Populates all required fields according to database schema
- Handles pricing calculations with discounts

##### calculateItemPaymentDistribution()
```php
private function calculateItemPaymentDistribution(array $cartItem, array $totalPayments, float $cartTotal): array
```
- Distributes payment method amounts proportionally per item
- Ensures accurate payment tracking per product

##### validatePurchaseData()
```php
private function validatePurchaseData(): array
```
- Validates cart contents, user authentication, and payment data
- Returns structured validation results

### Database Schema Integration

#### Sales Table Structure
Both vendas_tabira and vendas_princesa tables follow identical schema:

**Primary Fields:**
- `id_vendas`: Auto-increment primary key
- `id_vendedor`: Foreign key to users.id (logged-in user)
- `id_vendedor_atendente`: Foreign key to users.id (attendant if specified)
- `id_produto`: Foreign key to produtos.id

**Transaction Fields:**
- `data_venda`: Current date (YYYY-MM-DD)
- `hora`: Current time (HH:MM:SS)
- `preco`: Original product price without discount
- `preco_venda`: Final product price with discount applied
- `numeracao`: Product size/numbering

**Payment Fields:**
- `valor_dinheiro`: Cash payment amount (from valor_avista session)
- `valor_pix`: PIX payment amount
- `valor_cartao`: Card payment amount
- `valor_crediario`: Store credit payment amount

**Status Fields:**
- `desconto`: Boolean (always FALSE/0)
- `alerta`: Boolean (always FALSE/0)
- `baixa_fiscal`: Boolean (always FALSE/0)

**Null Fields:**
- `data_estorno`, `pedido_devolucao`, `reposicao`, `bd`, `ticket`: All set to NULL

## Data Models

### Session Data Structure

#### Cart Session (`carrinho`)
```php
[
    'itemId' => [
        'id' => product_id,
        'nome' => product_name,
        'preco' => original_price,
        'numeracao' => size,
        'quantidade' => quantity,
        'foto' => image_path
    ]
]
```

#### Discount Session (`descontos_aplicados`)
```php
[
    'avista' => formatted_amount,
    'pix' => formatted_amount,
    'cartao' => formatted_amount,
    'crediario' => formatted_amount,
    'tipo_selecionado' => discount_type,
    'modo_manual' => boolean
]
```

#### Client/Vendor Session (`cliente_vendedor`)
```php
[
    'nome_cliente' => client_name,
    'vendedor_atendente_id' => attendant_user_id
]
```

### Payment Distribution Logic

For each cart item, payment amounts are distributed proportionally based on the item's contribution to the total cart value:

```
item_payment_amount = (item_total / cart_total) * total_payment_method_amount
```

This ensures accurate payment tracking when multiple items are purchased with mixed payment methods.

### City-to-Table Mapping

The system maps user cidade values to sales table names:
- Tabira (cidade ID) → vendas_tabira
- Princesa Isabel (cidade ID) → vendas_princesa

The mapping is validated against the existing cidades table to ensure data integrity.

## Error Handling

### Validation Errors
- **Empty Cart**: Display error message, redirect to cart
- **Unauthenticated User**: Redirect to login
- **Invalid City**: Display configuration error message
- **Missing Payment Data**: Display validation error with specific requirements

### Database Errors
- **Transaction Failures**: Rollback all changes, display generic error message
- **Connection Issues**: Log error details, display user-friendly message
- **Constraint Violations**: Log specific constraint, display validation error

### Recovery Mechanisms
- **Partial Failures**: Complete transaction rollback maintains cart state
- **Session Persistence**: Cart data remains available for retry attempts
- **Error Logging**: Detailed logging for debugging and monitoring

## Testing Strategy

### Unit Tests

#### CarrinhoController Tests
- `testFinalizarCompraWithValidData()`: Verify successful purchase processing
- `testFinalizarCompraWithEmptyCart()`: Validate empty cart handling
- `testFinalizarCompraWithInvalidCity()`: Test city validation
- `testDetermineSalesTable()`: Verify table name mapping
- `testCreateSalesRecord()`: Validate sales record creation
- `testCalculateItemPaymentDistribution()`: Test payment distribution logic

#### Integration Tests
- `testCompleteCheckoutFlow()`: End-to-end purchase process
- `testDatabaseTransactionRollback()`: Verify transaction integrity
- `testSessionDataClearing()`: Confirm session cleanup after purchase
- `testMultipleItemsWithMixedPayments()`: Complex payment scenarios

#### Database Tests
- `testSalesRecordCreation()`: Verify correct data insertion
- `testForeignKeyConstraints()`: Validate relationship integrity
- `testCityTableMapping()`: Confirm table selection logic

### Test Data Requirements
- Sample users with different cidade values
- Test products with various pricing scenarios
- Mock session data for different payment combinations
- Database fixtures for both sales tables

### Edge Case Testing
- Cart with single item vs. multiple items
- Manual discount mode vs. automatic discounts
- Zero discount scenarios
- Maximum value transactions
- Concurrent user sessions

The testing strategy ensures robust validation of the purchase flow while maintaining data integrity and providing reliable user feedback throughout the process.