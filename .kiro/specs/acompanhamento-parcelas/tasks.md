# Implementation Plan

- [x] 1. Create service class for installment calculations


  - Implement ParcelaCalculoService with methods for calculating overdue days, penalties, interest, and total amount to pay
  - Add comprehensive unit tests for all calculation scenarios including grace period and maximum collection days
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_



- [ ] 2. Create ParcelaController with consultation logic
  - Implement index method to display CPF consultation page
  - Implement consultar method to process CPF search and retrieve client data

  - Add CPF validation and formatting logic maintaining mask format for database search
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 3. Implement installment retrieval and grouping logic
  - Add method to fetch installments with "aguardando pagamento" status for specific client


  - Implement logic to separate titular installments (id_autorizado = null) from authorized client installments
  - Create grouping logic for multiple authorized clients by id_autorizado
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 4. Create CPF consultation view with input mask


  - Build responsive consultation page with CPF input field
  - Implement jQuery mask for CPF format (XXX.XXX.XXX-XX)
  - Add form validation and error message display
  - Style with Tailwind CSS following existing design patterns
  - _Requirements: 1.1, 1.2, 5.3, 5.4_


- [ ] 5. Create installments display view with selection functionality
  - Build responsive installments display page showing client info (name and photo)
  - Implement installment cards displaying ticket, number, amount, overdue days, and total to pay
  - Add visual separation between titular and authorized client installments
  - Create checkbox selection system for each installment

  - _Requirements: 2.4, 2.5, 4.1, 5.1_

- [ ] 6. Implement real-time total calculation with JavaScript
  - Add JavaScript functionality for checkbox selection/deselection
  - Implement automatic total calculation when installments are selected/deselected
  - Display running total with Brazilian currency formatting


  - Ensure total updates in real-time as user interacts with checkboxes
  - _Requirements: 4.2, 4.3, 4.4, 4.5, 4.6_

- [ ] 7. Add navigation between consultation and installments pages
  - Implement navigation flow from CPF consultation to installments display


  - Add option to perform new consultation from installments page
  - Ensure data clearing when performing new consultation
  - Maintain client information visibility on installments page
  - _Requirements: 5.1, 5.2, 5.3, 5.5_



- [ ] 8. Create routes and integrate with existing navigation
  - Define routes for installment consultation functionality
  - Add navigation links to existing menu structure
  - Ensure proper route naming following Laravel conventions
  - Test route accessibility and parameter handling


  - _Requirements: 5.1, 5.2, 5.5_

- [ ] 9. Implement comprehensive error handling
  - Add validation for CPF format and existence in database
  - Create user-friendly error messages for various scenarios (CPF not found, no pending installments)



  - Implement proper error logging without exposing sensitive data
  - Add fallback handling for calculation errors
  - _Requirements: 1.4, 5.4_

- [ ] 10. Create unit tests for service class calculations
  - Write tests for overdue days calculation with various scenarios
  - Test penalty and interest calculations with different configurations
  - Verify grace period logic and maximum collection days limits
  - Test edge cases like zero amounts and negative scenarios
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

- [ ] 11. Create feature tests for complete user flow
  - Test complete flow from CPF input to installments display
  - Verify installment grouping for titular and authorized clients
  - Test checkbox selection and total calculation functionality
  - Validate navigation between pages and data persistence
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 2.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 12. Add responsive design and accessibility features
  - Ensure mobile-first responsive design for both pages
  - Implement proper ARIA labels and semantic HTML structure
  - Test keyboard navigation and screen reader compatibility
  - Verify touch targets are appropriate for mobile devices
  - _Requirements: 1.1, 2.4, 4.1, 5.1_