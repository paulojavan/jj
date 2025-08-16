# Project Context for Qwen Code

## Project Overview

This is a Laravel 12.x PHP web application for "JJ Calçados", a footwear store. The primary purpose of the application is to manage sales, inventory, customers, employees, and financial operations like accounts receivable and cash flow.

Key technologies and frameworks used:
- **Backend:** Laravel 12.x (PHP 8.2+)
- **Frontend:** Blade templates, Tailwind CSS 4.x, Flowbite, Vite 6.x
- **Database:** Likely MySQL or SQLite (common with Laravel, database type not explicitly stated in configs)
- **Image Processing:** Intervention Image 3.x
- **Development Tools:** Laravel Pint (code style), PHPUnit (testing), Laravel Sail (Docker development environment)

The application implements user authentication and role-based access control, as seen with the `auth` middleware and specific checks like `check.product.access`.

## Core Functionalities

Based on the routes (`routes/web.php`) and directory structure, the main features are:
- **User Authentication:** Login, logout.
- **Dashboard:** Main overview page (`/`).
- **Employee Management (`funcionario`):** CRUD operations for employees.
- **Customer Management (`clientes`):** Full CRUD, document/foto upload, purchase history, payment history, invoice generation (duplicata/carne).
- **Authorized Individuals (`autorizados`):** CRUD, linked to customers, document/foto upload.
- **Product Management (`produtos`):** CRUD, distribution, protected by `check.product.access`. Includes AJAX endpoints for sizes, returns, exchanges.
- **Shopping Cart (`carrinho`):** Add/remove items, apply discounts, finalize purchase (cash or credit).
- **Credit Sales (`carrinho/venda-crediario`):** Specific workflow for credit sales, including customer search and selection.
- **Product Categories:** Management for Brands (`marcas`), Subgroups (`subgrupos`), and Groups (`grupos`).
- **Accounts Receivable / Parcel Management (`parcelas`, `pagamentos`):** View and process customer payments on installments.
- **Cash Flow (`fluxo-caixa`):** General and individualized cash flow reports.
- **Fiscal Receipt (`baixa-fiscal`):** Process fiscal receipts for sales.
- **Customer Credit Limit Verification (`verificacao-limite`):** Check and manage customer credit limits and statuses.
- **Messages/Notifications (`mensagens-aviso`):** System for internal messages/notifications.
- **Expenses (`despesas`):** Track business expenses.
- **Store Hours (`horarios`):** Manage store opening/closing hours.
- **Discounts (`descontos`) & Late Fee Configuration (`multa-configuracao`):** Manage pricing and penalties.

## Building and Running

### Prerequisites

- PHP 8.2 or higher
- Composer (PHP dependency manager)
- Node.js and NPM (Frontend asset management)
- A database server (MySQL, MariaDB, PostgreSQL, or SQLite)

### Setup

1.  **Clone & Dependencies:**
    ```bash
    # Clone the project (already done if you are reading this)
    # cd /path/to/JJ

    # Install PHP dependencies
    composer install

    # Install JavaScript dependencies
    npm install
    ```
2.  **Environment Configuration:**
    ```bash
    # Copy the example environment file
    cp .env.example .env

    # Generate application key
    php artisan key:generate
    ```
3.  **Database Setup:**
    - Configure your database connection details in the `.env` file (e.g., `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
    - Run database migrations (and seeders if available) to set up the schema.
    ```bash
    # Create database tables
    php artisan migrate

    # (Optional) Seed the database with initial data if seeders exist
    # php artisan db:seed
    ```
4.  **Localization (if needed):**
    The project includes `lucascudo/laravel-pt-br-localization`, suggesting Portuguese (Brazil) localization might be relevant. Publishing these assets might be necessary.
    ```bash
    # Publish Portuguese (Brazil) localization files (if not already done)
    php artisan vendor:publish --tag=laravel-pt-br-lang
    ```

### Running the Application

Laravel provides several ways to run the application locally.

1.  **Development Server (Single Command):**
    The `composer.json` defines a `dev` script that starts the necessary development services concurrently.
    ```bash
    # Starts PHP server, queue listener, log tailer, and Vite dev server
    composer run dev
    ```
    This is the recommended way as it handles multiple required background processes.

2.  **Manual Commands (Alternative):**
    You can also run the necessary commands in separate terminals:
    -   **PHP Development Server:**
        ```bash
        php artisan serve
        ```
        This typically starts the application at `http://localhost:8000`.
    -   **Background Queue Listener (if queues are used):**
        ```bash
        php artisan queue:listen --tries=1
        ```
    -   **Log Viewer (for development):**
        ```bash
        php artisan pail --timeout=0
        ```
    -   **Frontend Asset Development Server:**
        ```bash
        npm run dev
        ```
        This compiles and serves Tailwind CSS and JavaScript assets.

### Building for Production

1.  **Optimize Autoloader:**
    ```bash
    composer install --optimize-autoloader --no-dev
    ```
2.  **Build Frontend Assets:**
    ```bash
    npm run build
    ```
    This compiles and minifies CSS and JavaScript assets for production.
3.  **Run Other Production Optimizations (Standard Laravel):**
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    # php artisan event:cache # If event discovery is used
    ```

## Development Conventions

- **PHP:** Follows PSR standards. Laravel Pint is included for code style fixing (`vendor/bin/pint` or likely `./vendor/bin/pint`).
- **JavaScript/CSS:** Uses Vite with Tailwind CSS v4 and Flowbite for styling. `tailwind.config.js` and `vite.config.js` manage the build process.
- **Architecture:** Standard Laravel MVC (Model-View-Controller) structure with Controllers, Models, and Blade Views.
  - Controllers are located in `app/Http/Controllers/`.
  - Models in `app/Models/`.
  - Views (Blade templates) in `resources/views/`.
  - Routes defined in `routes/web.php`.
  - Database migrations in `database/migrations/`.
- **Database:** Uses Eloquent ORM for database interactions. Migrations are used for schema management.
- **Authentication:** Uses Laravel's built-in authentication scaffolding, protected by `auth` middleware.
- **Authorization:** Custom middleware like `check.product.access` is used for specific feature access control.