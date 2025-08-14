# Design Document

## Overview

This feature enhances the product detail view (`resources/views/produto/exibir.blade.php`) by adding a comprehensive sales history section that displays the last 30 sales of the current product in the seller's city. The system will provide interactive functionality for processing returns and size exchanges while maintaining accurate inventory tracking.

## Architecture

### Database Schema
The system uses city-based sales tables with the naming convention `vendas_{cidade_name}` (e.g., `vendas_tabira`, `vendas_princesa`). Each sales table contains the following relevant fields:

- `id_vendedor` - Seller who made the sale
- `id_produto` - Product ID
- `data_venda` - Sale date
- `valor_dinheiro` - Cash payment amount
- `valor_pix` - PIX payment amount  
- `valor_cartao` - Card payment amount
- `numeracao` - Size/number sold
- `data_estorno` - Return date (null if not returned)
- `ticket` - Transaction ticket (null for individual sales)

### City-Based Inventory Tables
Inventory is managed through city-specific tables with the naming convention `estoque_{cidade_name}` containing:
- `id_produto` - Product ID
- `numero` - Size/number
- `quantidade` - Available quantity

## Components and Interfaces

### 1. Sales History Display Component
**Location**: Added to `resources/views/produto/exibir.blade.php`

**Functionality**:
- Displays last 30 sales in a responsive table format
- Shows seller name, sale date, payment breakdown, and size
- Applies red background for returned items
- Provides action buttons for non-returned items

**Data Structure**:
```php
$salesHistory = [
    'id_venda' => int,
    'vendedor_nome' => string,
    'data_venda' => date,
    'valor_dinheiro' => decimal,
    'valor_pix' => decimal, 
    'valor_cartao' => decimal,
    'numeracao' => string,
    'data_estorno' => date|null,
    'is_returned' => boolean
];
```

### 2. Return Processing Component
**Functionality**:
- Updates `data_estorno` field with current date
- Increments inventory for the returned size
- Refreshes the sales history display

**Process Flow**:
1. Validate sale is not already returned
2. Set `data_estorno` to current date
3. Add 1 unit to inventory for the returned size
4. Add 1 unit to main product quantity in produtos table
5. Return success response

### 3. Size Exchange Component
**Functionality**:
- Displays available sizes in dropdown
- Processes size exchanges with inventory updates
- Updates the sale record with new size

**Process Flow**:
1. Load available sizes with stock > 0
2. Validate selected size has inventory
3. Add 1 unit to original size inventory
4. Subtract 1 unit from new size inventory
5. Update sale record's `numeracao` field
6. Return success response

**Note**: Size exchanges do not affect the main product quantity in the produtos table since we're only swapping one size for another (no net change in total inventory).

### 4. Controller Methods
**Location**: `app/Http/Controllers/ProdutoController.php`

**New Methods**:
- `getSalesHistory($productId)` - Retrieves last 30 sales
- `processReturn($saleId)` - Handles product returns
- `processExchange($saleId, $newSize)` - Handles size exchanges
- `getAvailableSizes($productId)` - Gets sizes with stock

## Data Models

### Sales Query Structure
```php
$salesQuery = DB::table($salesTable)
    ->select([
        'id',
        'id_vendedor',
        'data_venda',
        'valor_dinheiro',
        'valor_pix', 
        'valor_cartao',
        'numeracao',
        'data_estorno'
    ])
    ->where('id_produto', $productId)
    ->whereNull('ticket')
    ->orderBy('data_venda', 'desc')
    ->limit(30);
```

### Inventory Query Structure
```php
$inventoryQuery = DB::table($stockTable)
    ->select(['numero', 'quantidade'])
    ->where('id_produto', $productId)
    ->where('quantidade', '>', 0)
    ->orderBy('numero');
```

### User/Seller Join
```php
$salesWithSellers = DB::table($salesTable)
    ->leftJoin('users', $salesTable . '.id_vendedor', '=', 'users.id')
    ->select([
        $salesTable . '.*',
        'users.name as vendedor_nome'
    ]);
```

## Error Handling

### Database Errors
- **Missing Sales Table**: Display message "Tabela de vendas não encontrada para sua cidade"
- **Missing Stock Table**: Display message "Tabela de estoque não encontrada para sua cidade"
- **Connection Issues**: Display generic error message and log details

### Business Logic Errors
- **Already Returned**: Display "Este item já foi devolvido"
- **No Stock for Exchange**: Display "Numeração selecionada sem estoque disponível"
- **Invalid Sale ID**: Display "Venda não encontrada"

### Validation Errors
- **Missing Parameters**: Validate all required fields before processing
- **Invalid Size Selection**: Ensure selected size exists and has stock
- **Permission Checks**: Verify user has appropriate permissions

## Testing Strategy

### Unit Tests
1. **Sales History Retrieval**
   - Test with valid product ID
   - Test with non-existent product
   - Test with no sales history
   - Test limit of 30 records

2. **Return Processing**
   - Test successful return
   - Test already returned item
   - Test inventory update
   - Test invalid sale ID

3. **Exchange Processing**
   - Test successful exchange
   - Test insufficient stock
   - Test inventory updates (both sizes)
   - Test sale record update

### Integration Tests
1. **Full Workflow Tests**
   - View product → See sales history → Process return
   - View product → See sales history → Process exchange
   - Test with multiple cities/users

2. **Database Integrity Tests**
   - Verify inventory consistency after operations
   - Test concurrent operations
   - Verify transaction rollback on errors

### Frontend Tests
1. **UI Component Tests**
   - Sales history table rendering
   - Action buttons visibility
   - Form submissions
   - Error message display

2. **JavaScript Functionality**
   - AJAX requests for returns/exchanges
   - Dynamic size dropdown updates
   - Success/error notifications

## Security Considerations

### Authorization
- Verify user is logged in and has appropriate permissions
- Ensure user can only see sales from their city
- Validate user permissions for return/exchange operations

### Data Validation
- Sanitize all input parameters
- Validate sale ownership before processing
- Verify inventory availability before exchanges

### CSRF Protection
- Include CSRF tokens in all forms
- Validate tokens on all POST requests

## Performance Considerations

### Database Optimization
- Index on `id_produto`, `data_venda`, and `ticket` fields
- Limit queries to 30 records maximum
- Use appropriate table joins for seller information

### Caching Strategy
- Cache available sizes for short periods
- Cache sales history for 1-2 minutes to reduce database load
- Clear cache after inventory changes

### Frontend Optimization
- Use AJAX for return/exchange operations to avoid full page reloads
- Implement loading states for better user experience
- Minimize DOM updates during operations