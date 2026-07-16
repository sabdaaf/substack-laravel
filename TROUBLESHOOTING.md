# 🔧 Troubleshooting Guide - Masalah Umum dan Solusi

## 📋 Daftar Cepat Solusi

| Masalah                            | Solusi Cepat                                                      |
| ---------------------------------- | ----------------------------------------------------------------- |
| Axios error "Gagal memuat artikel" | Cek DevTools Console, jalankan `php artisan migrate:fresh --seed` |
| Dashboard bisa diakses tanpa login | ✅ SUDAH DIPERBAIKI - middleware auth sudah ditambah              |
| 404 pada endpoint `/api/posts`     | Pastikan `npm run dev` berjalan, cek file `routes/api.php`        |
| Database kosong/tidak ada artikel  | Jalankan `php artisan db:seed`                                    |
| Styling Tailwind tidak muncul      | Clear cache browser (Ctrl+Shift+Delete), restart `npm run dev`    |

---

## 🛠️ SOLUSI A: Dashboard Sekarang Terkunci dengan Auth

### Apa yang Sudah Diperbaiki

File `routes/web.php` sudah diupdate:

```php
// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
```

### Cara Kerjanya

1. **Middleware `'auth'`** memeriksa apakah user sudah login
2. Jika **sudah login** → halaman dashboard ditampilkan
3. Jika **belum login** → otomatis redirect ke halaman `/login`

### Test Cara Kerjanya

1. Buka browser → `http://localhost:8000`
2. Klik menu **"Dashboard"** tanpa login
3. Harusnya redirect ke `/login` (bukan ke dashboard)
4. Login terlebih dahulu
5. Setelah login, baru bisa akses dashboard

---

## 🛠️ SOLUSI B: Debug Error Axios Lebih Detail

### Error Handling Sudah Ditingkatkan

Kode Axios di `index.blade.php` sudah diupdate dengan:

- ✅ Logging error detail ke console
- ✅ Deteksi HTTP status code
- ✅ Pesan error custom berdasarkan status code
- ✅ Error handling untuk berbagai skenario

### Cara Debug Error Axios

#### Langkah 1: Buka DevTools

```
Windows/Linux: Tekan F12
Mac: Cmd + Option + I
```

#### Langkah 2: Pergi ke Tab "Console"

#### Langkah 3: Klik Menu "Dashboard" atau refresh halaman

#### Langkah 4: Lihat Console untuk Error Detail

Anda akan lihat output seperti ini:

```
=== AXIOS ERROR DETAILS ===
Error Object: AxiosError {...}
Response Status: 500
Response Data: {message: "SQLSTATE[HY000]: General error: 1 no such table: posts"}
Error Message: Request failed with status code 500
Error Config: {...}
=== END ERROR ===
```

### Pesan Error yang Mungkin Anda Lihat

| Pesan Error                                                                     | Artinya                            | Solusi                                                          |
| ------------------------------------------------------------------------------- | ---------------------------------- | --------------------------------------------------------------- |
| **"Tidak ada respons dari server. Pastikan API server berjalan di port 8000."** | API server tidak berjalan          | Jalankan `php artisan serve`                                    |
| **"Endpoint API tidak ditemukan. Pastikan server berjalan."**                   | Route `/api/posts` tidak ditemukan | Cek `routes/api.php` sudah ada route                            |
| **"Server error"** (status 500)                                                 | Database error atau kode bug       | Lihat error detail di console                                   |
| **"no such table: posts"**                                                      | Table posts tidak ada di database  | Jalankan `php artisan migrate`                                  |
| **"Call to undefined function"**                                                | Function di controller tidak ada   | Cek controller di `app/Http/Controllers/Api/PostController.php` |
| **"Terlalu banyak request"** (status 429)                                       | Rate limit tercapai                | Tunggu beberapa saat sebelum request lagi                       |

---

## ⚡ PERINTAH ARTISAN YANG SERING DIBUTUHKAN

### Jika Error: Database Kosong / Tidak Ada Table

#### Opsi 1: Reset Total + Seed Data (Recommended untuk Development)

```bash
php artisan migrate:fresh --seed
```

