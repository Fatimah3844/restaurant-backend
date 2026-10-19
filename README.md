# 🍽️ Restaurant Backend API

A robust RESTful API backend for a restaurant management system, built with **Laravel 12** and **PHP 8.2**. The system supports multiple user roles (Admin, Cashier, Customer), handles the full ordering lifecycle, QR-based table scanning, product/menu management, customer enquiries, reviews, and real-time notifications.

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Server](#running-the-server)
- [API Overview](#api-overview)
  - [Authentication](#authentication)
  - [Admin – Users](#admin--users)
  - [Admin – Categories](#admin--categories)
  - [Admin – Products](#admin--products)
  - [Admin – Tables](#admin--tables)
  - [Admin – Enquiries](#admin--enquiries)
  - [Admin – Settings](#admin--settings)
  - [Public – Menu](#public--menu)
  - [Customer – Orders](#customer--orders)
  - [Customer – Reviews](#customer--reviews)
  - [Customer – Enquiries & Notifications](#customer--enquiries--notifications)
  - [Cashier – Orders](#cashier--orders)
  - [Public – Misc](#public--misc)
- [Project Structure](#project-structure)
- [User Roles & Middleware](#user-roles--middleware)
- [Running Tests](#running-tests)

---

## ✨ Features

- 🔐 **Authentication** – Register, login, logout, profile management, and password reset
- 👥 **Role-Based Access Control** – Admin, Cashier, and Customer roles with dedicated middleware
- 🛒 **Order Lifecycle** – Create, update items, submit, track, and cancel orders
- 🍕 **Menu Management** – Categories and products CRUD (Admin)
- 🪑 **Table Management** – QR code-based table scanning for dine-in ordering
- 💬 **Enquiries** – Customers submit enquiries; admins can list, receive, respond, and delete them
- ⭐ **Reviews** – Customers rate and review orders; admins can manage reviews
- 🔔 **Notifications** – Customer notification retrieval
- ⚙️ **Settings** – Restaurant-wide settings and terms management

---

## 🛠️ Tech Stack

| Layer        | Technology              |
|-------------|-------------------------|
| Framework    | Laravel 12              |
| Language     | PHP 8.2+                |
| Database     | SQLite (default) / MySQL|
| Queue        | Database-driven         |
| Build Tool   | Vite                    |
| Testing      | PHPUnit 11              |
| Dev Tools    | Laravel Pail, Laravel Pint, Laravel Sail |

---

## ✅ Requirements

- PHP >= 8.2
- Composer
- Node.js & npm
- SQLite (default) **or** MySQL/MariaDB

---

## 🚀 Installation

```bash
# 1. Clone the repository
git clone https://github.com/your-username/restaurant-backend-1.git
cd restaurant-backend-1

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy the environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

---

## ⚙️ Environment Configuration

Open `.env` and configure the following variables:

```env
APP_NAME=RestaurantApp
APP_ENV=local
APP_URL=http://localhost:8000

# Database (SQLite by default — no extra config needed)
DB_CONNECTION=sqlite

# To use MySQL instead, uncomment and fill these:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=restaurant_db
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Mail (use 'log' for local dev)
MAIL_MAILER=log
```

---

## 🗄️ Database Setup

```bash
# Run all migrations
php artisan migrate

# (Optional) Seed the database with sample data
php artisan db:seed
```

---

## ▶️ Running the Server

**Development (all services at once):**

```bash
composer run dev
```

This runs the Laravel server, queue listener, log monitor (Pail), and Vite concurrently.

**Or individually:**

```bash
# Laravel dev server
php artisan serve

# Queue worker
php artisan queue:listen --tries=1

# Vite (for front-end assets, if applicable)
npm run dev
```

The API will be available at: `http://localhost:8000/api`

---

## 📡 API Overview

Base URL: `http://localhost:8000/api`

All protected routes require a valid authentication token (passed via `Authorization: Bearer <token>` header or session cookie).

---

### Authentication

| Method | Endpoint          | Description              | Auth Required |
|--------|-------------------|--------------------------|:-------------:|
| POST   | `/register`       | Register a new user      | ❌            |
| POST   | `/login`          | Login and get token      | ❌            |
| POST   | `/logout`         | Logout current user      | ✅            |
| GET    | `/profile`        | Get authenticated profile| ✅            |
| PUT    | `/profile`        | Update profile           | ✅            |
| POST   | `/reset-password` | Reset user password      | ✅            |

---

### Admin – Users

> Requires `admin` role.

| Method | Endpoint       | Description         |
|--------|----------------|---------------------|
| GET    | `/users`       | List all users      |
| GET    | `/users/{id}`  | Show a user         |
| POST   | `/users`       | Create a user       |
| PUT    | `/users/{id}`  | Update a user       |
| DELETE | `/users/{id}`  | Delete a user       |

---

### Admin – Categories

> Requires `admin` role.

| Method | Endpoint             | Description           |
|--------|----------------------|-----------------------|
| GET    | `/categories`        | List all categories   |
| POST   | `/categories`        | Create a category     |
| PUT    | `/categories/{id}`   | Update a category     |
| DELETE | `/categories/{id}`   | Delete a category     |

---

### Admin – Products

> Requires `admin` role.

| Method | Endpoint           | Description         |
|--------|--------------------|---------------------|
| GET    | `/products`        | List all products   |
| GET    | `/products/{id}`   | Show a product      |
| POST   | `/products`        | Create a product    |
| PUT    | `/products/{id}`   | Update a product    |
| DELETE | `/products/{id}`   | Delete a product    |

---

### Admin – Tables

> Requires `admin` role.

| Method | Endpoint              | Description       |
|--------|-----------------------|-------------------|
| GET    | `/admin/tables`       | List all tables   |
| POST   | `/admin/tables`       | Create a table    |
| PUT    | `/admin/tables/{id}`  | Update a table    |
| DELETE | `/admin/tables/{id}`  | Delete a table    |

---

### Admin – Enquiries

> Requires `admin` role.

| Method | Endpoint                     | Description               |
|--------|------------------------------|---------------------------|
| GET    | `/enquiries/list`            | List all enquiries        |
| PUT    | `/enquiries/receive/{id}`    | Mark enquiry as received  |
| PUT    | `/enquiries/respond/{id}`    | Respond to an enquiry     |
| DELETE | `/enquiries/delete/{id}`     | Delete an enquiry         |

---

### Admin – Settings

> Requires `admin` role.

| Method | Endpoint                  | Description              |
|--------|---------------------------|--------------------------|
| PUT    | `/admin/settings/update`  | Update restaurant settings |

---

### Public – Menu

> No authentication required.

| Method | Endpoint            | Description                        |
|--------|---------------------|------------------------------------|
| GET    | `/menu`             | Get full menu (categories + items) |
| GET    | `/menu/products`    | Get all available products         |
| GET    | `/menu/categories`  | Get all categories                 |

---

### Customer – Orders

> Requires authentication.

| Method | Endpoint                    | Description                     |
|--------|-----------------------------|---------------------------------|
| POST   | `/orders`                   | Create a new order              |
| PUT    | `/orders/{id}/items`        | Update items in an order        |
| POST   | `/orders/{id}/submit`       | Submit (place) an order         |
| GET    | `/orders/{id}/track`        | Track order status              |
| POST   | `/orders/{id}/cancel`       | Cancel an order                 |
| GET    | `/my-orders`                | Get all orders for customer     |

---

### Customer – Reviews

> Requires authentication.

| Method | Endpoint         | Description           |
|--------|------------------|-----------------------|
| POST   | `/review/create` | Submit a review       |
| GET    | `/review/read`   | Read all reviews      |
| PUT    | `/review/update` | Update own review     |
| DELETE | `/review/delete` | Delete own review     |

---

### Customer – Enquiries & Notifications

> Requires authentication.

| Method | Endpoint          | Description                     |
|--------|-------------------|---------------------------------|
| POST   | `/enquiry`        | Submit a new enquiry            |
| GET    | `/notifications`  | Get customer notifications      |

---

### Cashier – Orders

> Requires `cashier` role.

| Method | Endpoint                | Description                  |
|--------|-------------------------|------------------------------|
| GET    | `/cashier_orders`       | View all active/pending orders |
| PUT    | `/orders/{id}/status`   | Update order status          |

---

### Public – Misc

| Method | Endpoint                    | Description                  |
|--------|-----------------------------|------------------------------|
| GET    | `/misc/view/terms`          | View restaurant terms        |
| GET    | `/misc/view/settings`       | View restaurant settings     |
| GET    | `/misc/scan/qr/{uuid}`      | Scan a table QR code         |

---

## 📁 Project Structure

```
restaurant-backend-1/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/
│   │   │       ├── AuthController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── CustomerController.php
│   │   │       ├── EnquiryController.php
│   │   │       ├── OrderController.php
│   │   │       ├── ProductController.php
│   │   │       ├── ReviewController.php
│   │   │       ├── SettingController.php
│   │   │       ├── TableController.php
│   │   │       └── UserController.php
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       ├── CashierMiddleware.php
│   │       └── checkUserId.php
│   └── Models/
│       ├── Category.php
│       ├── Enquiry.php
│       ├── Notification.php
│       ├── Order.php
│       ├── OrderItem.php
│       ├── OrderStatusChange.php
│       ├── Product.php
│       ├── Review.php
│       ├── Setting.php
│       ├── Table.php
│       └── User.php
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   └── api.php
├── .env.example
├── composer.json
└── phpunit.xml
```

---

## 🛡️ User Roles & Middleware

| Middleware       | Role     | Description                                              |
|-----------------|----------|----------------------------------------------------------|
| `checkUserId`   | Any      | Validates the authenticated user exists in the database  |
| `admin`         | Admin    | Restricts access to admin-only endpoints                 |
| `cashier`       | Cashier  | Restricts access to cashier-only endpoints               |

---

## 🧪 Running Tests

```bash
# Run all tests
composer run test

# Or directly with Artisan
php artisan test

# Or with PHPUnit
./vendor/bin/phpunit
```

---

## 📄 License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
