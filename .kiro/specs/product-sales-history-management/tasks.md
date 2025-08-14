# Implementation Plan

- [x] 1. Add sales history methods to ProdutoController


  - Create `getSalesHistory()` method to retrieve last 30 sales for a product in seller's city
  - Create `getAvailableSizes()` method to fetch sizes with available stock
  - Add proper error handling for missing sales/stock tables
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 6.1, 6.2, 6.3, 6.4_


- [ ] 2. Implement return processing functionality
  - Create `processReturn()` method in ProdutoController to handle product returns
  - Update `data_estorno` field with current date
  - Increment inventory quantity for returned size in city stock table
  - Add validation to prevent double returns

  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [ ] 3. Implement size exchange functionality
  - Create `processExchange()` method in ProdutoController for size exchanges
  - Add inventory validation before processing exchange
  - Update original size inventory (+1) and new size inventory (-1)
  - Update sale record's `numeracao` field with new size
  - Add error handling for insufficient stock scenarios
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5_



- [ ] 4. Add routes for AJAX operations
  - Create POST route for return processing
  - Create POST route for size exchange processing
  - Create GET route for fetching available sizes


  - Add CSRF protection and proper middleware
  - _Requirements: 3.1, 4.1, 5.1_

- [ ] 5. Create sales history view component
  - Add sales history table section to `exibir.blade.php`
  - Display seller name, sale date, payment values, and size for each sale

  - Apply red background styling for returned items (data_estorno not null)
  - Format dates in Brazilian format (dd/mm/yyyy)
  - Format currency values in Brazilian format (R$ X,XX)
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 2.3, 2.4, 2.5, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 6. Add return and exchange action buttons

  - Create return button for non-returned sales
  - Create size exchange dropdown and button for non-returned sales
  - Hide action buttons for already returned items
  - Add proper form structure with CSRF tokens
  - _Requirements: 3.1, 3.5, 4.1, 4.2_



- [ ] 7. Implement JavaScript for AJAX operations
  - Create JavaScript functions for return processing
  - Create JavaScript functions for size exchange processing
  - Add loading states and user feedback


  - Handle success and error responses
  - Update UI dynamically after operations
  - _Requirements: 3.4, 4.5, 7.5_

- [ ] 8. Add error handling and validation
  - Implement proper error messages for all failure scenarios



  - Add client-side validation for size selection
  - Add server-side validation for all operations
  - Display user-friendly error messages
  - _Requirements: 5.4, 6.3, 7.4_

- [ ] 9. Create unit tests for controller methods
  - Write tests for `getSalesHistory()` method with various scenarios
  - Write tests for `processReturn()` method including edge cases
  - Write tests for `processExchange()` method with inventory validation
  - Write tests for `getAvailableSizes()` method
  - Test error handling and validation scenarios
  - _Requirements: All requirements validation through automated testing_

- [ ] 10. Integration testing and final adjustments
  - Test complete workflow from product view to return/exchange
  - Verify inventory consistency after operations
  - Test with multiple cities and users
  - Ensure proper authorization and security
  - Test responsive design and mobile compatibility
  - _Requirements: All requirements end-to-end validation_