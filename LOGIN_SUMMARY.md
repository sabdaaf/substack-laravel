# 🔐 LOGIN SANCTUM FIX - RINGKASAN SOLUSI

Ringkasan lengkap solusi login gagal di Laravel 12 + Sanctum + Axios.

---

## 📋 Yang Sudah Diperbaiki

### 1. File `.env` ✅

```env
# SEBELUM (salah):
APP_URL=http://localhost

# SESUDAH (benar):
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DOMAIN=.localhost
```

### 2. File `resources/js/auth.js` ✅

**Perubahan Utama:**

#### ✅ Aktifkan `withCredentials`

```javascript
window.axios.defaults.withCredentials = true;
```

Fungsi: Agar cookie CSRF otomatis dikirim di setiap request

#### ✅ Tambah `getCsrfCookie()` Function

```javascript
async function getCsrfCookie() {
    try {
        await window.axios.get("/sanctum/csrf-cookie", {
            baseURL: "/",
        });
        console.log("✅ CSRF cookie obtained successfully");
        return true;
    } catch (error) {
        console.error("❌ Failed to get CSRF cookie:", error);
        return false;
    }
}
```

Fungsi: Ambil CSRF token dari Sanctum sebelum login

#### ✅ Update Flow Login (STEP 1 → STEP 2 → STEP 3)

```javascript
// STEP 1: Get CSRF cookie dulu
const csrfSuccess = await getCsrfCookie();

// STEP 2: Baru login dengan credentials
const response = await window.axios.post("/login", {
    name,
    password,
    device_name: "browser",
});

// STEP 3: Save token & redirect
localStorage.setItem("api_token", response.data.token);
window.location.href = "/dashboard";
```

#### ✅ Better Error Handling

```javascript
if (error.response?.status === 403) {
    errorBox.innerText = "🔒 CSRF token tidak valid. Refresh & coba lagi.";
} else if (error.response?.status === 401) {
    errorBox.innerText = "❌ Email/nama atau password salah.";
}
```

### 3. File `resources/js/bootstrap.js` ✅

```javascript
// Setup global Axios untuk seluruh app
window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Load token dari localStorage jika ada
const token = localStorage.getItem("api_token");
if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}
```

---

## 🚀 Cara Menggunakan Solusi

### STEP 1: Pastikan .env Sudah Benar

Buka file `.env` dan cek:

```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DRIVER=database
```

### STEP 2: Clear Cache

Terminal:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### STEP 3: Setup Database (Jika belum ada user)

```bash
# Reset database + seed
php artisan migrate:fresh --seed

# Atau jika database udah ada, buat user:
php artisan tinker
>>> App\Models\User::create(['name'=>'testuser','email'=>'test@test.com','password'=>Hash::make('pass123')]);
>>> exit
```

### STEP 4: Start Servers

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

### STEP 5: Test Login

1. Buka browser: `http://localhost:8000/login`
2. Isi form:
    - **Nama/Email:** testuser (atau nama user di database)
    - **Password:** pass123 (atau password yang benar)
3. Klik **Login**
4. Tekan **F12** → **Console** untuk lihat debug output

**Expected Console Output:**

```
🔐 Starting login process...
📝 Step 1: Fetching CSRF cookie...
✅ CSRF cookie obtained successfully
🔑 Step 2: Sending login credentials...
✅ Login response: {message: "Login successful", ...}
💾 Token saved to localStorage
🎉 Login successful! Redirecting...
```

---

## ✅ Flowchart Login yang Benar

```
┌─────────────────────────────┐
│  User Klik Login Button     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ auth.js: getCsrfCookie()   │
│ GET /sanctum/csrf-cookie   │
│ Response: 204 No Content   │
│ Status: ✅ CSRF cookie OK  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ auth.js: POST /api/login   │
│ Body: {name, password,...} │
│ Headers: XSRF-TOKEN cookie │
│ Response: {token, user}    │
│ Status: ✅ Login Success   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Save token to localStorage │
│ Redirect ke /dashboard     │
│ Status: ✅ All Done!       │
└─────────────────────────────┘
```

---

## 🔍 Debugging Singkat

