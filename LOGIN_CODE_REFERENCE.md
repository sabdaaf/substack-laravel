# 📖 Referensi Kode Login Sanctum - Penjelasan Baris per Baris

Dokumentasi lengkap kode login yang sudah diperbaiki dengan penjelasan detail.

---

## 📄 File 1: `.env` Configuration

### Konfigurasi Yang Benar

```env
# ============================================
# Basic Configuration
# ============================================

APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:c/mooMfRgZtIfn58HzcYGvufHYzHvXZ4621mapbZGRU=

# ⭐ CRITICAL: APP_URL dengan port yang tepat
APP_URL=http://localhost:8000
# Tanpa port akan menyebabkan CSRF cookie tidak dikirim!

# ============================================
# Sanctum Configuration
# ============================================

# ⭐ CRITICAL: Daftar domain yang trusted untuk stateful auth
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
# Jika browser request dari domain ini, Sanctum akan allow session cookies

# Session domain untuk cookie
SESSION_DOMAIN=.localhost
# Cookie akan dikirim ke .localhost, .localhost:8000, dll

# ============================================
# Session Configuration
# ============================================

# ⭐ CRITICAL: Harus 'database' untuk Sanctum stateful auth
SESSION_DRIVER=database
# Jangan gunakan 'file' atau 'cookie' karena Sanctum perlu database session!

SESSION_LIFETIME=120       # Session valid 120 menit
SESSION_ENCRYPT=false      # Tidak perlu encrypt session
SESSION_PATH=/             # Cookie path
```

### Penjelasan Setiap Setting

| Setting                    | Nilai                   | Penjelasan                                       |
| -------------------------- | ----------------------- | ------------------------------------------------ |
| `APP_URL`                  | `http://localhost:8000` | Harus sesuai dengan URL browser untuk CSRF token |
| `APP_DEBUG`                | `true`                  | Untuk development, biar lihat error detail       |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost:8000,...`    | Domain trusted untuk stateful auth               |
| `SESSION_DRIVER`           | `database`              | Session disimpan di DB, bukan file               |
| `SESSION_ENCRYPT`          | `false`                 | Session tidak di-encrypt di development          |

---

## 📄 File 2: `resources/js/bootstrap.js` - Global Axios Setup

### Kode Lengkap Dengan Penjelasan

```javascript
/**
 * resources/js/bootstrap.js
 *
 * File ini setup Axios global untuk seluruh aplikasi
 * Dijalankan otomatis saat app.js di-import
 *
 * PENTING: Semua setting di sini berlaku untuk SETIAP request Axios!
 */

import axios from "axios";

// ============================================
// 1. BUAT INSTANCE AXIOS GLOBAL
// ============================================

window.axios = axios;
// Agar bisa diakses dari mana saja: window.axios.post(), dll
// Contoh: window.axios.get('/api/posts')

// ============================================
// 2. SETUP DEFAULT HEADERS
// ============================================

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
// Header ini memberi tahu server bahwa ini AJAX request, bukan form submit
// Value: 'XMLHttpRequest' adalah standard untuk AJAX

// ============================================
// 3. ⭐⭐⭐ CRITICAL - ENABLE CREDENTIALS (COOKIES)
// ============================================

/**
 * MOST IMPORTANT SETTING!
 *
 * withCredentials = true mengaktifkan:
 * ✅ Mengirim cookies dari GET /sanctum/csrf-cookie
 * ✅ Menyimpan XSRF-TOKEN cookie secara otomatis
 * ✅ Mengirim cookies di setiap request ke API
 * ✅ Sanctum bisa verify session user
 *
 * Tanpa ini: CSRF token tidak bisa dikirim → Login gagal 401
 */
window.axios.defaults.withCredentials = true;

// ============================================
// 4. LOAD API TOKEN DARI LOCALSTORAGE (JIKA ADA)
// ============================================

/**
 * Jika user sudah login sebelumnya, token ada di localStorage
 * Kita load token dan setup di headers otomatis
 *
 * Flow:
 * 1. User login → token disimpan ke localStorage
 * 2. User refresh page
 * 3. bootstrap.js run ulang
 * 4. Load token dari localStorage
 * 5. Setup di Authorization header
 * 6. Request berikutnya sudah authenticated tanpa login ulang
 */
const token = localStorage.getItem("api_token");
if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    console.log("✅ API token loaded from localStorage");
}

