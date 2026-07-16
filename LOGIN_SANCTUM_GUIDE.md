# 🔐 Panduan Login Sanctum + Axios - Complete Guide

Dokumentasi lengkap untuk troubleshooting login gagal di Laravel 12 + Sanctum + Axios.

---

## 📋 Daftar Isi

1. [Masalah & Penyebabnya](#masalah--penyebabnya)
2. [Solusi yang Sudah Diterapkan](#solusi-yang-sudah-diterapkan)
3. [Cara Kerja Flow Login](#cara-kerja-flow-login)
4. [Cek Konfigurasi](#cek-konfigurasi)
5. [Testing Login](#testing-login)
6. [Debugging Error](#debugging-error)
7. [Common Problems & Solutions](#common-problems--solutions)

---

## ❌ Masalah & Penyebabnya

### Gejala: Login Gagal dengan Error "Credentials Invalid"

**Penyebab yang Umum:**

1. ❌ **Tidak ada CSRF Cookie Request** - Axios langsung POST ke `/login` tanpa GET `/sanctum/csrf-cookie` dulu
2. ❌ **`withCredentials` Tidak Diaktifkan** - Cookie CSRF tidak ikut dikirim di setiap request
3. ❌ **`APP_URL` Tidak Sesuai** - File `.env` punya `APP_URL=http://localhost` (tanpa port), harusnya `http://localhost:8000`
4. ❌ **`SANCTUM_STATEFUL_DOMAINS` Tidak Dikonfigurasi** - Sanctum tidak mengenali localhost sebagai trusted domain
5. ❌ **Session Driver** - Jika bukan `SESSION_DRIVER=database`, mungkin ada masalah session storage

---

## ✅ Solusi yang Sudah Diterapkan

### 1. File `.env` Sudah Diupdate

```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DOMAIN=.localhost
```

**Penjelasan:**

- `APP_URL=http://localhost:8000` → URL yang benar dengan port
- `SANCTUM_STATEFUL_DOMAINS` → List domain/host yang diizinkan untuk stateful auth
- `SESSION_DOMAIN=.localhost` → Cookie domain yang benar

### 2. File `resources/js/auth.js` Sudah Diperbaiki

**Perubahan Utama:**

#### a) Aktifkan Global `withCredentials`

```javascript
window.axios.defaults.withCredentials = true;
```

✅ Cookie CSRF otomatis dikirim di setiap request

#### b) Tambahkan CSRF Cookie Request

```javascript
async function getCsrfCookie() {
    try {
        await window.axios.get("/sanctum/csrf-cookie", {
            baseURL: "/", // Use root URL untuk csrf-cookie endpoint
        });
        console.log("✅ CSRF cookie obtained successfully");
        return true;
    } catch (error) {
        console.error("❌ Failed to get CSRF cookie:", error);
        return false;
    }
}
```

✅ Request ini mengambil CSRF token dari Sanctum middleware

#### c) Flow Login yang Benar

```javascript
// STEP 1: Get CSRF cookie dulu
const csrfSuccess = await getCsrfCookie();

// STEP 2: Baru login
const response = await window.axios.post("/login", {
    name,
    password,
    device_name: "browser",
});

// STEP 3: Store token
const token = response.data.token;
localStorage.setItem("api_token", token);
```

#### d) Error Handling yang Detail

```javascript
if (error.response?.status === 403) {
    errorBox.innerText =
        "🔒 CSRF token tidak valid. Silakan refresh page dan coba lagi.";
} else if (error.response?.status === 401) {
    errorBox.innerText = "❌ Email/nama atau password salah.";
}
```

---

## 🔄 Cara Kerja Flow Login

### Diagram Alur Login dengan CSRF

```
┌─────────────────────────────────────────────────────┐
│ User Klik Button Login                              │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ STEP 1: GET /sanctum/csrf-cookie                   │
│ • Request dari browser ke server                    │
│ • Server respond dengan CSRF token di cookie        │
│ • Browser otomatis simpan cookie (withCredentials) │
│ • ✅ Status 204 No Content                         │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ STEP 2: POST /api/login                            │
│ • Request dengan credentials:                       │
│   {                                                 │
│     "name": "user@example.com",                    │
│     "password": "password123",                      │
│     "device_name": "browser"                       │
│   }                                                 │
│ • Cookie CSRF otomatis ikut (withCredentials)     │
│ • ✅ Status 200 OK                                │
│ • Response:                                         │
│   {                                                 │
│     "message": "Login successful",                 │
│     "user": {...},                                 │
│     "token": "1|abc123xyz..."                      │
│   }                                                 │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ STEP 3: Store Token & Redirect                     │
│ • localStorage.setItem("api_token", token)         │
│ • window.location.href = "/dashboard"              │
└─────────────────────────────────────────────────────┘
```

### Penjelasan Setiap Step

**STEP 1: GET /sanctum/csrf-cookie**

- URL: `GET /sanctum/csrf-cookie` (bukan `/api/sanctum/csrf-cookie`)
- Fungsi: Mengambil CSRF token dari Sanctum middleware
- Response: Cookie dengan nama `XSRF-TOKEN` dan `laravel_session`
- Status: 204 No Content
- Timeout: ~100ms

**STEP 2: POST /api/login**

- URL: `POST /api/login`
- Body: JSON credentials (name/email, password, device_name)
- Header: `Cookie` (otomatis karena `withCredentials=true`)
- Response: User data + API token
- Status: 200 OK
- Token: Disimpan di `localStorage` untuk request selanjutnya

**STEP 3: Redirect ke Dashboard**

- Browser redirect ke `/dashboard`
- Session sudah authenticated
- User bisa akses protected pages

---

## ✔️ Cek Konfigurasi

### Checklist File & Config

| File                   | Yang Perlu Dicek                                                                 | Status              |
| ---------------------- | -------------------------------------------------------------------------------- | ------------------- |
| `.env`                 | `APP_URL=http://localhost:8000`                                                  | ✅ Sudah diperbaiki |
| `.env`                 | `SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1` | ✅ Sudah ditambah   |
| `.env`                 | `SESSION_DRIVER=database`                                                        | ✅ Sudah default    |
| `config/sanctum.php`   | 'stateful' => [...]                                                              | ✅ Sudah default    |
| `config/sanctum.php`   | 'guard' => ['web']                                                               | ✅ Sudah default    |
| `config/auth.php`      | 'defaults' => ['guard' => 'web']                                                 | ✅ Sudah default    |
| `routes/api.php`       | `Route::post('/login', ...)`                                                     | ✅ Sudah ada        |
| `resources/js/auth.js` | `withCredentials = true`                                                         | ✅ Sudah ditambah   |
| `resources/js/auth.js` | `getCsrfCookie()` function                                                       | ✅ Sudah ditambah   |

### Cara Manual Cek Setiap File

#### 1. Cek `.env`

Terminal:

```bash
cat .env | grep -E "APP_URL|SANCTUM_STATEFUL|SESSION"
```

Hasil yang diharapkan:

```
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DOMAIN=.localhost
SESSION_DRIVER=database
```

#### 2. Cek `config/sanctum.php`

Bagian yang penting:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort(),
))),

'guard' => ['web'],
```

#### 3. Cek `config/auth.php`

```php
'defaults' => [
    'guard' => env('AUTH_GUARD', 'web'),
    'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
],
```

---

## 🧪 Testing Login

### Test 1: Manual dengan curl

Terminal:

```bash
# Step 1: Get CSRF cookie
curl -c cookies.txt http://localhost:8000/sanctum/csrf-cookie -v

# Step 2: Login dengan cookie
curl -b cookies.txt -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"name":"nama_user","password":"password123","device_name":"browser"}'
```

**Expected Response:**

```json
{
    "message": "Login successful",
    "user": {
        "id": "uuid",
        "name": "nama_user",
        "email": "email@example.com",
        "created_at": "2026-07-14T10:00:00Z"
    },
    "token": "1|abc123xyz..."
}
```

### Test 2: Dari Browser DevTools

1. Buka halaman login: `http://localhost:8000/login`
2. Tekan **F12** → **Console** tab
3. Ketik kredensial yang benar di form
4. Klik tombol **Login**
5. Lihat di console:

```
🔐 Starting login process...
📝 Step 1: Fetching CSRF cookie...
✅ CSRF cookie obtained successfully
🔑 Step 2: Sending login credentials...
✅ Login response: {...}
💾 Token saved to localStorage
🎉 Login successful! Redirecting...
```

### Test 3: Dari Network Tab DevTools

1. Tekan F12 → **Network** tab
2. Filter: `api/`
3. Klik Login
4. Lihat requests:

| Request               | Status | Type | Notes               |
| --------------------- | ------ | ---- | ------------------- |
| `sanctum/csrf-cookie` | 204    | GET  | CSRF token endpoint |
| `api/login`           | 200    | POST | Login request       |

---

## 🐛 Debugging Error

### Jika Error: 401 Unauthorized

**Penjelasan:** Credentials tidak valid

**Debug langkah:**

```bash
# 1. Pastikan user ada di database
php artisan tinker
>>> App\Models\User::where('name', 'nama_user')->first();
# Harusnya return User object, bukan null

# 2. Test password hash
>>> $user = App\Models\User::first();
>>> Hash::check('password123', $user->password);
# Harusnya return true
```

### Jika Error: 403 Forbidden

**Penjelasan:** CSRF token tidak valid

**Debug langkah:**

```
1. Buka DevTools → Application tab
2. Cek Cookies:
   - Harus ada XSRF-TOKEN cookie
   - Harus ada laravel_session cookie
3. Jika tidak ada:
   - GET /sanctum/csrf-cookie gagal
   - Cek terminal: `php artisan serve` running?
```

### Jika Error: Network Error / Connection Refused

**Penjelasan:** Server tidak berjalan

**Debug langkah:**

```bash
# Terminal 1: Start server
php artisan serve

# Terminal 2: Test endpoint
curl http://localhost:8000/api/login

# Jika tidak bisa connect:
# Cek port 8000 sudah digunakan?
lsof -i :8000
```

### Jika Error: 422 Unprocessable Entity

**Penjelasan:** Validation error (missing field)

**Debug langkah:**

1. Lihat response di DevTools Network tab
2. Request body harus include:
    - `name` atau `email`
    - `password`
    - `device_name` (recommended)

### Jika Error: CORS Error

**Penjelasan:** Frontend dan backend di domain berbeda

**Debug langkah:**

```bash
# Cek .env APP_URL sesuai?
cat .env | grep APP_URL

# Harusnya: APP_URL=http://localhost:8000

# Jika berubah, clear cache:
php artisan config:clear
php artisan cache:clear
```

---

## 🆘 Common Problems & Solutions

### Problem 1: "CSRF token mismatch"

```
Error: POST /api/login - 419 (Unknown Status)
Response: {"message":"CSRF token mismatch."}
```

**Penyebab:** CSRF cookie tidak ter-fetch atau tidak dikirim

**Solusi:**

```javascript
// Pastikan di auth.js ada:
window.axios.defaults.withCredentials = true;

// Dan sebelum login, panggil:
await getCsrfCookie();
```

**Atau:** Clear browser cache & cookies:

```
DevTools → Application → Storage → Clear site data
```

---

### Problem 2: "The provided credentials are incorrect"

```
Error: POST /api/login - 401 (Unauthorized)
Response: {"message":"The provided credentials are incorrect."}
```

**Penyebab:** Email/name atau password salah

**Solusi:**

1. Pastikan user terdaftar:

    ```bash
    php artisan tinker
    >>> App\Models\User::all();
    ```

2. Test password dengan command:

    ```bash
    php artisan tinker
    >>> $user = App\Models\User::first();
    >>> Hash::check('password123', $user->password);
    ```

3. Atau buat user baru:
    ```bash
    php artisan tinker
    >>> User::create(['name'=>'test','email'=>'test@test.com','password'=>Hash::make('pass123')]);
    ```

---

### Problem 3: Login Berhasil tapi Token Tidak Tersimpan

```
✅ Login successful tapi:
- localStorage kosong
- Redirect tidak terjadi
```

**Penyebab:** localStorage disabled atau browser aturan berbeda

**Solusi:**

1. Check localStorage di DevTools:

    ```javascript
    DevTools → Console → typeof(localStorage)
    // Harusnya "object", bukan "undefined"
    ```

2. Check apakah token disimpan:

    ```javascript
    localStorage.getItem("api_token");
    // Harusnya return token string, bukan null
    ```

3. Enable cookies/storage di browser privacy settings

---

### Problem 4: Page Redirect ke Login Terus-Menerus

```
Login successful → Redirect ke /dashboard
Tapi langsung ke /login lagi
```

**Penyebab:** Dashboard route protected tapi session tidak valid

**Solusi:**

1. Pastikan token disimpan di localStorage
2. Check dashboard.blade.php tidak ada redirect
3. Clear browser cache:
    ```
    Ctrl+Shift+Delete → Clear All
    ```

---

### Problem 5: "withCredentials" Error

```
Error: "Access to XMLHttpRequest blocked by CORS policy"
```

**Penyebab:** `withCredentials=true` tapi CORS tidak dikonfigurasi

**Solusi:**

```bash
# Install Laravel CORS
composer require fruitcake/laravel-cors

# Publish config
php artisan vendor:publish --tag=cors

# Configure di .env:
CORS_ALLOWED_ORIGINS=http://localhost:8000
```

---

## 📝 Checklist Troubleshooting

Jika login gagal, cek ini **dalam urutan**:

- [ ] **Server Running**: `php artisan serve` di Terminal 1
- [ ] **Assets Built**: `npm run dev` di Terminal 2
- [ ] **.env Correct**:
    - `APP_URL=http://localhost:8000` ✅
    - `SANCTUM_STATEFUL_DOMAINS=...` ✅
    - `SESSION_DRIVER=database` ✅
- [ ] **Database Migrated**: `php artisan migrate:fresh`
- [ ] **User Exists**: `php artisan tinker` → `App\Models\User::all()`
- [ ] **Password Correct**: Double-check password saat login
- [ ] **auth.js Updated**:
    - `withCredentials = true` ✅
    - `getCsrfCookie()` di-panggil ✅
- [ ] **DevTools Console**: Lihat error message detail
- [ ] **Browser Cookies**: DevTools → Application → Cookies → Check XSRF-TOKEN
- [ ] **Network Tab**: Lihat status code setiap request
- [ ] **Cache Clear**: `php artisan optimize:clear` & browser cache

---

## 🎓 Poin Penting untuk Diingat

1. **CSRF Cookie HARUS diambil dulu** sebelum authenticated request

    ```javascript
    await getCsrfCookie(); // Step 1
    await axios.post('/api/login', ...); // Step 2
    ```

2. **`withCredentials` HARUS diaktifkan** agar cookie ikut dikirim

    ```javascript
    axios.defaults.withCredentials = true;
    ```

3. **`.env` harus benar** dengan APP_URL yang tepat

    ```env
    APP_URL=http://localhost:8000
    ```

4. **Session harus di-database** untuk Sanctum stateful auth

    ```env
    SESSION_DRIVER=database
    ```

5. **Sanctum STATEFUL_DOMAINS harus include localhost**
    ```env
    SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
    ```

---

## 📞 Quick Debug Commands

```bash
# Lihat user yang ada
php artisan tinker
>>> App\Models\User::all();

# Test login dengan command
>>> $user = App\Models\User::first();
>>> Hash::check('password_yang_ditest', $user->password);

# Reset semua cache
php artisan optimize:clear

# Jalankan migration ulang
php artisan migrate:fresh --seed

# Lihat config Sanctum
php artisan tinker
>>> config('sanctum');

# Test CSRF endpoint
curl http://localhost:8000/sanctum/csrf-cookie -v
```

---

**Semoga bermanfaat! Jika masih ada error, lihat console error message di browser untuk penyebab spesifik. 🚀**