### Jika Error 419 (CSRF token mismatch)

**Penyebab:** `getCsrfCookie()` tidak dipanggil

**Cek:**

```javascript
// Di console (F12):
localStorage.getItem("api_token");
// Harusnya null sebelum login

// Cek cookies:
// DevTools → Application → Cookies → Cari XSRF-TOKEN
```

**Fix:**

```bash
php artisan config:clear
# Restart browser
# Test login lagi
```

---

### Jika Error 401 (Credentials Invalid)

**Penyebab:** Username/password salah

**Cek:**

```bash
php artisan tinker
>>> App\Models\User::all();  # Lihat user yang ada
>>> $user = App\Models\User::first();
>>> Hash::check('password_yang_ditest', $user->password);
```

**Fix:** Gunakan password yang benar saat registrasi

---

### Jika Network Error (Connection Refused)

**Penyebab:** Server tidak running

**Cek:**

```bash
ps aux | grep "php artisan serve"
# Atau lihat Terminal 1
```

**Fix:**

```bash
php artisan serve
```

---

## 📋 Verifikasi Setup Akhir

Checklist final sebelum coba login:

- [ ] File `.env` sudah update (APP_URL, SANCTUM_STATEFUL_DOMAINS)
- [ ] File `auth.js` sudah punya `getCsrfCookie()` function
- [ ] File `bootstrap.js` sudah punya `withCredentials = true`
- [ ] Cache sudah clear: `php artisan optimize:clear`
- [ ] Database sudah di-migrate: `php artisan migrate`
- [ ] User sudah ada di database
- [ ] Server running: `php artisan serve`
- [ ] Assets built: `npm run dev`

Jika semua sudah ✅, buka `http://localhost:8000/login` dan coba login.

---

## 🆘 Masalah Masih Berlanjut?

1. **Baca full guide**: Open file `LOGIN_SANCTUM_GUIDE.md`
2. **Check console error**: Tekan F12 → Console tab
3. **Check network requests**: F12 → Network tab
4. **Run debug command**:
    ```bash
    php artisan tinker
    >>> config('sanctum');
    ```
5. **Clear everything & restart**:
    ```bash
    php artisan optimize:clear
    # Restart terminal 1 & 2
    ```

---

## 📁 File yang Sudah Diperbaiki

| File                        | Status   | Fungsi                  |
| --------------------------- | -------- | ----------------------- |
| `.env`                      | ✅ Fixed | Konfigurasi environment |
| `resources/js/auth.js`      | ✅ Fixed | CSRF + Login flow       |
| `resources/js/bootstrap.js` | ✅ Fixed | Global Axios setup      |

---

## 📚 Dokumentasi Tersedia

| File                     | Isi                                          |
| ------------------------ | -------------------------------------------- |
| `LOGIN_SANCTUM_GUIDE.md` | **Panduan lengkap** (50+ halaman)            |
| `LOGIN_QUICK_FIX.md`     | **Quick setup** (halaman ini, lebih ringkas) |
| `TROUBLESHOOTING.md`     | **Debug umum** (homepage)                    |

---

## 🎯 Key Takeaways

1. **CSRF Cookie HARUS diambil dulu** sebelum login
2. **`withCredentials = true` HARUS diaktifkan** agar cookie ikut dikirim
3. **APP_URL di .env HARUS tepat** dengan port server
4. **SANCTUM_STATEFUL_DOMAINS HARUS include localhost**
5. **Session driver HARUS database** untuk Sanctum

---

## ✨ Expected Result Setelah Fixed

✅ User bisa registrasi tanpa error
✅ User bisa login dengan credentials yang benar
✅ Token disimpan di localStorage
✅ Redirect ke dashboard berhasil
✅ Dashboard terkunci (auth required)
✅ Console tidak ada error

**Selamat! Login Sanctum sudah siap! 🚀**

---

**Quick Commands:**

```bash
php artisan config:clear && php artisan serve
npm run dev
php artisan tinker
```

**Questions? Check:**

- Console error message (F12)
- Network requests (F12 → Network)
- `LOGIN_SANCTUM_GUIDE.md` (full documentation)
