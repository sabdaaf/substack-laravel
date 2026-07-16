# ⚡ QUICK START - Test Register & Login Sekarang

Panduan cepat untuk test register & login langsung (5 menit).

---

## 🚀 SETUP CEPAT (5 MENIT)

### Step 1: Clear Cache & Reset DB (1 menit)

Terminal:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
```

Expected output:

```
Rolling back: ...
Dropped all tables successfully.
Migration: 0001_01_01_000000_create_users_table
Migration: ...
Database seeding completed successfully.
```

### Step 2: Start Servers (30 detik)

**Terminal 1:**

```bash
php artisan serve
```

Expected: `INFO  Server running on [http://127.0.0.1:8000]`

**Terminal 2 (Baru):**

```bash
npm run dev
```

Expected: `VITE v5.x.x ready in xxx ms`

### Step 3: Test Register (3 menit)

1. Buka browser: `http://localhost:8000/register`

2. Isi form:
    - **Nama Lengkap:** testuser
    - **Email:** test@test.com
    - **Password:** testpass123
    - **Konfirmasi Password:** testpass123

3. Tekan **F12** → **Console** tab

4. Klik **Daftar** button

5. Lihat console output:

    **Expected (Success):**

    ```
    📝 Starting registration process...
    Step 1: Fetching CSRF cookie...
    ✅ CSRF cookie obtained successfully
    Step 2: Sending registration data...
    ✅ Registration successful: {...}
    Step 3: Sending login credentials...
    ✅ Login successful: {...}
    💾 Token saved to localStorage
    🎉 Registration & login complete! Redirecting...
    ```

    **Then:** Browser redirect ke `/dashboard` ✅

---

## 🧪 Verify Success

### Test 1: Check Browser

- [ ] Redirected ke dashboard? ✅
- [ ] URL changed to `/dashboard`? ✅
- [ ] Page content loaded? ✅

### Test 2: Check localStorage

Di DevTools Console (F12):

```javascript
localStorage.getItem("api_token");
// Harusnya return: "1|abc123xyz..." (bukan null)
```

### Test 3: Check Database

Terminal:

```bash
php artisan tinker
>>> App\Models\User::where('email', 'test@test.com')->first();
# Harusnya return User object dengan password ter-hash
>>> exit
```

---

## ❌ Jika Error

### Error 1: "CSRF token mismatch" (419)

```
❌ CSRF token tidak valid. Silakan refresh page.
```

**Fix:**

```bash
# Terminal: Clear cache & restart
php artisan optimize:clear

# Ctrl+C di terminal 1, jalankan:
php artisan serve

# Browser: Refresh F5 & try lagi
```

---

### Error 2: "Email sudah terdaftar"

```
❌ email sudah terdaftar
```

**Fix:** Gunakan email berbeda, atau clear users:

```bash
php artisan tinker
>>> DB::table('users')->truncate();
>>> exit
# Coba register ulang dengan email baru
```

---

### Error 3: "Koneksi ke server gagal"

```
🌐 Koneksi ke server gagal. Pastikan server berjalan.
```

**Fix:**

```bash
# Check Terminal 1 masih running php artisan serve?
# Jika tidak, jalankan lagi:
php artisan serve
```

---

### Error 4: Network Error

```
❌ Login gagal. Periksa email/nama dan password Anda.
```

**Debug:**

1. Check DevTools Network tab (F12)
2. Cek request ke `/sanctum/csrf-cookie` → Status 204?
3. Cek request ke `/api/register` → Status 201?
4. Cek request ke `/api/login` → Status 200?

---

## 📋 Troubleshooting

| Error                 | Solusi                                                 |
| --------------------- | ------------------------------------------------------ |
| 419 CSRF Mismatch     | `php artisan optimize:clear` + Restart + Clear cookies |
| Email sudah terdaftar | Ganti email atau `DB::table('users')->truncate();`     |
| Database kosong       | `php artisan migrate:fresh --seed`                     |
| Server tidak jalan    | `php artisan serve` di Terminal 1                      |
| Assets tidak load     | `npm run dev` di Terminal 2                            |

---

## ✅ Jika Berhasil

Anda sudah punya:

- ✅ User berhasil ter-register
- ✅ User berhasil ter-login
- ✅ Token tersimpan di localStorage
- ✅ Redirect ke dashboard berhasil
- ✅ User ada di database SQLite

**Next step:** Test login dengan user yang berbeda, atau test logout & login ulang.

---

## 📚 Dokumentasi Lengkap

Jika masih ada issue, baca:

- **DEBUG_DATABASE_BACKEND.md** - Debugging database step-by-step
- **FIX_SUMMARY.md** - Ringkasan masalah & solusi
- **LOGIN_FINAL.md** - Login flow & troubleshooting

---

## 💻 Commands Quick Reference

```bash
# Clear everything
php artisan optimize:clear

# Reset database
php artisan migrate:fresh --seed

# Start server
php artisan serve

# Build assets
npm run dev

# Check users
php artisan tinker
>>> App\Models\User::all();
>>> exit

# Check specific user
php artisan tinker
>>> App\Models\User::where('email', 'test@test.com')->first();
>>> exit

# Clear users table
php artisan tinker
>>> DB::table('users')->truncate();
>>> exit
```

---

**Siap test sekarang? Ikuti 3 langkah di atas dan harusnya berjalan! 🚀**

Jika masih error, lihat bagian "❌ Jika Error" untuk solusi cepat.
