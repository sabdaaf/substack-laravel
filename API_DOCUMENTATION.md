# API Documentation - Foogu CMS API

## Base URL

`http://your-domain.com/api`

## 🛡️ Rate Limiting

All API endpoints are protected with rate limiting to prevent abuse:

| Endpoint Type                              | Rate Limit          | Tracked By |
| ------------------------------------------ | ------------------- | ---------- |
| **Authentication** (`/register`, `/login`) | 5 requests/minute   | IP Address |
| **Public** (`GET /posts`)                  | 60 requests/minute  | IP Address |
| **Authenticated** (Protected endpoints)    | 100 requests/minute | User ID    |

### Rate Limit Headers

Every response includes rate limit information:

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
```

### 429 Too Many Requests

When rate limit is exceeded:

```json
{
    "message": "Too many requests. Please try again later.",
    "retry_after": 60
}
```

📖 **Detailed Documentation**: See [THROTTLING_DOCUMENTATION.md](THROTTLING_DOCUMENTATION.md)

---

## Table of Contents

1. [Authentication Endpoints](#authentication-endpoints)
2. [Post Management Endpoints](#post-management-endpoints)

---

## Authentication Endpoints

### 1. Register

Register pengguna baru dan dapatkan token akses.

**Endpoint:** `POST /register`

**Request Body:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "web-browser"
}
```

**Response Success (201):**

```json
{
    "message": "User registered successfully",
    "user": {
        "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2026-01-10T06:30:00.000000Z",
        "updated_at": "2026-01-10T06:30:00.000000Z"
    },
    "token": "1|laravel_sanctum_token_string_here"
}
```

**Response Error (422):**

```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### 2. Login

Login dengan kredensial yang valid dan dapatkan token akses.

**Endpoint:** `POST /login`

**Request Body:**

```json
{
    "email": "john@example.com",
    "password": "password123",
    "device_name": "web-browser"
}
```

**Response Success (200):**

```json
{
    "message": "Login successful",
    "user": {
        "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2026-01-10T06:30:00.000000Z",
        "updated_at": "2026-01-10T06:30:00.000000Z"
    },
    "token": "2|laravel_sanctum_token_string_here"
}
```

**Response Error (422):**

```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

---

### 3. Get Current User

Mendapatkan informasi user yang sedang login.

**Endpoint:** `GET /me`

**Headers:**

```
Authorization: Bearer {your_token_here}
Accept: application/json
```

**Response Success (200):**

```json
{
    "user": {
        "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2026-01-10T06:30:00.000000Z",
        "updated_at": "2026-01-10T06:30:00.000000Z"
    }
}
```

**Response Error (401):**

```json
{
    "message": "Unauthenticated."
}
```

---

### 4. Logout

Logout dan hapus token akses saat ini.

**Endpoint:** `POST /logout`

**Headers:**

```
Authorization: Bearer {your_token_here}
Accept: application/json
```

**Response Success (200):**

```json
{
    "message": "Logged out successfully"
}
```

**Response Error (401):**

```json
{
    "message": "Unauthenticated."
}
```

---

## Post Management Endpoints

### 5. Get All Posts (Public)

Mendapatkan daftar semua posts dengan pagination, filtering, dan sorting.

**Endpoint:** `GET /posts`

**Query Parameters:**

-   `per_page` (optional, default: 10): Jumlah posts per halaman
-   `page` (optional, default: 1): Nomor halaman
-   `sort_by` (optional, default: created_at): Field untuk sorting (id, title, slug, created_at, updated_at)
-   `order` (optional, default: desc): Urutan sorting (asc, desc)
-   `author_id` (optional): Filter berdasarkan ID author
-   `search` (optional): Pencarian di title atau body
-   `slug` (optional): Filter berdasarkan slug tertentu

**Examples:**

