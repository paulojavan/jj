# Project Structure

## Laravel Standard Structure

### Core Application (`app/`)
- `Http/Controllers/` - Request handling logic
  - `CarrinhoController.php` - Shopping cart operations
  - Follow Laravel controller conventions
- `Models/` - Eloquent models for database entities
- `Providers/` - Service providers for dependency injection

### Views (`resources/views/`)
- `layouts/base.blade.php` - Main layout template
- `carrinho/` - Shopping cart views
- `descontos/` - Discount management views
- `login.blade.php` - Authentication views
- Use Blade templating with Tailwind CSS classes

### Database (`database/`)
- `migrations/` - Database schema changes
- `seeders/` - Sample data population
- `factories/` - Model factories for testing
- `database.sqlite` - Development database

### Configuration (`config/`)
- Standard Laravel configuration files
- Database, authentication, session settings

### Assets (`resources/`)
- `css/app.css` - Tailwind CSS entry point
- `js/app.js` - JavaScript entry point
- Compiled assets go to `public/build/`

## Naming Conventions

### Controllers
- PascalCase with "Controller" suffix
- RESTful method names: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Custom methods use descriptive names: `adicionar`, `remover`, `aplicarDesconto`

### Views
- Snake_case directory and file names
- Match controller structure: `carrinho/index.blade.php`
- Use Portuguese names for business-specific views

### Database
- Snake_case table names
- City-based inventory tables: `estoque_{cidade_name}`
- Foreign keys: `{table}_id` format

### CSS Classes
- Tailwind utility classes
- Flowbite component classes
- Custom classes in Portuguese for business context

## Code Organization

### Session Management
- Shopping cart stored in session as `carrinho`
- Discount data in `descontos_aplicados`
- User context in `cliente_vendedor`

### Multi-location Logic
- City-based inventory tables
- User city association for stock checking
- Dynamic table name generation based on user location

### Localization
- Portuguese (Brazil) as primary language
- Translation files in `lang/pt_BR/`
- Business terms in Portuguese throughout codebase