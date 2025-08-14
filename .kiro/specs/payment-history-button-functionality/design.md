# Design Document

## Overview

This design implements interactive button functionality for the payment history view in the JJ Calçados system. The solution extends the existing `ClienteController` and payment history view to provide WhatsApp messaging, receipt printing, and payment cancellation capabilities. The design follows Laravel conventions and integrates seamlessly with the existing Blade template structure.

## Architecture

### Controller Layer
- **ClienteController**: Extended with new methods for button actions
  - `enviarWhatsappPagamento()`: Generates WhatsApp links with payment confirmation messages
  - `imprimirComprovantePagamento()`: Renders printable receipt views
  - `cancelarPagamento()`: Handles payment cancellation and database updates

### View Layer
- **historico-pagamentos.blade.php**: Enhanced with JavaScript event handlers for button interactions
- **comprovante-pagamento.blade.php**: New print-friendly receipt template
- **SweetAlert2**: Used for confirmations and user feedback

### Data Layer
- **Pagamento Model**: Existing model used for payment operations
- **Parcela Model**: Updated for installment status changes during cancellation
- **Cliente Model**: Used for customer phone number access

## Components and Interfaces

### WhatsApp Integration Component

```php
// ClienteController method
public function enviarWhatsappPagamento(Request $request, $clienteId, $pagamentoId)
{
    // Validate customer phone number
    // Format WhatsApp URL with payment message
    // Return JSON response with WhatsApp URL
}
```

**Interface Requirements:**
- Customer phone number validation and formatting
- Message template with payment date interpolation
- URL encoding for WhatsApp Web compatibility
- Error handling for missing phone numbers

### Print Receipt Component

```php
// ClienteController method
public function imprimirComprovantePagamento($clienteId, $pagamentoId)
{
    // Load payment and customer data
    // Return print-friendly view
}
```

**Template Structure:**
- Minimal layout without navigation/sidebar
- Payment details section
- Customer information section
- Installment breakdown
- Print-specific CSS styling

### Payment Cancellation Component

```php
// ClienteController method
public function cancelarPagamento(Request $request, $clienteId, $pagamentoId)
{
    // Validate payment exists and can be cancelled
    // Delete payment record
    // Update related installments
    // Return success/error response
}
```

**Database Operations:**
1. Delete from `pagamentos` table
2. Update `parcelas` table fields to NULL
3. Set installment status to "aguardando pagamento"
4. Transaction-wrapped for data integrity

### Frontend JavaScript Component

```javascript
// Button event handlers
function enviarWhatsapp(clienteId, pagamentoId) {
    // AJAX call to generate WhatsApp URL
    // Open new tab with WhatsApp Web
    // Handle errors and loading states
}

function imprimirComprovante(clienteId, pagamentoId) {
    // Open print-friendly page in new window
    // Trigger browser print dialog
}

function cancelarPagamento(clienteId, pagamentoId) {
    // Show SweetAlert2 confirmation
    // AJAX call to cancel payment
    // Refresh page on success
}
```

## Data Models

### Payment Data Structure
```php
// Pagamento model fields used
- id_pagamento (primary key)
- id_cliente (foreign key)
- ticket (payment identifier)
- data (payment date)

// Related Parcela fields updated during cancellation
- data_pagamento → NULL
- hora → NULL
- valor_pago → NULL
- dinheiro → NULL
- pix → NULL
- cartao → NULL
- metodo → NULL
- id_vendedor → NULL
- ticket_pagamento → NULL
- status → "aguardando pagamento"
```

### Customer Phone Number Handling
```php
// Cliente model phone field processing
- telefone field: stored as numeric string
- Format for WhatsApp: ensure 55 country code prefix
- Validation: check for valid Brazilian phone format
```

## Error Handling

### WhatsApp Errors
- **Missing Phone**: Display "Número de telefone não encontrado" message
- **Invalid Format**: Show "Número de telefone inválido" error
- **Network Issues**: Handle AJAX failures gracefully

### Print Errors
- **Data Loading**: Show "Erro ao carregar dados do pagamento"
- **Template Issues**: Fallback to basic receipt format
- **Browser Compatibility**: Detect print support

### Cancellation Errors
- **Payment Not Found**: Return 404 with appropriate message
- **Database Errors**: Show specific error from exception
- **Transaction Failures**: Rollback and display error message

### User Feedback System
```javascript
// SweetAlert2 configuration
const showSuccess = (message) => {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: message,
        timer: 3000
    });
};

const showError = (message) => {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: message
    });
};
```

## Testing Strategy

### Unit Tests
- **WhatsApp URL Generation**: Test message formatting and URL encoding
- **Phone Number Validation**: Test Brazilian phone number formats
- **Payment Cancellation Logic**: Test database operations and rollbacks
- **Data Formatting**: Test date and currency formatting

### Integration Tests
- **Controller Methods**: Test complete request/response cycles
- **Database Transactions**: Test payment cancellation with rollback scenarios
- **View Rendering**: Test print template generation
- **AJAX Endpoints**: Test JSON responses and error handling

### Frontend Tests
- **Button Interactions**: Test click handlers and loading states
- **SweetAlert Integration**: Test confirmation dialogs
- **Print Functionality**: Test print dialog triggering
- **Error Display**: Test error message presentation

### Browser Compatibility Tests
- **WhatsApp Links**: Test across different browsers and devices
- **Print Functionality**: Test print dialog behavior
- **AJAX Requests**: Test cross-browser AJAX compatibility
- **SweetAlert2**: Test modal behavior across browsers

## Security Considerations

### Input Validation
- Validate customer and payment IDs to prevent unauthorized access
- Sanitize phone numbers to prevent injection attacks
- Validate payment ownership before operations

### Authorization
- Ensure users can only access payments for their authorized customers
- Implement proper session validation
- Check user permissions for payment operations

### Data Protection
- Mask sensitive customer information in logs
- Secure phone number handling and formatting
- Protect against CSRF attacks on payment operations

## Performance Optimization

### Database Queries
- Use eager loading for payment and customer relationships
- Index payment and customer ID fields for faster lookups
- Optimize installment updates with batch operations

### Frontend Performance
- Debounce button clicks to prevent duplicate requests
- Use loading states to improve user experience
- Cache customer phone numbers for repeated WhatsApp actions

### Caching Strategy
- Cache formatted phone numbers for session duration
- Cache print templates for repeated access
- Use browser caching for static assets