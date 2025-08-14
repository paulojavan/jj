# Design Document

## Overview

The penalty fee configuration system will be implemented as a Laravel feature that allows administrators to configure penalty rates, interest rates, collection periods, and grace periods for overdue payments. The system follows the existing application patterns with a dedicated controller, model, migration, and views, integrated into the main dashboard alongside the existing discount configuration.

## Architecture

### MVC Pattern
The feature follows Laravel's MVC architecture:
- **Model**: `MultaConfiguracao` - Handles penalty configuration data and business logic
- **Controller**: `MultaConfiguracaoController` - Manages HTTP requests and responses
- **Views**: Blade templates for configuration interface and dashboard card
- **Migration**: Database schema for storing penalty configuration

### Integration Points
- Dashboard integration with new configuration card
- Authentication middleware for admin access
- Session-based success/error messaging using SweetAlert2
- Validation using Laravel's built-in validation system

## Components and Interfaces

### Database Schema
```sql
CREATE TABLE multa_configuracoes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    taxa_multa DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    taxa_juros DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    dias_cobranca INT UNSIGNED NOT NULL DEFAULT 30,
    dias_carencia INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Model Structure
```php
class MultaConfiguracao extends Model
{
    protected $table = 'multa_configuracoes';
    
    protected $fillable = [
        'taxa_multa',
        'taxa_juros', 
        'dias_cobranca',
        'dias_carencia'
    ];
    
    protected $casts = [
        'taxa_multa' => 'decimal:2',
        'taxa_juros' => 'decimal:2',
        'dias_cobranca' => 'integer',
        'dias_carencia' => 'integer'
    ];
}
```

### Controller Methods
- `edit()` - Display configuration form with current settings
- `update()` - Process form submission and update configuration
- `getConfiguration()` - Helper method to get current configuration (singleton pattern)

### Routes
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/multa-configuracao/edit', [MultaConfiguracaoController::class, 'edit'])
         ->name('multa-configuracao.edit');
    Route::put('/multa-configuracao/{multaConfiguracao}', [MultaConfiguracaoController::class, 'update'])
         ->name('multa-configuracao.update');
});
```

## Data Models

### MultaConfiguracao Model
- **taxa_multa**: Decimal(5,2) - Penalty rate percentage (0.00-100.00)
- **taxa_juros**: Decimal(5,2) - Interest rate percentage (0.00-100.00)  
- **dias_cobranca**: Integer - Number of days before collection starts (1-365)
- **dias_carencia**: Integer - Grace period days before penalties apply (0-90)
- **timestamps**: Laravel's created_at and updated_at

### Validation Rules
```php
$rules = [
    'taxa_multa' => 'required|numeric|min:0|max:100',
    'taxa_juros' => 'required|numeric|min:0|max:100',
    'dias_cobranca' => 'required|integer|min:1|max:365',
    'dias_carencia' => 'required|integer|min:0|max:90|lte:dias_cobranca'
];
```

### Business Logic
- Single configuration record (singleton pattern)
- Automatic creation of default configuration if none exists
- Grace period must be less than or equal to collection days
- All rates stored as percentages with 2 decimal precision

## User Interface Design

### Dashboard Card
Following the existing pattern from discount configuration:
```html
<div class="dashboard-card">
    <a href="{{ route('multa-configuracao.edit') }}" class="dashboard-link">
        <div class="dashboard-icon">
            <i class="fas fa-exclamation-triangle text-3xl"></i>
        </div>
        <h3 class="dashboard-title">Configurar<br>Multas e Juros</h3>
    </a>
</div>
```

### Configuration Form Layout
- Header with icon and title following existing pattern
- Four main configuration cards with color-coded sections:
  - **Penalty Rate Card** (Red theme) - Taxa de Multa
  - **Interest Rate Card** (Orange theme) - Taxa de Juros  
  - **Collection Days Card** (Blue theme) - Dias para Cobrança
  - **Grace Period Card** (Green theme) - Dias de Carência
- Information panel with usage guidelines
- Action buttons (Save/Reset/Back to Dashboard)

### Visual Design Elements
- Consistent with existing discount configuration styling
- Tailwind CSS classes for responsive design
- Flowbite components for enhanced UI elements
- Color-coded sections for different configuration types
- Icons from Font Awesome for visual consistency

## Error Handling

### Validation Errors
- Field-level validation with specific error messages
- Real-time validation using JavaScript
- Server-side validation as fallback
- Portuguese error messages for user clarity

### Business Logic Validation
- Grace period cannot exceed collection days
- Rates must be within valid percentage ranges
- Days must be within reasonable business limits
- Database constraint validation

### Error Display
- Individual field error messages below inputs
- SweetAlert2 for success/error notifications
- Consistent error styling with existing forms
- Graceful degradation for JavaScript-disabled browsers

## Testing Strategy

### Unit Tests
- Model validation testing
- Business logic validation
- Configuration retrieval methods
- Data casting and formatting

### Feature Tests
- Controller method testing
- Route accessibility testing
- Form submission validation
- Authentication middleware testing

### Integration Tests
- Dashboard card display
- Form rendering with existing data
- Success/error message display
- Database persistence verification

### Browser Tests
- End-to-end configuration workflow
- JavaScript validation behavior
- Responsive design verification
- Cross-browser compatibility

## Security Considerations

### Authentication & Authorization
- Admin-only access using existing auth middleware
- CSRF protection on form submissions
- Input sanitization and validation
- SQL injection prevention through Eloquent ORM

### Data Validation
- Server-side validation as primary security layer
- Client-side validation for user experience only
- Type casting for data integrity
- Range validation for business logic compliance

### Audit Trail
- Laravel's built-in timestamps for change tracking
- Session-based user identification
- Configuration change logging capability
- Database-level constraints for data integrity

## Performance Considerations

### Database Optimization
- Single configuration record reduces query complexity
- Indexed primary key for fast retrieval
- Minimal table structure for optimal performance
- Connection reuse through Laravel's connection pooling

### Caching Strategy
- Configuration data suitable for application-level caching
- Singleton pattern reduces database queries
- Cache invalidation on configuration updates
- Session-based temporary data storage

### Frontend Optimization
- Minimal JavaScript for validation
- CSS classes reused from existing components
- Optimized asset loading through Vite
- Progressive enhancement approach