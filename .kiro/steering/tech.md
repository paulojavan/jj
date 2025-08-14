# Technology Stack

## Backend
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (development), supports MySQL/PostgreSQL
- **Authentication**: Laravel's built-in authentication system
- **Session Management**: Laravel sessions for cart and user data
- **Image Processing**: Intervention Image library
- **Localization**: Portuguese (Brazil) with lucascudo/laravel-pt-br-localization

## Frontend
- **CSS Framework**: Tailwind CSS 4.x
- **UI Components**: Flowbite components
- **JavaScript**: Vanilla JS with SweetAlert2 for notifications
- **Build Tool**: Vite for asset compilation
- **Template Engine**: Blade templates

## Development Tools
- **Code Style**: Laravel Pint for PHP formatting
- **Testing**: PHPUnit
- **Package Manager**: Composer (PHP), npm (Node.js)
- **Development Server**: Laravel Sail (Docker) or Artisan serve

## Common Commands

### Development
```bash
# Start development environment (all services)
composer run dev

# Individual services
php artisan serve          # Laravel server
npm run dev               # Vite development server
php artisan queue:listen  # Queue worker
php artisan pail          # Log monitoring
```

### Database
```bash
php artisan migrate       # Run migrations
php artisan migrate:fresh # Fresh migration
php artisan db:seed       # Run seeders
```

### Code Quality
```bash
./vendor/bin/pint         # Format PHP code
php artisan test          # Run tests
```

### Build
```bash
npm run build            # Production build
php artisan optimize     # Optimize for production
```