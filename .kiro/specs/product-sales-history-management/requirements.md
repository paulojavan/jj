# Requirements Document

## Introduction

This feature enhances the product detail view (exibir.blade.php) to display the last 30 sales of a specific product in the seller's city, along with functionality for processing returns and size exchanges. The system will provide real-time sales history with interactive controls for inventory management operations.

## Requirements

### Requirement 1

**User Story:** As a seller, I want to view the last 30 sales of a product in my city, so that I can track recent sales activity and make informed decisions about returns and exchanges.

#### Acceptance Criteria

1. WHEN a seller views a product detail page THEN the system SHALL display the last 30 sales of that product from the seller's city
2. WHEN displaying sales history THEN the system SHALL show seller name, sale date, payment values (cash, PIX, card), and size sold
3. WHEN a sale has a return date (data_estorno is not null) THEN the system SHALL display the row with a red background
4. WHEN filtering sales THEN the system SHALL only include records where ticket field is null
5. WHEN retrieving sales data THEN the system SHALL order by sale date in descending order

### Requirement 2

**User Story:** As a seller, I want to see payment details for each sale, so that I can understand the payment methods used and total amounts.

#### Acceptance Criteria

1. WHEN displaying sales history THEN the system SHALL show valor_dinheiro (cash amount)
2. WHEN displaying sales history THEN the system SHALL show valor_pix (PIX amount)
3. WHEN displaying sales history THEN the system SHALL show valor_cartao (card amount)
4. WHEN displaying payment values THEN the system SHALL format amounts in Brazilian currency format
5. WHEN any payment value is zero THEN the system SHALL display it as "R$ 0,00"

### Requirement 3

**User Story:** As a seller, I want to process product returns, so that I can handle customer returns and update inventory accordingly.

#### Acceptance Criteria

1. WHEN a sale is not returned (data_estorno is null) THEN the system SHALL display a "Devolução" (Return) button
2. WHEN a seller clicks the return button THEN the system SHALL set data_estorno to current date
3. WHEN processing a return THEN the system SHALL add 1 unit to the returned product size in city inventory
4. WHEN processing a return THEN the system SHALL add 1 unit to the main product quantity in produtos table
5. WHEN a return is processed THEN the system SHALL refresh the sales history display
5. WHEN a sale is already returned THEN the system SHALL NOT display the return button

### Requirement 4

**User Story:** As a seller, I want to exchange product sizes, so that I can accommodate customer size change requests while maintaining accurate inventory.

#### Acceptance Criteria

1. WHEN a sale is not returned (data_estorno is null) THEN the system SHALL display available sizes in a select dropdown
2. WHEN displaying available sizes THEN the system SHALL only show sizes with stock > 0 in the seller's city
3. WHEN a seller selects a new size and clicks "Trocar" (Exchange) THEN the system SHALL add 1 unit to the original size
4. WHEN processing an exchange THEN the system SHALL subtract 1 unit from the selected new size
5. WHEN processing an exchange THEN the system SHALL update the numeracao field to the new selected size
6. WHEN an exchange is completed THEN the system SHALL refresh the sales history and available sizes

### Requirement 5

**User Story:** As a seller, I want the system to validate inventory before exchanges, so that I cannot exchange to sizes that are out of stock.

#### Acceptance Criteria

1. WHEN loading the page THEN the system SHALL check current inventory for the seller's city
2. WHEN a size has zero stock THEN the system SHALL NOT include it in the exchange dropdown
3. WHEN processing an exchange THEN the system SHALL verify the selected size still has stock
4. IF selected size has no stock THEN the system SHALL display an error message and prevent the exchange
5. WHEN inventory changes THEN the system SHALL update available sizes in real-time

### Requirement 6

**User Story:** As a seller, I want to see which seller made each sale, so that I can track sales performance and contact colleagues if needed.

#### Acceptance Criteria

1. WHEN displaying sales history THEN the system SHALL show the seller name for each sale
2. WHEN retrieving seller information THEN the system SHALL use the id_vendedor field to get seller details
3. WHEN a seller is not found THEN the system SHALL display "Vendedor não encontrado"
4. WHEN displaying seller names THEN the system SHALL show the full name or username
5. WHEN seller information is available THEN the system SHALL display it in a readable format

### Requirement 7

**User Story:** As a seller, I want the sales history to be visually clear and organized, so that I can quickly identify returned items and available actions.

#### Acceptance Criteria

1. WHEN displaying sales history THEN the system SHALL use a table format with clear headers
2. WHEN a sale is returned THEN the system SHALL apply a red background color to the entire row
3. WHEN displaying dates THEN the system SHALL format them in Brazilian date format (dd/mm/yyyy)
4. WHEN no sales exist THEN the system SHALL display "Nenhuma venda encontrada nos últimos 30 registros"
5. WHEN loading sales data THEN the system SHALL show a loading indicator if the process takes time