// ============================================
// 5. REQUEST INTERCEPTOR
// ============================================

/**
 * Interceptor menangkap request SEBELUM dikirim ke server
 * Berguna untuk setup header atau log request
 */
window.axios.interceptors.request.use(
    (config) => {
        // STEP 1: Pastikan token ada di setiap request
        if (token) {
            config.headers["Authorization"] = `Bearer ${token}`;
        }

        // STEP 2: Return config yang sudah di-setup
        return config;
    },
    (error) => {
        // Jika ada error saat setup request
        console.error("❌ Request Interceptor Error:", error);
        return Promise.reject(error);
    },
);

// ============================================
// 6. RESPONSE INTERCEPTOR
// ============================================

/**
 * Interceptor menangkap response SETELAH diterima dari server
 * Berguna untuk handle error global atau token expiry
 */
window.axios.interceptors.response.use(
    (response) => {
        // Response sukses (2xx status)
        // Cukup return response apa adanya
        return response;
    },
    (error) => {
        // Response error (4xx, 5xx status)
        // Handle token invalid atau expired

        if (error.response?.status === 401 || error.response?.status === 403) {
            // 401: Unauthorized (token invalid)
            // 403: Forbidden (token expired/revoked)

            console.warn("⚠️ Authentication failed. Token invalid or expired.");

            // Hapus token dari localStorage
            localStorage.removeItem("api_token");

            // Hapus dari header
            window.axios.defaults.headers.common["Authorization"] = "";

            // Optional: Redirect ke login
            // window.location.href = '/login';
        }

        return Promise.reject(error);
    },
);

// ============================================
// 7. SETUP CSRF TOKEN DARI META TAG
// ============================================

/**
 * Jika HTML punya <meta name="csrf-token">
 * Ambil value dan setup di header
 * Berguna untuk form submission (bukan Axios)
 */
const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfTokenMeta) {
    const csrfToken = csrfTokenMeta.getAttribute("content");
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
}

// ============================================
// 8. LOGGER UNTUK DEVELOPMENT
// ============================================

/**
 * Log informasi setup Axios saat di development
 * Membantu debugging jika ada masalah
 */
if (
    window.location.hostname === "localhost" ||
    window.location.hostname === "127.0.0.1"
) {
    console.log("🔧 Axios Global Configuration:");
    console.log("  ✅ withCredentials:", window.axios.defaults.withCredentials);
    console.log(
        "  ✅ X-Requested-With:",
        window.axios.defaults.headers.common["X-Requested-With"],
    );
    console.log(
        "  " + (token ? "✅" : "❌") + " API Token:",
        token ? "Present" : "Not found",
    );
}

export default window.axios;
```

---

## 📄 File 3: `resources/js/auth.js` - Login & Register

### Kode Login Lengkap Dengan Penjelasan

```javascript
import axios from "axios";

// ============================================
// SETUP AXIOS INSTANCE
// ============================================

window.axios = axios;

// Header untuk identify AJAX request
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// ⭐ CRITICAL: Enable credentials (cookies)
window.axios.defaults.withCredentials = true;

// Set API base URL
window.axios.defaults.baseURL = "/api";

// ============================================
// CSRF COOKIE HELPER FUNCTION
// ============================================

/**
 * getCsrfCookie()
 *
 * Fungsi ini HARUS dipanggil SEBELUM login!
 *
 * Yang dilakukan:
 * 1. Request GET ke /sanctum/csrf-cookie
 * 2. Server respond dengan CSRF token di cookie
 * 3. Browser otomatis simpan cookie (karena withCredentials=true)
 * 4. Baru setelah ini, login request bisa berhasil
 *
 * Tanpa function ini: Login gagal 419 (CSRF token mismatch)
 */
async function getCsrfCookie() {
    try {
        // Request ke endpoint Sanctum untuk get CSRF token
        await window.axios.get("/sanctum/csrf-cookie", {
            baseURL: "/", // Use root URL, bukan /api
        });

        console.log("✅ CSRF cookie obtained successfully");
        return true;
    } catch (error) {
        console.error("❌ Failed to get CSRF cookie:", error);
        return false;
    }
}

// ============================================
// LOGIN FORM HANDLER
// ============================================

const form = document.getElementById("form-login");
const errorBox = document.getElementById("error-message");

