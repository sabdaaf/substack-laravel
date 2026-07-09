# Foogu CMS API

Laravel-based Content Management System API dengan Laravel Sanctum Authentication.

## Features

-   ✅ **Laravel 12** - Framework PHP modern
-   ✅ **Laravel Sanctum** - Token-based API authentication
-   ✅ **UUID Primary Keys** - Secure unique identifiers untuk semua users
-   ✅ **Form Request Validation** - Input validation dengan custom error messages
-   ✅ **Comprehensive Tests** - Full test coverage dengan Pest PHP (49 tests, 234 assertions)
-   ✅ **RESTful API** - Well-structured API endpoints
-   ✅ **Rate Limiting (Throttling)** - Multi-tier rate limiting untuk security & resource protection

## Requirements

-   PHP >= 8.2
-   Composer
-   SQLite (atau database lain sesuai kebutuhan)

## Installation

1. Clone repository:

```bash
git clone <repository-url>
cd foogu-cms-api
```

2. Install dependencies:

```bash
composer install
```

3. Copy environment file:

```bash
cp .env.example .env
```

4. Generate application key:

```bash
php artisan key:generate
```

5. Run migrations:

```bash
php artisan migrate
```

6. Start development server:

```bash
php artisan serve
```

API akan berjalan di `http://localhost:8000`

## API Documentation

Lihat dokumentasi lengkap API di [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

### Quick Start

#### 1. Register User Baru

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "postman"
  }'
```

#### 2. Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123",
    "device_name": "postman"
  }'
```

Simpan token yang diterima untuk request selanjutnya.

#### 3. Create a Post

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "My First Post",
    "body": "This is the content of my first blog post."
  }'
```

#### 4. Get All Posts

```bash
curl -X GET http://localhost:8000/api/posts \
  -H "Accept: application/json"
```

#### 5. Get Current User

```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

#### 6. Logout

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Available Endpoints

| Method    | Endpoint          | Auth Required | Description                 |
| --------- | ----------------- | ------------- | --------------------------- |
| POST      | `/api/register`   | No            | Register user baru          |
| POST      | `/api/login`      | No            | Login dan dapatkan token    |
| GET       | `/api/posts`      | No            | List semua posts            |
| GET       | `/api/posts/{id}` | No            | Get detail post             |
| POST      | `/api/posts`      | Yes           | Create post baru            |
| PUT/PATCH | `/api/posts/{id}` | Yes           | Update post (author only)   |
| DELETE    | `/api/posts/{id}` | Yes           | Delete post (author only)   |
| POST      | `/api/logout`     | Yes           | Logout dan hapus token      |
| GET       | `/api/me`         | Yes           | Get user yang sedang login  |
| GET       | `/api/user`       | Yes           | Get user info (alternative) |

## Database Schema

### Users Table (UUID Primary Key)

-   `id` (UUID) - Primary Key
-   `name` (string)
-   `email` (string, unique)
-   `email_verified_at` (timestamp, nullable)
-   `password` (string, hashed)
-   `remember_token` (string, nullable)
-   `created_at`, `updated_at` (timestamps)

### Posts Table (UUID Primary Key)

-   `id` (UUID) - Primary Key
-   `title` (string) - Judul post
-   `slug` (string, unique) - URL-friendly slug
-   `body` (text) - Konten post
-   `author_id` (UUID) - Foreign key to users
-   `created_at`, `updated_at` (timestamps)

### Personal Access Tokens Table

-   Menyimpan Sanctum authentication tokens
-   Mendukung multiple devices per user
-   Token abilities/permissions support

## Testing

Run all tests:

```bash
php artisan test
```

Run specific test:

```bash
php artisan test --filter=AuthTest
php artisan test --filter=PostTest
```

Test Results:

-   ✅ 30 tests passed (120 assertions)
-   ✅ 9 authentication tests
-   ✅ 19 post CRUD tests

## Code Style

Project ini menggunakan Laravel Pint untuk code formatting:

```bash
./vendor/bin/pint
```

Atau check tanpa fix:

```bash
./vendor/bin/pint --test
```

## Development Tools

-   **Laravel Boost** - Enhanced development experience dengan MCP
-   **Pest PHP** - Modern testing framework
-   **Laravel Pint** - Code formatter
-   **Laravel Sail** - Docker development environment (optional)

## Security

-   Passwords di-hash menggunakan bcrypt
-   API tokens di-hash dengan SHA-256
-   CSRF protection untuk web routes
-   UUID sebagai primary key untuk keamanan tambahan
-   Rate limiting dapat dikonfigurasi di `bootstrap/app.php`

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