**Apa yang terjadi:**

- ✅ Drop semua table
- ✅ Re-create semua table dari migrations
- ✅ Insert dummy data (dari seeders)
- ⏱️ Waktu: ~5-10 detik

#### Opsi 2: Hanya Run Migration (jika belum pernah)

```bash
php artisan migrate
```

**Apa yang terjadi:**

- ✅ Membuat table sesuai migration files
- ⚠️ Tidak ada data, grid akan kosong

#### Opsi 3: Hanya Insert Dummy Data

```bash
php artisan db:seed
```

**Apa yang terjadi:**

- ✅ Jalankan seeder untuk insert data
- ⚠️ Hanya jika table sudah ada

#### Opsi 4: Buat Banyak Artikel Dummy (untuk testing)

1. Buka file `database/seeders/DatabaseSeeder.php`
2. Cari baris:
    ```php
    \App\Models\Post::factory(10)->create();
    ```
3. Ubah angka `10` jadi lebih besar, misal `100`:
    ```php
    \App\Models\Post::factory(100)->create();
    ```
4. Jalankan:
    ```bash
    php artisan migrate:fresh --seed
    ```

---

## 🔍 Tahap Debugging Step-by-Step

### Tahap 1: Cek Apakah Server Berjalan

Terminal:

```bash
curl http://localhost:8000/
```

Hasil yang bagus:

```
<!DOCTYPE html>
<html>
<head>...
```

Hasil yang jelek:

```
curl: (7) Failed to connect to localhost port 8000
```

**Solusi:** Jalankan `php artisan serve` jika belum

---

### Tahap 2: Test API Endpoint Langsung

Terminal:

```bash
curl http://localhost:8000/api/posts
```

Hasil yang bagus:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": "...",
      "title": "Judul Artikel",
      "slug": "judul-artikel",
      ...
    }
  ],
  "total": 10
}
```

Hasil yang jelek:

```json
{
    "message": "SQLSTATE[HY000]: General error: 1 no such table: posts"
}
```

**Solusi:** Jalankan `php artisan migrate:fresh --seed`

---

### Tahap 3: Test dari Browser Console

Buka DevTools → Console, ketik:

```javascript
// Test Axios request
axios
    .get("/api/posts?per_page=5")
    .then((res) => {
        console.log("Success! Data:", res.data);
    })
    .catch((err) => {
        console.log("Error:", err.response?.status, err.response?.data);
    });
```

Klik Enter dan lihat hasil di console.

---

### Tahap 4: Cek File-file Penting Ada atau Tidak

Pastikan file berikut ada:

- ✅ `routes/api.php` - berisi route API
- ✅ `routes/web.php` - berisi route web (sudah diupdate)
- ✅ `app/Http/Controllers/Api/PostController.php` - controller API
- ✅ `app/Models/Post.php` - model Post
- ✅ `resources/views/index.blade.php` - homepage
- ✅ `resources/views/posts/show.blade.php` - detail article
- ✅ `database/migrations/` - folder dengan migration files

---

## 🔑 Daftar Lengkap Artisan Commands

### Untuk Database

```bash
# Reset + migrate + seed (MOST COMMON)
php artisan migrate:fresh --seed

# Hanya migrate
php artisan migrate

# Rollback migration terakhir
php artisan migrate:rollback

# Rollback semua migration
php artisan migrate:reset

# Hanya seed (insert data)
php artisan db:seed

# Rollback dan migrate ulang
php artisan migrate:refresh

# Migrate fresh + seed + dengan verbose output
php artisan migrate:fresh --seed -v
```

### Untuk Cache & Optimization

```bash
# Clear aplikasi cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear semua cache (recommended jika ada masalah aneh)
php artisan optimize:clear
```

### Untuk Development

```bash
# Jalankan dev server
php artisan serve

# Jalankan dengan port custom
php artisan serve --port=3000

# List semua routes
php artisan route:list