if (form) {
    // Tunggu form di-submit
    form.addEventListener("submit", async function (e) {
        // Prevent form default submit behavior
        e.preventDefault();

        // ========== STEP 1: AMBIL DATA DARI FORM ==========

        const name = document.getElementById("name").value.trim();
        const password = document.getElementById("password").value;

        try {
            // Clear error message dari submit sebelumnya
            errorBox.classList.add("hidden");
            errorBox.innerText = "";

            console.log("🔐 Starting login process...");

            // ========== STEP 2: GET CSRF COOKIE DULU ==========

            console.log("📝 Step 1: Fetching CSRF cookie...");
            const csrfSuccess = await getCsrfCookie();

            if (!csrfSuccess) {
                throw new Error("Failed to initialize CSRF cookie");
            }

            // ========== STEP 3: POST LOGIN REQUEST ==========

            console.log("🔑 Step 2: Sending login credentials...");
            const response = await window.axios.post("/login", {
                name, // Username atau email
                password, // Password user
                device_name: "browser", // Identify token untuk browser
            });

            // Server respond dengan:
            // {
            //   "message": "Login successful",
            //   "user": {...},
            //   "token": "1|abc123xyz..."
            // }

            console.log("✅ Login response:", response.data);

            // ========== STEP 4: STORE TOKEN ==========

            const token = response.data.token;
            localStorage.setItem("api_token", token);

            // Setup Authorization header untuk request selanjutnya
            window.axios.defaults.headers.common["Authorization"] =
                `Bearer ${token}`;

            console.log("💾 Token saved to localStorage");
            console.log("🎉 Login successful! Redirecting...");

            // ========== STEP 5: REDIRECT KE DASHBOARD ==========

            window.location.href = "/dashboard";
        } catch (error) {
            // HANDLE ERROR

            console.error("❌ Login error:", error);
            console.error("Error response:", error.response?.data);
            console.error("Error status:", error.response?.status);

            errorBox.classList.remove("hidden");

            // Beri pesan error spesifik berdasarkan status code
            if (error.response && error.response.status === 429) {
                // 429: Too Many Requests (rate limit)
                errorBox.innerText =
                    "⏱️ Terlalu banyak percobaan login. Silakan tunggu 1 menit.";
            } else if (error.response?.status === 403) {
                // 403: Forbidden (CSRF token invalid)
                errorBox.innerText =
                    "🔒 CSRF token tidak valid. Silakan refresh page dan coba lagi.";
            } else if (error.response?.status === 401) {
                // 401: Unauthorized (credentials invalid)
                errorBox.innerText =
                    "❌ Email/nama atau password salah. Silakan coba lagi.";
            } else if (error.response?.status === 422) {
                // 422: Validation error (missing field)
                const firstError = Object.values(
                    error.response.data.errors || {},
                )[0]?.[0];
                errorBox.innerText =
                    firstError || "❌ Login gagal. Data tidak valid.";
            } else if (error.response?.data?.message) {
                // Generic error message dari server
                errorBox.innerText = "❌ " + error.response.data.message;
            } else if (error.message === "Network Error") {
                // Network error (server not running)
                errorBox.innerText =
                    "🌐 Koneksi ke server gagal. Pastikan server berjalan di http://localhost:8000";
            } else {
                // Unknown error
                errorBox.innerText =
                    "❌ Login gagal. Periksa email/nama dan password Anda.";
            }
        }
    });
}

// ============================================
// EXPORT UNTUK DIGUNAKAN DI FILE LAIN
// ============================================

export { getCsrfCookie };
```

---

## 🔄 Flow Diagram Dengan Code

```javascript
// FLOW 1: GET CSRF COOKIE
await window.axios.get("/sanctum/csrf-cookie", { baseURL: "/" });
// ↓
// Request: GET /sanctum/csrf-cookie
// Response: 204 No Content
// Headers: Set-Cookie: XSRF-TOKEN=xxx; laravel_session=yyy
// Storage: Browser simpan cookies otomatis
// ✅ Result: CSRF cookie tersimpan

// FLOW 2: LOGIN REQUEST
const response = await window.axios.post("/login", {
    name: "testuser",
    password: "password123",
    device_name: "browser",
});
// ↓
// Request: POST /api/login
// Body: {name, password, device_name}
// Headers:
//   - XSRF-TOKEN: xxx (dari cookie, dikirim otomatis)
//   - X-Requested-With: XMLHttpRequest
//   - Content-Type: application/json
// Cookies: laravel_session=yyy (dikirim otomatis)
// ✅ Result: Server terima CSRF cookie, verify, return token