```
GET /api/posts
GET /api/posts?per_page=20&page=2
GET /api/posts?sort_by=title&order=asc
GET /api/posts?author_id=9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b
GET /api/posts?search=Laravel
GET /api/posts?slug=my-first-post
GET /api/posts?per_page=15&sort_by=created_at&order=desc&search=tutorial
```

**Response Success (200):**

```json
{
    "data": [
        {
            "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
            "title": "My First Blog Post",
            "slug": "my-first-blog-post",
            "body": "This is the content...",
            "author_id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
            "created_at": "2026-01-10T06:30:00.000000Z",
            "updated_at": "2026-01-10T06:30:00.000000Z",
            "author": {
                "id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    ],
    "links": {...},
    "meta": {...}
}
```

---

### 6. Get Single Post (Public)

Mendapatkan detail satu post berdasarkan slug.

**Endpoint:** `GET /posts/{slug}`

**Example:** `GET /api/posts/my-first-blog-post`

**Response Success (200):**

```json
{
    "post": {
        "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
        "title": "My First Blog Post",
        "slug": "my-first-blog-post",
        "body": "This is the content...",
        "author_id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
        "created_at": "2026-01-10T06:30:00.000000Z",
        "updated_at": "2026-01-10T06:30:00.000000Z",
        "author": {
            "id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

**Response Error (404):**

```json
{
    "message": "No query results for model [App\\Models\\Post]."
}
```

---

### 7. Create Post (Protected)

Membuat post baru. Memerlukan autentikasi. Slug akan otomatis dibuat dari title jika tidak disediakan.

**Endpoint:** `POST /posts`

**Headers:**

```
Authorization: Bearer {your_token_here}
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
    "title": "My New Blog Post",
    "slug": "my-new-blog-post",
    "body": "This is the content of my blog post..."
}
```

**Note:**

-   `slug` is optional. If not provided, it will be auto-generated from the title.
-   `author_id` is automatically set to the authenticated user.

**Response Success (201):**

```json
{
    "message": "Post created successfully",
    "post": {
        "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
        "title": "My New Blog Post",
        "slug": "my-new-blog-post",
        "body": "This is the content of my blog post...",
        "author_id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
        "created_at": "2026-01-10T06:30:00.000000Z",
        "updated_at": "2026-01-10T06:30:00.000000Z",
        "author": {
            "id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

**Response Error (401):**

```json
{
    "message": "Unauthenticated."
}
```

**Response Error (422):**

```json
{
    "message": "The title field is required.",
    "errors": {
        "title": ["Judul wajib diisi"],
        "body": ["Konten wajib diisi"]
    }
}
```

---

### 8. Update Post (Protected)

Update post yang sudah ada. Hanya author yang dapat mengupdate postnya sendiri. Jika title diubah, slug akan otomatis di-generate ulang.

**Endpoint:** `PUT /posts/{slug}` atau `PATCH /posts/{slug}`

**Example:** `PUT /api/posts/my-first-blog-post`

**Headers:**

```
Authorization: Bearer {your_token_here}
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
    "title": "Updated Title",
    "body": "Updated content..."
}
```

**Note:** All fields are optional. Only send fields you want to update.

**Response Success (200):**

```json
{
    "message": "Post updated successfully",
    "post": {
        "id": "9d4f5a6b-7c8d-9e0f-1a2b-3c4d5e6f7a8b",
        "title": "Updated Title",
        "slug": "updated-title",
        "body": "Updated content...",
        "author_id": "8c3e4b5a-6b7c-8d9e-0f1a-2b3c4d5e6f7a",
        "created_at": "2026-01-10T06:30:00.000000Z",
        "updated_at": "2026-01-10T06:35:00.000000Z",
        "author": {...}
    }
}
```

**Response Error (403):**

```json
{
    "message": "This action is unauthorized."
}
```

---

### 9. Delete Post (Protected)

Hapus post. Hanya author yang dapat menghapus postnya sendiri.

**Endpoint:** `DELETE /posts/{slug}`

**Example:** `DELETE /api/posts/my-first-blog-post`

**Headers:**

```
Authorization: Bearer {your_token_here}
Accept: application/json
```

**Response Success (200):**

```json
{
    "message": "Post deleted successfully"
}
```

**Response Error (403):**

```json
{
    "message": "Forbidden. You can only delete your own posts."
}
```

---

## Protected Routes

Semua endpoint yang memerlukan autentikasi harus menyertakan header `Authorization` dengan token Bearer:

```
Authorization: Bearer {your_token_here}
```

### Public Endpoints (No Auth Required)

-   `GET /posts` - List all posts
-   `GET /posts/{slug}` - Get single post by slug
-   `POST /register` - Register new user
-   `POST /login` - Login

### Protected Endpoints (Auth Required)

-   `POST /posts` - Create post
-   `PUT /posts/{slug}` - Update post
-   `PATCH /posts/{slug}` - Partial update post
-   `DELETE /posts/{slug}` - Delete post
-   `POST /logout` - Logout
-   `GET /me` - Get current user

---

## Features Implemented

1. ✅ **Laravel Sanctum** - Token-based API authentication
2. ✅ **UUID Primary Keys** - Semua user dan post menggunakan UUID sebagai ID
3. ✅ **Form Request Validation** - Validasi input dengan pesan error dalam Bahasa Indonesia
4. ✅ **Auto Slug Generation** - Slug otomatis dibuat dari title dan dijamin unique
5. ✅ **Slug-based Routing** - Get, update, dan delete post menggunakan slug sebagai parameter
6. ✅ **Pagination, Filtering, Sorting** - Posts dapat dipaginasi, difilter, dan disort dengan berbagai parameter
7. ✅ **CRUD Posts** - Create, Read, Update, Delete posts dengan authorization
8. ✅ **Auto Slug Generation** - Slug otomatis dibuat dari title
9. ✅ **Author Authorization** - Hanya author yang bisa update/delete postnya
10. ✅ **Relationship** - User has many Posts, Post belongs to User
11. ✅ **Comprehensive Tests** - 30 test cases (120 assertions)
12. ✅ **RESTful API Structure** - Endpoint terorganisir dengan baik
13. ✅ **Pagination** - Posts list dengan pagination

---

## Database Schema

### Users Table

-   `id` (UUID) - Primary Key
-   `name` (string) - Nama lengkap user
-   `email` (string) - Email unik
-   `email_verified_at` (timestamp) - Waktu verifikasi email
-   `password` (string) - Password ter-hash
-   `remember_token` (string) - Token untuk remember me
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

### Posts Table

-   `id` (UUID) - Primary Key
-   `title` (string) - Judul post
-   `slug` (string) - URL-friendly slug (unique)
-   `body` (text) - Konten post
-   `author_id` (UUID) - Foreign key ke users table
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

### Personal Access Tokens Table

-   `id` (integer) - Primary Key
-   `tokenable_type` (string) - Model type
-   `tokenable_id` (integer) - Model ID
-   `name` (string) - Token name/device name
-   `token` (string) - Hashed token
-   `abilities` (text) - Token abilities/permissions
-   `last_used_at` (timestamp) - Last usage time
-   `expires_at` (timestamp) - Expiration time
-   `created_at` (timestamp)
-   `updated_at` (timestamp)

---

## Testing

Run tests dengan command:

```bash
# Run authentication tests
php artisan test --filter=AuthTest

# Run post CRUD tests
php artisan test --filter=PostTest

# Run all tests
php artisan test
```

Test Results:

-   ✅ 39 tests passed (151 assertions)
-   ✅ 9 authentication tests
-   ✅ 28 post CRUD tests (termasuk pagination, sorting, filtering)
-   ✅ 2 example tests

---

## Notes

-   Token akan tetap aktif sampai di-revoke melalui endpoint logout
-   Satu user bisa memiliki multiple tokens (untuk different devices)
-   Token disimpan secara aman dengan SHA-256 hashing
-   UUID memastikan ID yang unique dan secure
