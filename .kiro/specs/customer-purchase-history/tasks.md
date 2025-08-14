# Implementation Plan

- [x] 1. Create Eloquent models for tickets and parcelas tables


  - Create Ticket model with proper relationships, casts, and methods
  - Create Parcela model with status color logic and date validation methods
  - Write unit tests for model relationships and methods
  - _Requirements: 6.1, 6.2_



- [ ] 2. Implement PaymentProfileService for customer behavior analysis
  - Create service class to calculate payment behavior patterns (late/on-time/early)
  - Implement method to calculate return rate based on purchase history
  - Add method to calculate total purchased amount excluding returns
  - Implement purchase frequency analysis (regular/irregular pattern detection)


  - Write unit tests for all calculation methods
  - _Requirements: 5.2, 5.3, 5.4, 5.5, 5.6_

- [ ] 3. Add new routes for purchase history functionality
  - Add route for customer purchase history page with pagination support


  - Add route for individual purchase details page
  - Add routes for document generation (duplicata, carnê, mensagem)
  - Update web.php with proper route naming and middleware
  - _Requirements: 1.1, 1.2, 4.1_



- [ ] 4. Extend ClienteController with purchase history methods
  - Implement historico method with pagination and profile calculation
  - Create detalhesCompra method to show individual purchase details
  - Add methods for document generation (gerarDuplicata, gerarCarne, enviarMensagem)
  - Implement proper error handling and validation for all methods
  - _Requirements: 1.3, 1.4, 4.2, 4.3, 6.3, 6.4_


- [ ] 5. Create purchase history main view with expandable cards
  - Create Blade template for purchase history listing page
  - Implement expandable cards showing ticket, date, total value, and down payment
  - Add pagination controls for 20 purchases per page
  - Integrate payment profile analysis display at bottom of page


  - Style with Tailwind CSS following project conventions
  - _Requirements: 1.3, 1.4, 2.1, 2.2, 5.1_

- [ ] 6. Implement expandable card functionality for installments
  - Add JavaScript functionality to expand/collapse purchase cards


  - Create installment display within expanded cards showing number, due date, amount, and status
  - Implement color-coded status display (green/black/red/yellow)
  - Add "Ver Compra Completa" button in expanded state
  - _Requirements: 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 3.5_


- [ ] 7. Create purchase details view with product listing
  - Create Blade template for individual purchase details page
  - Display complete product list for the selected purchase
  - Add three action buttons: Duplicata, Carnê de Pagamento, Mensagem de Aviso
  - Implement proper navigation and breadcrumb structure
  - _Requirements: 4.1, 4.2, 4.3_



- [ ] 8. Add purchase history button to client search page
  - Locate existing client search view template
  - Add "Histórico de Compras" button below the "Editar" button
  - Implement proper routing to purchase history page


  - Style button consistently with existing UI patterns
  - _Requirements: 1.1_

- [ ] 9. Implement payment profile analysis calculations
  - Create methods to analyze payment timing patterns using data_vencimento and data_pagamento
  - Implement return rate calculation comparing returned vs total purchases



  - Add total purchase amount calculation excluding returned items
  - Create first purchase date finder and purchase regularity analyzer
  - _Requirements: 5.2, 5.3, 5.4, 5.5, 5.6_

- [ ] 10. Add database indexes for performance optimization
  - Create migration to add index on tickets.id_cliente for faster customer lookups
  - Add index on parcelas.ticket for efficient installment queries
  - Add composite index on parcelas (ticket, data_vencimento) for status calculations
  - Test query performance improvements with sample data
  - _Requirements: 6.1, 6.2_

- [ ] 11. Write comprehensive feature tests for purchase history workflow
  - Create test for complete purchase history page functionality
  - Test pagination behavior with various data sets
  - Test expandable card functionality and installment display
  - Test purchase details page navigation and display
  - Test payment profile calculations with different scenarios
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4_

- [ ] 12. Implement responsive design and mobile optimization
  - Ensure purchase history cards work properly on mobile devices
  - Optimize installment display for smaller screens
  - Test and adjust payment profile section for mobile viewing
  - Verify all buttons and interactions work on touch devices
  - _Requirements: 2.1, 2.2, 3.1_