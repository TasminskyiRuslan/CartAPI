# Cart API 🛒

**Cart API** is a high-performance RESTful service for managing shopping cart functionality with full support for guest and authenticated users. It features automated cart merging, price snapshots, and advanced expiration logic.

---

## 🛠 Tech Stack

* **Framework:** Laravel 12
* **Language:** PHP 8.4
* **Database:** MySQL 8.0
* **Cache & Queue:** Redis 7
* **Server:** Nginx (Alpine)
* **API Docs:** L5-Swagger (OpenAPI 3)
* **Testing:** Pest PHP
* **Utilities:**
* Spatie Data (DTOs & Validation)
* Laravel Sanctum (Auth)

---

## 🐳 Prerequisites

Ensure you have installed:

* Docker
* Docker Compose

---

## 🚀 Installation & Setup

### 1. Clone repository

```bash
git clone git@github.com:TasminskyiRuslan/CartAPI.git
cd CartAPI
```

### 2. Configure environment

```bash
cp .env.example .env
```

### 3. Start containers

```bash
docker compose up -d --build
```

### 4. Install dependencies

```bash
docker compose exec app composer install
```

### 5. Setup application

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 6. Generate Swagger documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

---

## 📚 API Documentation

Swagger / OpenAPI documentation is available once the server is running.

👉 **[View API Documentation](http://localhost:8080/api/documentation)**

---

## 🧪 Running Tests

Run full test suite:

```bash
docker compose exec app php artisan test
```

---

## ✨ Key Features

### Authentication & Identification

* **Sanctum:** Token-based authentication (`Bearer`).
* **Guest Support:** Identification header (`Guest-Token`).
* **Cart Merge:** Sophisticated logic to merge guest items into a user's cart upon login/register, preventing duplicate products and summing quantities.

### Cart & Item Logic

* **Price Snapshots:** Captures the product price at the moment of entry.
* **Quantity Limits:** Items are limited to a maximum quantity of **99** per product.
* **Database Integrity:** SQL-level CHECK constraints to ensure a cart belongs to either a user or a guest, but never both.

### Automated Lifecycle

* **Expiration:** Authenticated carts live for 30 days, while guest carts expire after 3 days.
* **Auto-Prune:** A daily automated Cron task identifies and deletes expired carts and their items to maintain database efficiency.

---

## 📂 Project Structure

```text
app/Http/Controllers/Api   # Handles API requests and returns responses
app/Models                 # Contains all Eloquent models
app/Http/Resources         # API Resources for output formatting
app/Data                   # Defines structured input data for the API (DTOs)
app/Actions                # Performs single operations and business logic
app/Listeners/Cart         # Listeners for merging carts on authentication
app/Swagger/               # Swagger annotations and definitions
database/migrations        # Database structure changes
routes/api.php             # API Routes definitions
docker/                    # Docker configuration files
tests/                     # Feature and Unit tests (Pest)
```

---

## 📊 Database Design

### Entities

* **Product:** Catalog of products available
* **User:** Registered customers
* **Cart:** Cart belonging to a user or a guest.
* **CartItem:** List of products inside a specific cart with their quantity.

### Relations

* **User** `hasOne` **Cart**
* **Product** `hasMany` **CartItem**
* **Cart** `hasMany` **CartItem**

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).
