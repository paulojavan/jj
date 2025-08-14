# Design Document

## Overview

The credit sale feature extends the existing CarrinhoController to provide a comprehensive credit-based sales system. The feature implements a multi-step workflow that includes customer search, status validation, credit limit verification, installment configuration, token authentication, and final transaction processing. The system integrates with existing customer management, installment tracking, and sales recording infrastructure.

The implementation leverages the existing session-based cart management and builds upon the current sales processing logic while adding specialized credit sale functionality including customer search, credit validation, installment calculations, and token-based authorization.

## Architecture

### High-Level Flow
1. User clicks "venda crediário" button from cart view
2. System redirects to customer search page with search functionality
3. System validates customer status and determines available actions
4. For active customers with current data, system proceeds to credit sale configuration
5. System validates credit limits and calculates available credit
6. System provides installment configuration interface with payment options
7. System handles token verification for transaction authorization
8. System processes complete credit sale with ticket generation and installment creation
9. System integrates with existing sales recording logic

### Integration Points
- **CarrinhoController**: Extends existing controller with credit sale methods
- **Customer Management**: Integrates with clientes and autorizados tables
- **Credit System**: Utilizes tickets and parcelas tables for installment tracking
- **Sales Processing**: Leverages existing finalizarCompra logic for final transaction
- **Session Management**: Extends current cart and payment session handling

## Components and Interfaces

### CarrinhoController Enhancement

#### vendaCrediario Method
```php
public function vendaCrediario(Request $request)
```
**Responsibilities:**
- Redirect to customer search page
- Initialize credit sale session data

#### pesquisarCliente Method
```php
public function pesquisarCliente(Request $request)
```
**Responsibilities:**
- Handle customer search by nome, apelido, rg, cpf
- Display search results with customer status validation
- Provide appropriate action buttons based on customer status

#### selecionarCliente Method
```php
public function selecionarCliente($clienteId)
```
**Responsibilities:**
- Validate customer credit limits
- Check for overdue payments
- Calculate available credit and minimum entry requirements
- Display credit sale configuration interface

#### configurarVendaCrediario Method
```php
public function configurarVendaCrediario(Request $request)
```
**Responsibilities:**
- Handle entry payment configuration
- Process installment calculations
- Manage token verification
- Provide final sale authorization interface

#### finalizarVendaCrediario Method
```php
public function finalizarVendaCrediario(Request $request)
```
**Responsibilities:**
- Generate unique ticket for the sale
- Create installment records in parcelas table
- Process final sale using existing sales logic
- Clear session data and provide confirmation

### Supporting Private Methods

#### searchCustomers()
```php
private function searchCustomers(string $searchTerm): Collection
```
- Performs customer search across nome, apelido, rg, cpf fields
- Returns collection of matching customers

#### validateCustomerStatus()
```php
private function validateCustomerStatus($cliente): array
```
- Checks customer status (ativo/inativo)
- Validates atualizacao date for active customers
- Checks for SPC records in tickets/parcelas tables
- Returns status validation results with appropriate actions

#### calculateAvailableCredit()
```php
private function calculateAvailableCredit($clienteId): array
```
- Sums pending installments (status = 'aguardando pagamento')
- Calculates available credit (limite - pending amount)
- Identifies overdue payments
- Returns credit calculation results

#### calculateMinimumEntry()
```php
private function calculateMinimumEntry(float $purchaseTotal, float $availableCredit): float
```
- Calculates minimum entry when purchase exceeds available credit
- Returns half of the excess amount as minimum entry

#### generatePaymentDates()
```php
private function generatePaymentDates(string $selectedDay): array
```
- Generates payment date options (10th, 20th, last day of month)
- Provides dates for next month and one additional month
- Handles month-end date calculations

#### calculateInstallments()
```php
private function calculateInstallments(float $amount, int $parcelas): array
```
- Divides amount by number of installments
- Rounds up to next integer for all installments except last
- Calculates last installment as remainder to ensure exact total
- Validates minimum R$20 per installment

#### generateUniqueTicket()
```php
private function generateUniqueTicket(): string
```
- Generates unique ticket identifier for the sale
- Ensures uniqueness across tickets table

#### createTicketRecord()
```php
private function createTicketRecord(array $ticketData): void
```
- Creates record in tickets table with sale information
- Populates all required fields according to schema

