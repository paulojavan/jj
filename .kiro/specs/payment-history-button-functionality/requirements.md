# Requirements Document

## Introduction

This feature implements interactive button functionality for the payment history view (historico-pagamentos.blade.php) to enable WhatsApp messaging, receipt printing, and payment cancellation operations. The system will provide sellers with tools to communicate payment confirmations to customers, generate printable receipts, and handle payment cancellations with proper inventory adjustments.

## Requirements

### Requirement 1

**User Story:** As a seller, I want to send WhatsApp messages to customers about payment confirmations, so that I can quickly notify them when their payments are processed.

#### Acceptance Criteria

1. WHEN a seller clicks the "Enviar WhatsApp" button THEN the system SHALL open a new browser tab with WhatsApp Web
2. WHEN opening WhatsApp THEN the system SHALL use the URL format https://wa.me/55{customer_phone}?text={message}
3. WHEN generating the WhatsApp message THEN the system SHALL include the text: "Joécio calçados informa: Pagamento de parcela efetuado dia {payment_date}, acesse o comprovante através do link: https://joeciocalçados.com.br/"
4. WHEN formatting the payment date THEN the system SHALL use the data field from the pagamentos table
5. WHEN the customer phone number is not available THEN the system SHALL display an error message

### Requirement 2

**User Story:** As a seller, I want to print payment receipts, so that I can provide physical proof of payment to customers.

#### Acceptance Criteria

1. WHEN a seller clicks the "Imprimir Comprovante" button THEN the system SHALL open a print-friendly version of the receipt
2. WHEN generating the printable receipt THEN the system SHALL remove all website layout elements (navigation, sidebar, etc.)
3. WHEN printing THEN the system SHALL follow the same format as the duplicata (invoice) layout
4. WHEN displaying the receipt THEN the system SHALL include payment details, customer information, and installment data
5. WHEN the print dialog opens THEN the system SHALL automatically trigger the browser's print function

### Requirement 3

**User Story:** As a seller, I want to cancel payments, so that I can handle payment reversals and update the system accordingly.

#### Acceptance Criteria

1. WHEN a seller clicks the "Cancelar Pagamento" button THEN the system SHALL delete the payment record from the pagamentos table
2. WHEN canceling a payment THEN the system SHALL update all related installments (parcelas) for that payment
3. WHEN updating installments THEN the system SHALL set the following fields to NULL: data_pagamento, hora, valor_pago, dinheiro, pix, cartao, metodo, id_vendedor, ticket_pagamento
4. WHEN updating installments THEN the system SHALL set the status field to "aguardando pagamento"
5. WHEN payment cancellation is complete THEN the system SHALL redirect back to the payment history with a success message

### Requirement 4

**User Story:** As a seller, I want confirmation dialogs for destructive actions, so that I can prevent accidental payment cancellations.

#### Acceptance Criteria

1. WHEN a seller clicks the "Cancelar Pagamento" button THEN the system SHALL display a confirmation dialog using SweetAlert2
2. WHEN showing the confirmation dialog THEN the system SHALL clearly explain the consequences of the action
3. WHEN the seller confirms cancellation THEN the system SHALL proceed with the payment cancellation process
4. WHEN the seller cancels the dialog THEN the system SHALL take no action and close the dialog
5. WHEN the cancellation is successful THEN the system SHALL display a success message using SweetAlert2

### Requirement 5

**User Story:** As a seller, I want proper error handling for button actions, so that I can understand when operations fail and why.

#### Acceptance Criteria

1. WHEN any button action fails THEN the system SHALL display an appropriate error message
2. WHEN displaying error messages THEN the system SHALL use SweetAlert2 for consistent UI
3. WHEN a WhatsApp action fails THEN the system SHALL show "Erro ao abrir WhatsApp. Verifique o número do cliente."
4. WHEN a print action fails THEN the system SHALL show "Erro ao gerar comprovante para impressão."
5. WHEN a payment cancellation fails THEN the system SHALL show the specific database error message

### Requirement 6

**User Story:** As a seller, I want the buttons to be properly integrated with the existing payment history interface, so that the functionality feels native to the application.

#### Acceptance Criteria

1. WHEN displaying buttons THEN the system SHALL maintain the existing button styling and layout
2. WHEN buttons are clicked THEN the system SHALL provide visual feedback (loading states, disabled states)
3. WHEN operations are in progress THEN the system SHALL disable relevant buttons to prevent duplicate actions
4. WHEN operations complete THEN the system SHALL re-enable buttons and update the interface as needed
5. WHEN the page loads THEN the system SHALL ensure all buttons are properly initialized with event handlers

### Requirement 7

**User Story:** As a seller, I want access to customer phone numbers for WhatsApp functionality, so that the messaging feature works correctly.

#### Acceptance Criteria

1. WHEN loading payment history THEN the system SHALL include customer phone number data
2. WHEN the customer phone number exists THEN the system SHALL format it properly for WhatsApp (removing special characters)
3. WHEN the phone number is missing THEN the system SHALL disable the WhatsApp button and show a tooltip
4. WHEN formatting phone numbers THEN the system SHALL ensure they include the country code (55 for Brazil)
5. WHEN displaying phone numbers THEN the system SHALL validate they contain only numeric characters