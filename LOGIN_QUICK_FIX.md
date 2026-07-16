# 🔑 Login Sanctum - Quick Setup & Debug

Panduan cepat untuk setup login Sanctum dan troubleshoot error.

---

## ✅ Quick Setup Checklist

Jalankan ini **dalam urutan** untuk setup login Sanctum:

### 1. Update `.env`

```bash
# Edit .env dan pastikan:
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DOMAIN=.localhost
SESSION_DRIVER=database
```

Atau buka file `.env` langsung di editor dan paste konfigurasi di atas.

### 2. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

Jika tidak jalan, gunakan:

```bash
php artisan optimize:clear --force
```

### 3. Buat/Verifikasi User di Database

**Option A: Via Tinker (Interactive)**

```bash
php artisan tinker
# Ketik di prompt:
>>> App\Models\User::all();
# Lihat daftar user
```

**Option B: Via Seeder**

```bash
php artisan migrate:fresh --seed
# Database di-reset + insert dummy users
```

**Option C: Create User Manual**

```bash
php artisan tinker
>>> App\Models\User::create(['name'=>'testuser','email'=>'test@test.com','password'=>Hash::make('password123')]);
```

### 4. Start Servers

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

### 5. Test Login

1. Buka `http://localhost:8000/login`
2. Isi form:
    - **Nama/Email:** nama user atau email
    - **Password:** password yang benar
3. Klik **Login**
4. Lihat console (F12) untuk debug

---

## 🧪 Testing Login - 3 Metode

### Metode 1: Via Browser (Termudah)

1. Buka `http://localhost:8000/login`
2. Tekan **F12** → **Console** tab
3. Isi form login dengan data yang benar
4. Klik tombol Login
5. Lihat console output

**Expected Output di Console:**

```
🔐 Starting login process...
📝 Step 1: Fetching CSRF cookie...
✅ CSRF cookie obtained successfully
🔑 Step 2: Sending login credentials...
✅ Login response: {...}
💾 Token saved to localStorage
🎉 Login successful! Redirecting...
```

### Metode 2: Via curl Command

Terminal:

```bash
# Step 1: Get CSRF Cookie
curl -c cookies.txt http://localhost:8000/sanctum/csrf-cookie -v

# Step 2: Login (replace nama_user & password)
curl -b cookies.txt -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"name":"nama_user","password":"password123","device_name":"browser"}'
```

**Expected Response:**

```json
{
    "message": "Login successful",
    "user": {
        "id": "uuid...",
        "name": "nama_user",
        "email": "user@example.com",
        "created_at": "2026-07-14T10:00:00Z"
    },
    "token": "1|abc123xyz..."
}
```

### Metode 3: Via Postman/Thunder Client

1. **Request 1 - Get CSRF Cookie:**
    - Method: `GET`
    - URL: `http://localhost:8000/sanctum/csrf-cookie`
    - Klik Send
    - Status: `204 No Content` ✅

2. **Request 2 - Login:**
    - Method: `POST`
    - URL: `http://localhost:8000/api/login`
    - Header: `Content-Type: application/json`
    - Body (JSON):
        ```json
        {
            "name": "nama_user",
            "password": "password123",
            "device_name": "browser"
        }
        ```
    - Klik Send
    - Status: `200 OK` ✅
    - Response: User data + token

---

## ❌ Common Login Errors & Quick Fixes

### Error 1: "CSRF token mismatch" (419)

**Error Response:**

```
POST /api/login 419
{"message":"CSRF token mismatch."}
```

**Penyebab:** GET /sanctum/csrf-cookie tidak dijalankan

**Fix:**

```javascript
// Di auth.js, pastikan ada:
await getCsrfCookie(); // Ini HARUS dipanggil duluan

// Pastikan juga:
window.axios.defaults.withCredentials = true;
```

**Atau:** Clear browser cookies:

```
DevTools → Application → Cookies → Delete XSRF-TOKEN
Refresh page dan coba login lagi
```

---

### Error 2: "The provided credentials are incorrect" (401)

**Error Response:**

```
POST /api/login 401
{"message":"The provided credentials are incorrect."}
```

**Penyebab:** Email/nama atau password salah

**Fix:**

1. Verify user ada di database:

    ```bash
    php artisan tinker
    >>> App\Models\User::where('name', 'testuser')->first();
    # Harusnya return User object
    ```

2. Test password:

    ```bash
    php artisan tinker
    >>> $user = App\Models\User::first();
    >>> Hash::check('password123', $user->password);
    # Harusnya return: true
    ```

3. Buat user baru kalau tidak ada:
    ```bash
    php artisan tinker
    >>> App\Models\User::create(['name'=>'testuser','email'=>'test@test.com','password'=>Hash::make('pass123')]);
    ```

---

### Error 3: "Network Error / Connection refused"

**Error Message di Console:**

```
❌ Login failed. Koneksi ke server gagal.
Network Error
```

**Penyebab:** Server tidak running

**Fix:**

```bash
# Terminal 1
php artisan serve

# Terminal 2 (cek port)
curl http://localhost:8000/

# Jika port 8000 sudah digunakan:
php artisan serve --port=8001
```

---

### Error 4: CORS Error

**Error di Console:**

```
Access to XMLHttpRequest blocked by CORS policy
```

**Penyebab:** APP_URL tidak benar atau CORS tidak configured

**Fix:**

```bash
# Update .env
APP_URL=http://localhost:8000

# Clear cache
php artisan config:clear
```

---