#### createInstallmentRecords()
```php
private function createInstallmentRecords(array $installmentData): void
```
- Creates individual records in parcelas table for each installment
- Handles date calculations and field population

### Database Schema Integration

#### Tickets Table Structure
```sql
id_ticket int(255) UNSIGNED NOT NULL AUTO_INCREMENT,
id_cliente int(255) NOT NULL,
ticket varchar(60) NOT NULL,
data datetime NOT NULL,
valor double(15,2) NOT NULL,
entrada double(15,2) NOT NULL,
parcelas int(5) NOT NULL,
spc varchar(10) DEFAULT NULL
```

#### Parcelas Table Structure
```sql
id_parcelas int(255) UNSIGNED NOT NULL AUTO_INCREMENT,
ticket varchar(60) NOT NULL,
id_cliente int(255) NOT NULL,
id_autorizado int(255) DEFAULT NULL,
numero varchar(11) NOT NULL,
data_vencimento date NOT NULL,
data_pagamento date DEFAULT NULL,
hora time DEFAULT NULL,
valor_parcela double(15,2) NOT NULL,
valor_pago double(15,2) DEFAULT NULL,
dinheiro double(15,2) DEFAULT NULL,
pix double(15,2) DEFAULT NULL,
cartao double(15,2) DEFAULT NULL,
metodo varchar(11) DEFAULT NULL,
id_vendedor int(255) DEFAULT NULL,
status varchar(30) NOT NULL,
bd varchar(100) NOT NULL,
ticket_pagamento varchar(60) DEFAULT NULL,
lembrete varchar(20) DEFAULT NULL,
primeira varchar(10) DEFAULT NULL,
segunda varchar(10) DEFAULT NULL,
terceira varchar(10) DEFAULT NULL,
quarta varchar(10) DEFAULT NULL,
quinta varchar(10) DEFAULT NULL,
sexta varchar(10) DEFAULT NULL,
setima varchar(10) DEFAULT NULL,
oitava varchar(10) DEFAULT NULL,
nona varchar(10) DEFAULT NULL
```

## Data Models

### Customer Search Results
```php
[
    'id' => customer_id,
    'nome' => customer_name,
    'apelido' => nickname,
    'rg' => rg_number,
    'cpf' => cpf_number,
    'status' => 'ativo|inativo',
    'atualizacao' => update_date,
    'limite' => credit_limit,
    'action_type' => 'select|verify|blocked|negativado'
]
```

### Credit Validation Results
```php
[
    'available_credit' => calculated_available_amount,
    'pending_amount' => sum_of_pending_installments,
    'overdue_payments' => boolean,
    'overdue_count' => number_of_overdue_installments,
    'minimum_entry_required' => boolean,
    'minimum_entry_amount' => calculated_minimum_entry
]
```

### Installment Configuration
```php
[
    'purchase_total' => cart_total,
    'entry_amount' => user_specified_entry,
    'entry_method' => 'dinheiro|pix|cartao',
    'installment_amount' => amount_to_be_financed,
    'number_of_installments' => selected_parcelas,
    'first_payment_date' => selected_date,
    'payment_day' => '10|20|ultimo',
    'buyer_type' => 'titular|autorizado',
    'buyer_id' => selected_buyer_id
]
```

### Session Data Extensions

#### Credit Sale Session (`venda_crediario`)
```php
[
    'cliente_id' => selected_customer_id,
    'cliente_data' => customer_information,
    'credit_validation' => credit_calculation_results,
    'installment_config' => installment_configuration,
    'token_verified' => boolean,
    'ticket_generated' => unique_ticket_id
]
```

## User Interface Design

### Customer Search Page
- Search form with fields for nome, apelido, rg, cpf
- Search results table with customer information
- Action buttons based on customer status:
  - "Selecionar Cliente" (enabled) - for active customers with current data
  - "Verificar Número" (enabled) - for active customers with outdated data
  - "Cliente Negativado" (disabled) - for inactive customers with SPC records
  - "Cliente Bloqueado" (disabled) - for inactive customers without SPC records

### Credit Sale Configuration Page
**Left Side - Customer Information:**
- Nome
- RG
- CPF
- Limite Total
- Limite Disponível

**Right Side - Purchase Configuration:**
- Valor Total da Compra
- Entry Payment Section:
  - Input field for entry amount
  - Selection for payment method (dinheiro, pix, cartão)
  - "Aplicar Valor da Entrada" button
- Token Verification Section:
  - Token input field
  - "Enviar Token" and "Verificar Token" buttons
