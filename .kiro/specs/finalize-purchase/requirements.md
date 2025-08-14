# Requirements Document

## Introduction

This feature implements the "finalizar compra" (finalize purchase) functionality for the JJ Calçados shopping cart system. The system will process cart items and create sales records in city-specific MySQL tables (vendas_tabira or vendas_princesa) based on the logged-in user's city. The feature handles payment method distribution, product pricing with discounts, and maintains proper relationships between users, products, and sales data.

## Requirements

### Requirement 1

**User Story:** As a logged-in user, I want to finalize my purchase from the shopping cart, so that my order is recorded as a sale in the appropriate city-specific sales table.

#### Acceptance Criteria

1. WHEN a user clicks the "finalizar compra" button THEN the system SHALL validate that the user is authenticated
2. WHEN processing the purchase THEN the system SHALL determine the correct sales table (vendas_tabira or vendas_princesa) based on the user's cidade field
3. WHEN creating sales records THEN the system SHALL create one record per cart item in the appropriate city-specific table
4. IF the user's cidade field is empty or invalid THEN the system SHALL display an error message and prevent purchase completion

### Requirement 2

**User Story:** As a system administrator, I want sales records to contain complete payment and pricing information, so that I can track revenue and payment methods accurately.

#### Acceptance Criteria

1. WHEN creating a sales record THEN the system SHALL populate valor_dinheiro with the valor_avista amount from the cart
2. WHEN creating a sales record THEN the system SHALL populate valor_pix with the valor_pix amount from the cart
3. WHEN creating a sales record THEN the system SHALL populate valor_cartao with the valor_cartao amount from the cart
4. WHEN creating a sales record THEN the system SHALL populate valor_crediario with the valor_crediario amount from the cart
5. WHEN creating a sales record THEN the system SHALL set preco to the original product price without discount
6. WHEN creating a sales record THEN the system SHALL set preco_venda to the product price with applied discounts

### Requirement 3

**User Story:** As a system administrator, I want sales records to maintain proper relationships and timestamps, so that I can track who made the sale and when it occurred.

#### Acceptance Criteria

1. WHEN creating a sales record THEN the system SHALL set id_vendedor to the logged-in user's ID from the users table
2. WHEN creating a sales record THEN the system SHALL set id_vendedor_atendente to the value from the vendedor_atendente field if present
3. WHEN creating a sales record THEN the system SHALL set id_produto to the product ID from the produtos table
4. WHEN creating a sales record THEN the system SHALL set data_venda to the current date
5. WHEN creating a sales record THEN the system SHALL set hora to the current time
6. WHEN creating a sales record THEN the system SHALL set numeracao to the product's size/numbering

### Requirement 4

**User Story:** As a system administrator, I want default values set for tracking fields, so that the sales records are properly initialized for future processing.

#### Acceptance Criteria

1. WHEN creating a sales record THEN the system SHALL set desconto, alerta, and baixa_fiscal fields to FALSE (0)
2. WHEN creating a sales record THEN the system SHALL set data_estorno, pedido_devolucao, reposicao, bd, and ticket fields to NULL
3. WHEN the sales record is successfully created THEN the system SHALL generate a unique auto-increment id_vendas value

### Requirement 5

**User Story:** As a user, I want to receive confirmation after completing my purchase, so that I know my order was processed successfully.

#### Acceptance Criteria

1. WHEN all sales records are successfully created THEN the system SHALL clear the shopping cart session data
2. WHEN the purchase is completed THEN the system SHALL display a success message to the user
3. WHEN the purchase is completed THEN the system SHALL redirect the user to a confirmation page or back to the cart with success feedback
4. IF any error occurs during sales record creation THEN the system SHALL display an appropriate error message and maintain the cart contents

### Requirement 6

**User Story:** As a system administrator, I want the purchase process to handle errors gracefully, so that partial sales are not created and data integrity is maintained.

#### Acceptance Criteria

1. WHEN processing multiple cart items THEN the system SHALL use database transactions to ensure all-or-nothing processing
2. IF any sales record creation fails THEN the system SHALL rollback all changes and display an error message
3. WHEN database errors occur THEN the system SHALL log the error details for debugging purposes
4. WHEN validation errors occur THEN the system SHALL provide specific feedback about what needs to be corrected