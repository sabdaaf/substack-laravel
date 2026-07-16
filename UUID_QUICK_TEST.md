# ⚡ UUID FIX - QUICK TEST (3 MENIT)

Panduan cepat untuk test login setelah UUID fix diterapkan.

---

## 🚀 SETUP (1 MENIT)

```bash
# Terminal: Clear cache
php artisan optimize:clear
php artisan config:clear

# Restart servers jika sudah running sebelumnya
# Ctrl+C di Terminal 1 & 2, lalu jalankan:

# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

---

## 🧪 TEST (2 MENIT)

### Test 1: Login dengan Email ✅

1. Buka: `http://localhost:8000/login`

2. Input:

    ```
    Email atau Nama: caniago@test.com
    Password: (password user caniago)
    ```

3. Tekan **F12** → **Console** tab

4. Klik **Login**

5. Lihat console log:

    ```
    📝 Step 1: Fetching CSRF cookie...
    ✅ CSRF cookie obtained successfully
    🔑 Step 2: Sending login credentials...
    Email: caniago@test.com
    ✅ Login response: {...}
    💾 Token saved to localStorage
    🎉 Login successful! Redirecting...
    ```

6. **Browser should redirect ke `/dashboard`** ✅

---

### Test 2: Login dengan Name ✅

1. Logout: Buka DevTools → localStorage → hapus `api_token`

2. Atau buka incognito window baru

3. Buka: `http://localhost:8000/login`

4. Input:

    ```
    Email atau Nama: caniago
    Password: (password user caniago)
    ```

5. Klik **Login**

6. **Browser should redirect ke `/dashboard`** ✅

---

### Test 3: Verify Token di localStorage

Di DevTools Console:

```javascript
localStorage.getItem("api_token");
// Output: "1|abc123xyz..."
// (bukan null, bukan undefined)
```

---

## ✅ JIKA BERHASIL

- ✅ Redirect ke `/dashboard` setelah login
- ✅ Token di localStorage
- ✅ Console log menunjukkan semua step sukses
- ✅ Bisa login dengan email ATAU name

**Selesai! UUID login fix working! 🎉**

---

## ❌ JIKA MASIH ERROR

### Error 1: "Email atau nama dan password tidak sesuai"

**Check database:**

```bash
php artisan tinker
>>> App\Models\User::where('email', 'caniago@test.com')->first();
# Harusnya return user object, bukan null
>>> exit
```

**Fix:** Pastikan user ada di database atau buat user test:

```bash
php artisan tinker
>>> App\Models\User::create([
    'name' => 'testuser',
    'email' => 'test@test.com',
    'password' => Hash::make('test123456')
]);
>>> exit
```

---

### Error 2: "CSRF token tidak valid" (419)

**Fix:**

```bash
php artisan optimize:clear
# Restart servers (Ctrl+C, jalankan lagi)
# Browser: F12 → Application → Clear site data
# Try login lagi
```

---

### Error 3: Console log tidak keluar atau page tidak redirect

**Check:** Apakah server benar-benar running?

```bash
# Terminal 1 harus menunjukkan:
# INFO  Server running on [http://127.0.0.1:8000]

# Terminal 2 harus menunjukkan:
# VITE v5.x.x ready in xxx ms
```

---

### Error 4: Token tidak tersimpan (localStorage kosong)

**Debug di console:**

```javascript
// Check jika error terjadi saat POST
// F12 → Network tab → cari request POST /api/login
// Check response status: 200 OK?
// Check response body: ada token field?
```

---

## 📋 Troubleshooting Checklist

- [ ] `php artisan optimize:clear` sudah dijalankan
- [ ] Servers sudah di-restart
- [ ] Database sudah ter-migrate: `php artisan migrate`
- [ ] User ada di database (check via tinker)
- [ ] F12 Console jangan ada error
- [ ] Network tab: POST /api/login → 200 OK
- [ ] localStorage ada api_token
- [ ] Bisa redirect ke dashboard

---

## 🎯 EXPECTED BEHAVIOR

**Setelah fix diterapkan:**

- ✅ User.php punya UUID settings
- ✅ AuthController login logic optimized
- ✅ auth.js support email + name
- ✅ Form label jelas: "Email atau Nama Lengkap"

**Result:** Login bekerja dengan UUID tanpa error! 🚀

---

**Siap? Ikuti step-step di atas dan harusnya OK dalam 3 menit! ⚡**

Jika masih ada issue, baca dokumentasi lengkap di `UUID_LOGIN_FIX.md`
