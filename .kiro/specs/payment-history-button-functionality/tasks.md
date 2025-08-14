# Implementation Plan

- [x] 1. Add new controller methods for button functionality


  - Create three new methods in ClienteController: enviarWhatsappPagamento, imprimirComprovantePagamento, and cancelarPagamento
  - Implement proper request validation and error handling for each method
  - Add database transaction support for payment cancellation operations
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4_


- [ ] 2. Implement WhatsApp messaging functionality
  - Add phone number validation and formatting logic for Brazilian numbers
  - Create WhatsApp URL generation with proper message templating
  - Implement customer phone number retrieval and country code handling
  - Add error handling for missing or invalid phone numbers


  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 7.1, 7.2, 7.3, 7.4, 7.5_

- [ ] 3. Create printable receipt template
  - Design new Blade template for print-friendly payment receipts
  - Remove website layout elements and implement print-specific CSS

  - Include payment details, customer information, and installment breakdown
  - Follow duplicata layout format as specified in requirements
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [ ] 4. Implement payment cancellation logic
  - Add payment deletion functionality with proper validation


  - Create installment update logic to reset payment fields to NULL
  - Implement status change to "aguardando pagamento" for affected installments
  - Add database transaction wrapping for data integrity
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_


- [-] 5. Add confirmation dialogs and error handling

  - Integrate SweetAlert2 for payment cancellation confirmations
  - Implement error message display for all button actions
  - Add success feedback for completed operations
  - Create proper error handling for WhatsApp, print, and cancellation failures


  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 6. Update payment history view with JavaScript functionality
  - Add event handlers for all three button types
  - Implement AJAX calls for WhatsApp and cancellation operations



  - Add loading states and button disable/enable logic during operations
  - Integrate with existing payment card toggle functionality
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 7. Add new routes for button endpoints
  - Create routes for WhatsApp, print, and cancellation endpoints
  - Implement proper route parameter validation
  - Add route names for easy URL generation in views
  - Ensure routes follow Laravel RESTful conventions
  - _Requirements: 1.1, 2.1, 3.1_

- [ ] 8. Write comprehensive tests for new functionality
  - Create unit tests for controller methods and business logic
  - Add integration tests for complete request/response cycles
  - Test database transaction rollback scenarios for payment cancellation
  - Add frontend tests for JavaScript button interactions and AJAX calls
  - _Requirements: All requirements - comprehensive testing coverage_