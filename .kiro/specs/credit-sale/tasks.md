# Implementation Plan

- [x] 1. Create customer search functionality


  - Add vendaCrediario method to CarrinhoController to redirect to customer search page
  - Create pesquisarCliente method to handle customer search by nome, apelido, rg, cpf
  - Implement searchCustomers private method to query clientes table with search criteria
  - Create customer search view with search form and results display
  - _Requirements: 1.1, 1.2, 1.3, 1.4_



- [ ] 2. Implement customer status validation logic
  - Create validateCustomerStatus private method to check customer status and atualizacao date
  - Add logic to check tickets and parcelas tables for SPC records when customer is inactive
  - Implement status-based button display logic (selecionar cliente, verificar número, cliente negativado, cliente bloqueado)


  - Add redirect functionality for "verificar número" button to cliente edit route
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 3.1, 3.2, 3.3_

- [ ] 3. Create credit limit validation and calculation methods
  - Implement calculateAvailableCredit method to sum pending parcelas and calculate available credit


  - Add checkOverduePayments method to identify parcelas with data_vencimento before today
  - Create calculateMinimumEntry method to calculate required entry when purchase exceeds credit
  - Add validation logic to prevent sales when overdue payments exist
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_

- [x] 4. Build credit sale configuration interface


  - Create selecionarCliente method to display credit sale configuration page
  - Implement view layout with customer information on left side and purchase configuration on right
  - Add entry payment input field and payment method selection (dinheiro, pix, cartão)
  - Create installment configuration with date selection and parcelas count
  - Add buyer selection functionality with titular and authorized buyers from autorizados table


  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 6.1, 6.2, 6.3_

- [ ] 5. Implement token verification system
  - Add token input field and verification buttons to credit sale configuration view
  - Create token verification method to compare against cliente.token field
  - Implement "enviar token" and "verificar token" button functionality


  - Add logic to enable "finalizar venda" button only after successful token verification
  - _Requirements: 6.4, 6.5, 6.6, 6.7_

- [ ] 6. Create installment calculation and date generation methods
  - Implement generatePaymentDates method to create date options for 10th, 20th, and last day of month
  - Add calculateInstallments method to divide purchase amount by number of installments


  - Create logic to round up installment amounts and calculate exact last installment
  - Add validation to ensure minimum R$20 per installment
  - Handle month-end date calculations for "último dia do mês" option
  - _Requirements: 5.6, 5.7, 5.8_



- [ ] 7. Implement ticket generation and database record creation
  - Create generateUniqueTicket method to generate unique ticket identifiers
  - Implement createTicketRecord method to insert records in tickets table
  - Add createInstallmentRecords method to create parcelas records for each installment
  - Handle installment date calculations preserving selected day (10th, 20th, last day)
  - Set appropriate default values for parcelas table fields
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.8_

- [ ] 8. Create main credit sale processing method
  - Implement finalizarVendaCrediario method to handle complete credit sale transaction
  - Add database transaction wrapper to ensure data integrity
  - Process ticket creation, installment generation, and sales recording in sequence
  - Implement proper error handling and rollback mechanisms for failed transactions
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 7.8_

- [ ] 9. Integrate with existing sales system
  - Modify finalizarVendaCrediario to call existing sales logic after credit processing
  - Map entry payment amount to appropriate payment field (valor_pix, valor_cartao, valor_dinheiro)
  - Set remaining purchase amount in valor_crediario field
  - Use generated ticket in sales record ticket field
  - Ensure proper session cleanup after successful sale completion
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_



- [ ] 10. Create comprehensive unit tests for credit sale functionality
  - Write tests for customer search functionality across different search criteria
  - Create tests for customer status validation with various scenarios
  - Add tests for credit limit calculations and overdue payment detection
  - Test installment calculations with different amounts and parcela counts
  - Write tests for token verification logic
  - _Requirements: All validation and calculation requirements_

- [ ] 11. Implement integration tests for complete credit sale workflow
  - Create end-to-end test for successful credit sale completion
  - Test transaction rollback scenarios with database failures
  - Add tests for various customer status scenarios and appropriate responses
  - Test integration with existing sales system and session management
  - Verify proper error handling and user feedback throughout the workflow
  - _Requirements: All workflow and integration requirements_

- [ ] 12. Add routes and update cart view for credit sale button
  - Create routes for all credit sale methods (vendaCrediario, pesquisarCliente, selecionarCliente, etc.)
  - Update cart view template to include "venda crediário" button
  - Add necessary form elements and CSRF protection for all credit sale forms
  - Ensure proper styling and user experience for all credit sale interfaces
  - Add JavaScript functionality for dynamic form interactions and validations
  - _Requirements: 1.1, 5.2, 5.3, 6.4, 6.5, 6.6, 6.7_