### Error 5: "Failed to get CSRF cookie"

**Error Message di Console:**

```
❌ Failed to get CSRF cookie
```

**Penyebab:** GET /sanctum/csrf-cookie error

**Debug:**

```bash
# Test endpoint
curl http://localhost:8000/sanctum/csrf-cookie -v

# Harus return 204 No Content
```

---

## 🔍 Debug Checklist

Jika login masih gagal, cek ini **satu per satu**:

- [ ] **Server Running?**

    ```bash
    ps aux | grep "php artisan serve"
    # Atau check terminal 1
    ```

- [ ] **.env Correct?**

    ```bash
    grep APP_URL .env
    # Harusnya: APP_URL=http://localhost:8000
    ```

- [ ] **User Exists?**

    ```bash
    php artisan tinker
    >>> App\Models\User::all();
    ```

- [ ] **Password Correct?**
    - Verifikasi password saat registrasi
    - Test di Tinker: `Hash::check('pass', $user->password)`

- [ ] **auth.js Updated?**
    - Check `resources/js/auth.js` ada `getCsrfCookie()`
    - Check `withCredentials = true`

- [ ] **bootstrap.js Updated?**
    - Check `resources/js/bootstrap.js` ada `withCredentials = true`

- [ ] **CSRF Cookie Exist?**
    - DevTools → Application → Cookies
    - Cari `XSRF-TOKEN` atau `laravel_session`

- [ ] **Network OK?**
    - DevTools → Network tab
    - GET /sanctum/csrf-cookie → Status 204 ✅
    - POST /api/login → Status 200 ✅

- [ ] **Cache Cleared?**
    ```bash
    php artisan optimize:clear
    ```

---

## 🚀 Flow Login Yang Benar

```
1. User fill form
   ↓
2. Click Login button
   ↓
3. JS trigger getCsrfCookie()
   GET /sanctum/csrf-cookie
   ↓ Response: 204 No Content
   ↓ Browser simpan XSRF-TOKEN & laravel_session cookie
   ↓
4. JS trigger POST /api/login
   Headers: Content-Type: application/json
   Cookies: XSRF-TOKEN, laravel_session (auto dikirim)
   Body: {name, password, device_name}
   ↓
5. Server validate
   - Check CSRF token ✅
   - Check user credentials ✅
   - Return token ✅
   ↓ Response: 200 OK
   ↓ Data: {message, user, token}
   ↓
6. JS save token to localStorage
   localStorage.setItem('api_token', token)
   ↓
7. JS redirect ke dashboard
   window.location.href = '/dashboard'
```

---

## 📱 Key Configuration Points

| Config                     | Value                                                   | Why                                         |
| -------------------------- | ------------------------------------------------------- | ------------------------------------------- |
| `APP_URL`                  | `http://localhost:8000`                                 | Server URL must include port                |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1` | Trusted domains for cookies                 |
| `SESSION_DRIVER`           | `database`                                              | Session stored in DB (required for Sanctum) |
| `withCredentials`          | `true`                                                  | Enable cookies in requests                  |
| `device_name`              | `browser`                                               | Identify token device                       |

---

## 📊 Database Check

```bash
# Connect to database
php artisan tinker

# Check users table
>>> App\Models\User::all();

# Check specific user
>>> App\Models\User::where('name', 'testuser')->first();

# Check password
>>> $user = App\Models\User::first();
>>> Hash::check('password123', $user->password);  # return true/false

# Create new user
>>> App\Models\User::create([
    'name' => 'newuser',
    'email' => 'new@test.com',
    'password' => Hash::make('password123')
]);

# Exit tinker
>>> exit
```

---

## 💾 Token Management

### Store Token

```javascript
// After successful login
localStorage.setItem("api_token", response.data.token);
```

### Retrieve Token

```javascript
// For subsequent requests
const token = localStorage.getItem("api_token");
axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
```

### Clear Token

```javascript
// On logout
localStorage.removeItem("api_token");
```

---

## 🎯 Endpoints Summary

| Endpoint               | Method | Purpose          | Requires CSRF |
| ---------------------- | ------ | ---------------- | ------------- |
| `/sanctum/csrf-cookie` | GET    | Get CSRF token   | No            |
| `/api/login`           | POST   | Login user       | Yes (CSRF)    |
| `/api/register`        | POST   | Register user    | Yes (CSRF)    |
| `/api/logout`          | POST   | Logout user      | Yes (Token)   |
| `/api/me`              | GET    | Get current user | Yes (Token)   |

---

## 🆘 Still Not Working?

1. **Check browser console** (F12):

    ```
    Look for: 🔐 Starting login process...
    If not there: JavaScript not loaded
    ```

2. **Check Network tab** (F12 → Network):

    ```
    GET /sanctum/csrf-cookie → Status?
    POST /api/login → Status & Response?
    ```

3. **Check .env matches server URL**:

    ```bash
    APP_URL must match the URL you opened in browser
    ```

4. **Clear everything & restart**:

    ```bash
    php artisan optimize:clear
    # Close all terminals
    # Restart: php artisan serve + npm run dev
    ```

5. **Read full guide**: Open `LOGIN_SANCTUM_GUIDE.md` untuk debugging mendalam

---

**Need help? Check:**

- `LOGIN_SANCTUM_GUIDE.md` - Panduan lengkap
- `TROUBLESHOOTING.md` - Common issues
- Console error message (F12)
- Network tab requests (F12 → Network)

**Happy debugging! 🚀**
