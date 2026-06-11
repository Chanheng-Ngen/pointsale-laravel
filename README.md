# Point Sale System - Backend

A robust Laravel-based backend for a Sales-only Point of Sale (POS) system.

## Features

- **Product Inventory**: Manage products, stock levels, min/max stock, and categories.
- **Sales Transactions**: Automated transaction logging for all completed orders.
- **Order Management**: Track orders from pending to completed states.
- **Analytics**: Real-time sales summaries, trends, and top-selling products.
- **Timezone Optimized**: Configured for `Asia/Phnom_Penh` for accurate local reporting.

## Tech Stack

- **Framework**: Laravel 11.x
- **Database**: MySQL
- **Architecture**: REST API

## Getting Started

### Prerequisites

- PHP >= 8.3
- Composer
- MySQL

### Installation

1.  **Clone the repository**
2.  **Install dependencies**
    ```bash
    composer install
    ```
3.  **Setup Environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Configure Database**
    Update your `.env` file with your database credentials:
    ```env
    DB_DATABASE=point_sale
    DB_USERNAME=root
    DB_PASSWORD=
    ```
5.  **Run Migrations & Seeders**
    ```bash
    php artisan migrate --seed
    ```
6.  **Start the Server**
    ```bash
    php artisan serve
    ```

## API Documentation

The backend provides endpoints for:
- `/api/auth/*` - Authentication (Register, Login, Me, Logout)
- `/api/products` - Inventory Management
- `/api/categories` - Product Categorization
- `/api/orders` - Order Processing
- `/api/transactions` - Financial Records
- `/api/analytics` - Business Insights

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