- Installment Configuration:
  - First payment date selection (10th, 20th, last day)
  - Number of installments (1-12)
  - Calculated installment amount display
- Buyer Selection:
  - Titular (default)
  - Authorized buyers from autorizados table
- "Finalizar Venda" button (enabled after token verification)

## Business Logic

### Customer Status Validation Logic
1. **Inactive Customers:**
   - Query tickets table for records with matching id_cliente
   - Check parcelas table for any parcela with spc = "true"
   - If SPC records exist: Display "Cliente Negativado" (disabled)
   - If no SPC records: Display "Cliente Bloqueado" (disabled)

2. **Active Customers:**
   - Check atualizacao field date
   - If date equals today: Display "Selecionar Cliente" (enabled)
   - If date is not today: Display "Verificar Número" (enabled)

### Credit Limit Calculation
```sql
SELECT SUM(valor_parcela) as pending_amount 
FROM parcelas 
WHERE id_cliente = ? AND status = 'aguardando pagamento'
```
Available Credit = cliente.limite - pending_amount

### Minimum Entry Calculation
When purchase_total > available_credit:
minimum_entry = (purchase_total - available_credit) / 2

### Installment Date Generation
- **Day 10:** Next month 10th, following month 10th
- **Day 20:** Next month 20th, following month 20th  
- **Last Day:** Next month last day, following month last day

### Installment Amount Calculation
```php
$installment_amount = ceil(($purchase_total - $entry_amount) / $number_of_installments);
$last_installment = ($purchase_total - $entry_amount) - ($installment_amount * ($number_of_installments - 1));
```

## Error Handling

### Customer Search Errors
- **No Results Found:** Display "Nenhum cliente encontrado" message
- **Database Connection:** Display generic error, log specific details
- **Invalid Search Terms:** Validate input format and provide feedback

### Credit Validation Errors
- **Overdue Payments:** Display alert with overdue count, prevent sale
- **Insufficient Credit:** Display minimum entry requirement
- **Invalid Customer Data:** Redirect to customer edit or display error

### Token Verification Errors
- **Invalid Token:** Display error message, keep "Finalizar Venda" disabled
- **Token Verification Failure:** Log error, display user-friendly message

### Transaction Processing Errors
- **Ticket Generation Failure:** Rollback transaction, display error
- **Installment Creation Failure:** Rollback all changes, maintain cart state
- **Sales Integration Failure:** Complete rollback, log detailed error information

## Testing Strategy

### Unit Tests

#### Customer Search Tests
- `testSearchCustomersByNome()`: Verify name-based search functionality
- `testSearchCustomersByRgCpf()`: Test document-based search
- `testValidateCustomerStatusActive()`: Test active customer validation
- `testValidateCustomerStatusInactive()`: Test inactive customer validation with SPC checks

#### Credit Validation Tests
- `testCalculateAvailableCredit()`: Verify credit calculation logic
- `testCalculateMinimumEntry()`: Test minimum entry calculations
- `testCheckOverduePayments()`: Validate overdue payment detection

#### Installment Calculation Tests
- `testCalculateInstallments()`: Verify installment amount calculations
- `testGeneratePaymentDates()`: Test date generation for different day options
- `testValidateMinimumInstallmentAmount()`: Ensure R$20 minimum per installment

#### Token Verification Tests
- `testTokenVerification()`: Validate token matching logic
- `testTokenVerificationFailure()`: Test invalid token handling

### Integration Tests
- `testCompleteCreditsaleFlow()`: End-to-end credit sale process
- `testCustomerSearchToSaleCompletion()`: Full workflow integration
- `testCreditSaleWithExistingSalesLogic()`: Verify integration with existing sales system
- `testSessionManagementThroughoutFlow()`: Test session data handling

### Database Tests
- `testTicketRecordCreation()`: Verify ticket table insertions
- `testInstallmentRecordCreation()`: Test parcelas table record creation
- `testCreditLimitCalculationQueries()`: Validate database queries for credit calculations

### Edge Case Testing
- Customer with exactly zero available credit
- Purchase amount exactly matching available credit
- Maximum number of installments (12) with minimum amounts
- Token verification with special characters
- Date calculations for month-end scenarios
- Concurrent credit sales for same customer

The testing strategy ensures robust validation of the complete credit sale workflow while maintaining integration with existing systems and providing reliable error handling throughout the process.