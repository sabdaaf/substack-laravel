# ✅ LOGIN SANCTUM - SOLUSI FINAL RINGKAS

Solusi lengkap untuk login gagal di Laravel 12 + Sanctum + Axios.

---

## 🎯 Ringkasan Masalah & Solusi

### ❌ MASALAH:

```
1. Login gagal dengan error "Credentials are incorrect" atau "CSRF token mismatch"
2. Axios tidak mengirim CSRF cookie
3. Konfigurasi Sanctum tidak sesuai dengan localhost
```

### ✅ SOLUSI SUDAH DITERAPKAN:

| File                        | Perubahan                                                     |
| --------------------------- | ------------------------------------------------------------- |
| `.env`                      | ✅ `APP_URL=http://localhost:8000` + SANCTUM_STATEFUL_DOMAINS |
| `resources/js/auth.js`      | ✅ Tambah `getCsrfCookie()` + enable `withCredentials`        |
| `resources/js/bootstrap.js` | ✅ Setup global Axios dengan credentials                      |

---

## 🚀 LANGKAH LANGSUNG PRAKTIK

### STEP 1: Update .env (Jika Belum)

Buka `.env` dan pastikan ada:

```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
SESSION_DOMAIN=.localhost
```

### STEP 2: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### STEP 3: Verifikasi Database

```bash
# Option A: Check user ada?
php artisan tinker
>>> App\Models\User::all();
>>> exit

# Option B: Atau buat user baru
php artisan tinker
>>> App\Models\User::create([
    'name' => 'testuser',
    'email' => 'test@test.com',
    'password' => Hash::make('testpass123')
]);
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

1. Buka `http://localhost:8000/login`
2. Isi form:
    - Nama: testuser
    - Password: testpass123
3. Klik Login
4. Lihat console (F12):

**Expected:**

```
🔐 Starting login process...
📝 Step 1: Fetching CSRF cookie...
✅ CSRF cookie obtained successfully
🔑 Step 2: Sending login credentials...
✅ Login response: {...}
💾 Token saved to localStorage
🎉 Login successful! Redirecting...
```

---

## 📋 Verifikasi File

Pastikan file sudah diupdate dengan benar:

### ✅ auth.js

Harus ada:

```javascript
// Line 1-5:
window.axios.defaults.withCredentials = true;

// Somewhere:
async function getCsrfCookie() {
    await window.axios.get("/sanctum/csrf-cookie", {...});
}

// In form submit:
await getCsrfCookie();  // STEP 1
const response = await window.axios.post("/login", {...}); // STEP 2
localStorage.setItem("api_token", response.data.token); // STEP 3
```

### ✅ bootstrap.js

Harus ada:

```javascript
// Line 1-10 something:
window.axios.defaults.withCredentials = true;

const token = localStorage.getItem("api_token");
if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}
```

### ✅ .env

Harus ada:

```env
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1
```

---

## 🔍 Jika Masih Error

### Error 419 (CSRF Token Mismatch)

```
POST /api/login 419
{"message":"CSRF token mismatch."}
```

**Fix:**

```bash
# 1. Clear cache
php artisan optimize:clear

# 2. Restart server
# Ctrl+C di Terminal 1, jalankan lagi: php artisan serve

# 3. Clear browser cookies
# DevTools → Application → Cookies → Delete all
# Refresh page (F5) dan coba login lagi
```

---

### Error 401 (Credentials Invalid)

```
POST /api/login 401
{"message":"The provided credentials are incorrect."}
```

**Fix:**

1. Verify user ada di database:

    ```bash
    php artisan tinker
    >>> App\Models\User::where('name', 'testuser')->first();
    ```

    Harus return User object, bukan null

2. Test password:

    ```bash
    >>> $user = App\Models\User::first();
    >>> Hash::check('testpass123', $user->password);
    ```

    Harus return `true`

3. Gunakan password yang benar saat login

---

### Error "Network Error / Connection refused"

```
❌ Login failed. Koneksi ke server gagal.
```

**Fix:**

```bash
# 1. Check server running
ps aux | grep "php artisan serve"

# 2. Start server jika belum
php artisan serve

# 3. Test endpoint
curl http://localhost:8000/api/login
```

---

## 📚 Dokumentasi Lengkap

| File                        | Isi                                | Untuk Siapa           |
| --------------------------- | ---------------------------------- | --------------------- |
| **LOGIN_SUMMARY.md**        | Ringkasan solusi (quick reference) | Semua orang           |
| **LOGIN_QUICK_FIX.md**      | Setup cepat + common errors        | Yang ingin cepat      |
| **LOGIN_SANCTUM_GUIDE.md**  | Panduan lengkap (50+ halaman)      | Yang ingin detail     |
| **LOGIN_CODE_REFERENCE.md** | Kode lengkap + penjelasan          | Developer yang teliti |

---

## ✅ Final Checklist

Jika semua ini ✅, login harusnya berjalan:

- [ ] `.env` punya `APP_URL=http://localhost:8000`
- [ ] `.env` punya `SANCTUM_STATEFUL_DOMAINS=...localhost:8000...`
- [ ] Cache sudah di-clear: `php artisan optimize:clear`
- [ ] User ada di database
- [ ] Server running: `php artisan serve`
- [ ] Assets built: `npm run dev`
- [ ] `auth.js` punya `getCsrfCookie()` function
- [ ] `auth.js` punya `withCredentials = true`
- [ ] `bootstrap.js` punya `withCredentials = true`

---

## 🎯 Cara Kerja Login (Simplified)

```
User klik Login
    ↓
JavaScript: GET /sanctum/csrf-cookie
    ↓
Server: Return CSRF token di cookie
    ↓
Browser: Simpan cookie otomatis (karena withCredentials=true)
    ↓
JavaScript: POST /api/login dengan credentials + cookie
    ↓
Server: Verify CSRF token + credentials
    ↓
Server: Return token jika valid
    ↓
JavaScript: Simpan token ke localStorage
    ↓
Browser: Redirect ke /dashboard
```

---

## 💬 Pesan Error Yang Sering Muncul

| Error         | Penyebab                  | Fix                       |
| ------------- | ------------------------- | ------------------------- |
| 419 Mismatch  | CSRF cookie tidak dikirim | Clear cache + restart     |
| 401 Invalid   | Password salah            | Check database + password |
| Network Error | Server tidak running      | `php artisan serve`       |
| 403 Forbidden | Token expired             | Login ulang               |
| CORS Error    | Domain tidak match        | Check APP_URL di .env     |

---

## 🆘 Masih Tidak Jalan?

1. **Buka DevTools (F12)** dan cek console error
2. **Cek Network tab** → GET /sanctum/csrf-cookie status?
3. **Baca LOGIN_SANCTUM_GUIDE.md** untuk debugging mendalam
4. **Run:** `php artisan tinker` → `config('sanctum');` → lihat stateful domains

---

## 📞 Quick Commands

```bash
# Clear all cache
php artisan optimize:clear

# Start server
php artisan serve

# Build assets
npm run dev

# Check user
php artisan tinker
>>> App\Models\User::all();

# Create test user
>>> App\Models\User::create(['name'=>'test','email'=>'test@test.com','password'=>Hash::make('pass')]);
```

---

**🎉 Selesai! Login Sanctum sudah siap pakai!**

Jika ada pertanyaan, cek file dokumentasi yang lebih detail:

- Quick issues? → **LOGIN_QUICK_FIX.md**
- Need detail? → **LOGIN_SANCTUM_GUIDE.md**
- Show me code? → **LOGIN_CODE_REFERENCE.md**

**Semoga berhasil! 🚀**