# List routes dengan detail
php artisan route:list -v
```

---

## 📊 Checklist Debugging

Jika masih error, cek satu per satu:

- [ ] **Server berjalan** → `php artisan serve` running di Terminal 1
- [ ] **Assets built** → `npm run dev` running di Terminal 2
- [ ] **Database migrated** → `php artisan migrate:fresh --seed` sudah dijalankan
- [ ] **API endpoint working** → Test dengan `curl http://localhost:8000/api/posts`
- [ ] **Homepage terbuka** → `http://localhost:8000` bisa diakses
- [ ] **Error detail di console** → Buka DevTools (F12) → Console → lihat error
- [ ] **Network tab cek request** → DevTools → Network → refresh page → cek request ke `/api/posts`
- [ ] **Cache cleared** → Jalankan `php artisan optimize:clear` kalau perlu

---

## 🆘 Masalah Umum & Solusi Cepat

### "Gagal memuat artikel" muncul di homepage

**Kemungkinan 1: Database kosong**

```bash
php artisan migrate:fresh --seed
```

**Kemungkinan 2: API error (lihat console)**

```bash
# Cek apakah endpoint bekerja
curl http://localhost:8000/api/posts
```

**Kemungkinan 3: Server tidak jalan**

```bash
php artisan serve
```

---

### Dashboard bisa diakses tanpa login

**Status: ✅ SUDAH DIPERBAIKI**

Sekarang route `/dashboard` sudah punya middleware `'auth'`:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
```

Jika masih bisa diakses, clear cache:

```bash
php artisan route:clear
```

---

### Styling Tailwind tidak muncul

**Solusi:**

```bash
# Terminal 1: Restart dev server
php artisan serve

# Terminal 2: Restart Vite
npm run dev

# Browser: Clear cache
Ctrl + Shift + Delete → Clear cache → Reload F5
```

---

### "axios is not defined" error

**Sebab:** Script Axios tidak ter-load

**Solusi:** Pastikan di file `resources/views/index.blade.php` ada:

```html
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
```

---

## 📝 Contoh Error Response dari Berbagai Masalah

### Error 1: Database Kosong

```json
{
    "message": "SQLSTATE[HY000]: General error: 1 no such table: posts"
}
```

→ Jalankan: `php artisan migrate:fresh --seed`

### Error 2: Route Tidak Ada

```
{
  "message": "Route [api.posts.index] not defined."
}
```

→ Cek: `routes/api.php` harus ada route `/posts`

### Error 3: Server Tidak Berjalan

```
No response / Connection refused
```

→ Jalankan: `php artisan serve`

### Error 4: Rate Limited

```json
{
    "message": "Too Many Requests"
}
```

→ Tunggu beberapa menit atau ubah throttle limit di `config/app.php`

---

## 🎯 Flow Debugging Otomatis

Setiap kali error terjadi sekarang, console akan menampilkan:

```javascript
console.log("=== AXIOS ERROR DETAILS ===");
console.log("Error Object:", error); // Object lengkap
console.log("Response Status:", status); // HTTP status code
console.log("Response Data:", data); // Server response body
console.log("Error Message:", message); // Error message
console.log("Error Config:", config); // Request config
```

**Gunakan informasi ini untuk diagnose masalah!**

---

## 💡 Tips Pro

### Tip 1: Gunakan Network Tab

```
DevTools → Network tab → Refresh page → Klik request ke /api/posts
```

Lihat:

- Status code (200 = OK, 404 = not found, 500 = server error)
- Response body (lihat data yang dikembalikan)
- Request headers (lihat apa yang dikirim)

### Tip 2: Monitor Real-time

```bash
# Terminal 1: Laravel logs (real-time)
tail -f storage/logs/laravel.log

# Terminal 2: Lihat saat ada error di console
```

### Tip 3: Test dengan Postman/Thunder Client

Lebih mudah test API tanpa perlu membuka browser devtools setiap kali.

---

## ✅ Setelah Semua Diperbaiki

Anda seharusnya lihat:

- ✅ Homepage terbuka dengan artikel grid
- ✅ Search & filter berfungsi
- ✅ Pagination bekerja
- ✅ Klik artikel → buka halaman detail
- ✅ Klik Dashboard tanpa login → redirect ke login
- ✅ Tidak ada error di console

**Selamat! 🎉**
