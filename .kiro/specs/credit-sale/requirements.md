# Requirements Document

## Introduction

This feature implements the "venda crediário" (credit sale) functionality for the JJ Calçados shopping cart system. When users click the credit sale button from the cart, the system will guide them through a customer search process, validate customer status and credit limits, handle installment calculations, and process credit-based sales with proper token verification and payment entry handling.

## Requirements

### Requirement 1

**User Story:** As a sales employee, I want to initiate a credit sale from the shopping cart, so that I can sell products to customers using store credit with installment payments.

#### Acceptance Criteria

1. WHEN a user clicks the "venda crediário" button from the cart THEN the system SHALL redirect to a customer search page
2. WHEN displaying the customer search page THEN the system SHALL provide search fields for nome, apelido, rg, and cpf
3. WHEN a user performs a customer search THEN the system SHALL query the cliente table using the provided search criteria
4. WHEN search results are found THEN the system SHALL display matching customers with their basic information

### Requirement 2

**User Story:** As a sales employee, I want to validate customer status before proceeding with credit sales, so that I can ensure customers are eligible for credit purchases.

#### Acceptance Criteria

1. WHEN a customer has status "inativo" THEN the system SHALL check the tickets table for parcelas with spc = "true" for that customer
2. IF parcelas with spc = "true" exist THEN the system SHALL display a disabled "cliente negativado" button
3. IF no parcelas with spc = "true" exist THEN the system SHALL display a disabled "cliente bloqueado" button
4. WHEN a customer has status "ativo" THEN the system SHALL check the atualização field date
5. IF the atualização date equals today's date THEN the system SHALL display an enabled "selecionar cliente" button
6. IF the atualização date is not today's date THEN the system SHALL display an enabled "verificar número" button

### Requirement 3

**User Story:** As a sales employee, I want to redirect customers for number verification when needed, so that customer information stays current before credit sales.

#### Acceptance Criteria

1. WHEN the "verificar número" button is clicked THEN the system SHALL redirect to the cliente edit route
2. WHEN redirecting to cliente edit THEN the system SHALL pass the customer ID as a parameter
3. WHEN returning from cliente edit THEN the system SHALL re-evaluate the customer's atualização status

### Requirement 4

**User Story:** As a sales employee, I want to validate customer credit limits before finalizing credit sales, so that customers don't exceed their available credit.

#### Acceptance Criteria

1. WHEN "selecionar cliente" is clicked THEN the system SHALL calculate the customer's available credit limit
2. WHEN calculating available credit THEN the system SHALL sum all parcelas with status "aguardando pagamento" for the customer
3. WHEN calculating available credit THEN the system SHALL subtract the sum from the customer's limite field
4. IF the purchase total exceeds available credit THEN the system SHALL calculate minimum entry as half of the excess amount
5. IF the purchase total exceeds available credit THEN the system SHALL display an alert requiring entry payment
6. WHEN checking for overdue payments THEN the system SHALL verify parcelas with data_vencimento before today's date
7. IF overdue parcelas exist THEN the system SHALL display an alert and prevent the sale

### Requirement 5

**User Story:** As a sales employee, I want to configure credit sale details including entry payment and installments, so that I can customize the payment terms for each customer.

#### Acceptance Criteria

1. WHEN displaying the credit sale configuration page THEN the system SHALL show customer information on the left side (Nome, RG, CPF, Limite total, Limite disponível)
2. WHEN displaying the credit sale configuration page THEN the system SHALL show purchase information on the right side (valor total da compra)
3. WHEN configuring entry payment THEN the system SHALL provide an input field for entry amount
4. WHEN configuring entry payment THEN the system SHALL provide selection options for dinheiro, pix, or cartão
5. WHEN configuring entry payment THEN the system SHALL provide an "aplicar valor da entrada" button
6. WHEN configuring installments THEN the system SHALL provide date selection for first payment (10th, 20th, or last day of month)
7. WHEN displaying payment dates THEN the system SHALL show options for next month and one additional month
8. WHEN configuring installments THEN the system SHALL allow selection of 1-12 parcelas with minimum R$20 per installment

### Requirement 6

**User Story:** As a sales employee, I want to handle authorized buyers and token verification, so that credit sales are properly authorized and secure.

#### Acceptance Criteria

1. WHEN configuring the sale THEN the system SHALL provide a field to select the buyer (titular by default)
2. WHEN loading buyer options THEN the system SHALL query the autorizados table for records matching the customer ID
3. WHEN displaying buyer options THEN the system SHALL show the titular and any authorized buyers
4. WHEN configuring the sale THEN the system SHALL provide a token input field
5. WHEN a token is entered THEN the system SHALL provide buttons to "enviar token" and "verificar token"
6. WHEN verifying token THEN the system SHALL compare against the cliente.token field
7. IF the token matches THEN the system SHALL enable the "finalizar venda" button

### Requirement 7

**User Story:** As a sales employee, I want to process the complete credit sale transaction, so that tickets and installments are properly recorded in the system.

#### Acceptance Criteria

1. WHEN "finalizar venda" is clicked THEN the system SHALL generate a unique ticket for the sale
2. WHEN creating the ticket THEN the system SHALL insert a record in the tickets table with id_cliente, ticket, data (current), valor (purchase - entry), entrada, parcelas count, and spc as NULL
3. WHEN calculating installments THEN the system SHALL divide (purchase value - entry) by number of installments, rounding up to next integer
4. WHEN calculating installments THEN the system SHALL use the calculated amount for all installments except the last
5. WHEN calculating the last installment THEN the system SHALL use (purchase value - entry) minus sum of previous installments
6. WHEN creating installment records THEN the system SHALL insert records in the parcelas table for each installment
7. WHEN setting installment dates THEN the system SHALL use the selected first date and add one month for each subsequent installment
8. WHEN setting installment dates THEN the system SHALL preserve the selected day (10th, 20th, or last day) for all installments

### Requirement 8

**User Story:** As a sales employee, I want the credit sale to integrate with the existing sales system, so that inventory and sales records are properly maintained.

#### Acceptance Criteria

1. WHEN the credit sale is completed THEN the system SHALL execute the same sales logic as the regular "finalizar compra" button
2. WHEN recording the sale THEN the system SHALL use the entry amount in the appropriate payment field (valor_pix, valor_cartao, or valor_dinheiro)
3. WHEN recording the sale THEN the system SHALL use the remaining purchase amount in valor_crediario
4. WHEN recording the sale THEN the system SHALL use the generated ticket in the ticket field
5. WHEN the sale is completed THEN the system SHALL clear the shopping cart
6. WHEN the sale is completed THEN the system SHALL provide confirmation to the user