// FLOW 3: STORE TOKEN
localStorage.setItem("api_token", response.data.token);
window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
// ↓
// localStorage: {api_token: "1|abc123xyz..."}
// Headers: Authorization: Bearer 1|abc123xyz...
// ✅ Result: Token siap untuk authenticated requests
```

---

## 🧪 Testing Dengan curl

### Test 1: Get CSRF Cookie

```bash
# Request
curl -c cookies.txt http://localhost:8000/sanctum/csrf-cookie -v

# Response:
# < HTTP/1.1 204 No Content
# < Set-Cookie: XSRF-TOKEN=xxx; Path=/
# < Set-Cookie: laravel_session=yyy; Path=/
# Cookies saved to: cookies.txt
```

### Test 2: Login With CSRF Cookie

```bash
# Request
curl -b cookies.txt -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d '{
    "name": "testuser",
    "password": "password123",
    "device_name": "browser"
  }'

# Response:
# {
#   "message": "Login successful",
#   "user": {
#     "id": "uuid...",
#     "name": "testuser",
#     "email": "test@test.com",
#     "created_at": "2026-07-14T10:00:00Z"
#   },
#   "token": "1|abc123xyz..."
# }
```

---

## 📊 Header Comparison

### SEBELUM (Salah - 419 Error)

```javascript
// Request ke /api/login
// Headers:
//   X-Requested-With: XMLHttpRequest
//   Content-Type: application/json
// Cookies: (EMPTY - tidak ada CSRF token)
// ❌ Result: 419 CSRF token mismatch
```

### SESUDAH (Benar - 200 OK)

```javascript
// Request ke /api/login
// Headers:
//   X-Requested-With: XMLHttpRequest
//   Content-Type: application/json
// Cookies:
//   XSRF-TOKEN: abc123xyz...
//   laravel_session: def456uvw...
// ✅ Result: 200 Login successful
```

---

## 🎯 Checklist: Apakah Setup Benar?

Untuk verify setup benar, buka DevTools (F12) dan cek:

### 1. Cek withCredentials

Console:

```javascript
window.axios.defaults.withCredentials;
// Harusnya return: true
```

### 2. Cek CSRF Cookie

DevTools → Application → Cookies:

```
Name: XSRF-TOKEN
Value: abc123xyz...
Domain: localhost
Path: /
```

### 3. Cek Authorization Header

Console saat sudah login:

```javascript
window.axios.defaults.headers.common["Authorization"];
// Harusnya return: "Bearer 1|abc123xyz..."
```

### 4. Cek localStorage Token

Console:

```javascript
localStorage.getItem("api_token");
// Harusnya return: "1|abc123xyz..."
```

---

## 🆘 Debugging Dengan Code

### Debug 1: Print request config

```javascript
window.axios.interceptors.request.use((config) => {
    console.log("📤 Request config:", config);
    return config;
});
```

### Debug 2: Print response data

```javascript
const response = await window.axios.get("/api/posts");
console.log("📥 Response data:", response);
console.log("Status:", response.status);
console.log("Headers:", response.headers);
```

### Debug 3: Print all cookies

```javascript
console.log(document.cookie);
// Output: XSRF-TOKEN=xxx; laravel_session=yyy
```

---

## 💾 Buat User Via tinker

```bash
php artisan tinker

# Di dalam tinker prompt:
>>> $user = App\Models\User::create([
    'name' => 'testuser',
    'email' => 'test@test.com',
    'password' => Hash::make('password123')
]);

>>> $user
# Harusnya return User object dengan semua field

>>> exit
```

---

## ✨ Setelah Setup Benar

Anda bisa:

```javascript
// 1. Login
const response = await axios.post('/api/login', {...});
const token = response.data.token;

// 2. Simpan token
localStorage.setItem('api_token', token);

// 3. Request authenticated
const posts = await axios.get('/api/posts');
// Token otomatis dikirim di header

// 4. Logout
await axios.post('/api/logout');
localStorage.removeItem('api_token');
```

---

**Semoga dokumentasi ini membantu! Happy coding! 🚀**
