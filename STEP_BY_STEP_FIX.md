# 📖 Panduan Praktik Langkah-Langkah Solusi

Ikuti panduan ini secara berurutan untuk menyelesaikan kedua masalah.

---

## ✅ STEP 1: Dashboard Sekarang Terkunci (Sudah Dilakukan)

### File yang Sudah Diupdate

**`routes/web.php`** ← Sudah berubah

### Bukti Perubahan

Sebelum:

```php
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
```

Sesudah:

```php
// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
```

### Cara Test

1. **Buka browser** → `http://localhost:8000`
2. **Jangan login dulu**
3. **Klik menu "Dashboard"** di navbar
4. **Hasil yang diharapkan:** Redirect otomatis ke `/login` (jangan langsung ke dashboard)
5. **Setelah login** baru bisa akses dashboard

**✅ Selesai untuk Masalah #2!**

---

## ✅ STEP 2: Error Handling Axios Ditingkatkan (Sudah Dilakukan)

### File yang Sudah Diupdate

**`resources/views/index.blade.php`** ← Error catch block sudah diperbarui

### Kode Baru yang Ditambah

Di file `index.blade.php`, bagian `catch (error)` sekarang:

```javascript
} catch (error) {
    console.error('=== AXIOS ERROR DETAILS ===');
    console.error('Error Object:', error);
    console.error('Response Status:', error.response?.status);
    console.error('Response Data:', error.response?.data);
    console.error('Error Message:', error.message);
    console.error('Error Config:', error.config);
    console.error('=== END ERROR ===');

    let errorMsg = 'Gagal memuat artikel. Silakan coba lagi.';

    // Custom error messages based on status code
    if (error.response) {
        switch (error.response.status) {
            case 400:
                errorMsg = 'Request tidak valid...';
                break;
            case 404:
                errorMsg = 'Endpoint API tidak ditemukan...';
                break;
            case 500:
                errorMsg = 'Server error...';
                break;
            // ... dan seterusnya
        }
    }
    // ... error handling lengkap
}
```

### Cara Test Error Debugging

1. **Buka browser** → `http://localhost:8000`
2. **Tekan F12** untuk buka DevTools
3. **Pergi ke tab "Console"**
4. **Refresh halaman** (F5)
5. **Lihat di console output:**

Jika ada error, akan muncul:

```
=== AXIOS ERROR DETAILS ===
Error Object: AxiosError {...}
Response Status: 404
Response Data: {message: "..."}
Error Message: Request failed with status code 404
Error Config: {...}
=== END ERROR ===
```

Jika tidak ada error:

```
// Console akan kosong atau hanya ada log normal
```

**✅ Error debugging sudah ditingkatkan!**

---

## 🔧 STEP 3: Jika Masih Ada Error, Jalankan Perintah Berikut

### Masalah: Grid Artikel Kosong atau Error "no such table: posts"

**Solusi:** Reset database dan insert dummy data

#### Di Terminal, Jalankan:

```bash
php artisan migrate:fresh --seed
```

**Apa yang terjadi:**

1. ✅ Hapus semua table di database
2. ✅ Buat ulang table dari migration files
3. ✅ Insert dummy data (user & posts)

**Output yang diharapkan:**

```
Rolling back: 2026_01_10_063551_create_posts_table
Rolling back: 2026_01_10_062855_create_personal_access_tokens_table
...
Rolling back: 0001_01_01_000000_create_users_table
Dropped all tables successfully.

Migration: 0001_01_01_000000_create_users_table
Migration: 0001_01_01_000001_create_cache_table
...
Seeding: Database\Seeders\DatabaseSeeder
Database seeding completed successfully.
```

#### Setelah Seeding Selesai:

1. **Refresh browser** (F5 atau Ctrl+R)
2. **Homepage** seharusnya menampilkan artikel
3. **Tidak ada error** di console

**✅ Database sudah siap!**

---

## 🔧 STEP 4: Jika Masih Ada Error "Tidak ada respons dari server"

**Masalah:** API server tidak berjalan

**Solusi:** Start Laravel development server

#### Di Terminal Baru, Jalankan:

```bash
php artisan serve
```

**Output yang diharapkan:**

```
 INFO  Server running on [http://127.0.0.1:8000].

  Press Ctrl+C to stop the server
```

**Catatan:** Biarkan terminal ini tetap berjalan. Jangan tutup sampai Anda selesai testing.

#### Setelah Server Running:

1. **Refresh browser** (F5)
2. **Homepage** seharusnya load artikel
3. **Tidak ada connection error**

**✅ Server sudah berjalan!**

---

## 🔧 STEP 5: Jika Styling Tailwind Belum Muncul

**Masalah:** CSS styling tidak tampak, halaman terlihat plain text

**Solusi:** Build assets dengan Vite

#### Di Terminal Lain, Jalankan:

```bash
npm run dev
```

**Output yang diharapkan:**

```
> dev
> vite

  VITE v5.x.x  ready in xxx ms

  ➜  Local:   http://localhost:5173/
  ➜  press h + enter to show help
```

**Catatan:** Biarkan terminal ini tetap berjalan juga.

#### Setelah Vite Running:

1. **Refresh browser** (F5 atau Ctrl+Shift+Delete untuk clear cache)
2. **Styling** seharusnya muncul (Tailwind CSS)
3. **Layout** seharusnya terlihat rapi

**✅ Assets sudah built!**

---

## 📋 Checklist Sebelum Testing

Sebelum membuka browser, pastikan **3 terminal** sudah running:

| Terminal   | Perintah                           | Status      |
| ---------- | ---------------------------------- | ----------- |
| Terminal 1 | `php artisan serve`                | ✅ Running? |
| Terminal 2 | `npm run dev`                      | ✅ Running? |
| Database   | `php artisan migrate:fresh --seed` | ✅ Done?    |

Jika semua sudah, buka browser → `http://localhost:8000`

---

## 🧪 Testing Fitur

### Test 1: Homepage Terbuka & Artikel Tampil

```
Expected:
✅ Homepage terbuka
✅ Navbar terlihat dengan "Substack News"
✅ Grid artikel 3 kolom dengan kartu artikel
✅ Tidak ada error di console
```

### Test 2: Search Berfungsi

```
1. Ketik di search bar "error"
2. Expected:
   ✅ Hasil artikel di-filter
   ✅ Halaman otomatis ke page 1
   ✅ Tidak ada error di console
```

### Test 3: Filter Berfungsi

```
1. Klik tombol "Terlama"
2. Expected:
   ✅ Artikel di-sort dari yang paling lama
   ✅ Tombol berubah warna biru (active state)
   ✅ Tidak ada error di console
```

### Test 4: Pagination Berfungsi

```
1. Klik tombol "Selanjutnya →"
2. Expected:
   ✅ Halaman berubah ke page 2
   ✅ Info page terlihat "Halaman 2 dari 5" (misal)
   ✅ Halaman scroll ke atas otomatis
```

### Test 5: Klik Artikel Membuka Detail

```
1. Klik salah satu artikel card
2. Expected:
   ✅ Redirect ke /posts/{slug}
   ✅ Halaman detail artikel terbuka
   ✅ Judul, isi, penulis, tanggal terlihat
   ✅ Ada button "Kembali ke Beranda"
```

### Test 6: Dashboard Terkunci

```
1. Jangan login
2. Klik menu "Dashboard"
3. Expected:
   ✅ Redirect ke halaman /login
   ✅ Tidak bisa akses dashboard tanpa login
4. Login terlebih dahulu
5. Klik menu "Dashboard"
6. Expected:
   ✅ Bisa akses dashboard setelah login
```

---

## 🆘 Jika Masih Ada Error

### Step 1: Lihat Console Error Detail

1. Tekan F12 → Console tab
2. Cari output yang dimulai dengan `=== AXIOS ERROR DETAILS ===`
3. Catat error message & response status

### Step 2: Match dengan Error List

Lihat file `TROUBLESHOOTING.md` → bagian "Pesan Error yang Mungkin Anda Lihat"

### Step 3: Jalankan Solusi yang Sesuai

Contoh:

```
Jika error: "no such table: posts"
Solusi: php artisan migrate:fresh --seed

Jika error: "Tidak ada respons dari server"
Solusi: php artisan serve

Jika error: "Endpoint API tidak ditemukan"
Solusi: Cek routes/api.php ada route /posts
```

---

## ✨ Setelah Semua Working

Anda sudah punya:

- ✅ Homepage dengan grid artikel 3 kolom
- ✅ Search & filter berfungsi
- ✅ Pagination bekerja
- ✅ Detail artikel page
- ✅ Dashboard terkunci (auth required)
- ✅ Error handling yang detail & informative
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Tailwind CSS v4 styling
- ✅ Axios + Vanilla JS integration

**Selamat! 🎉 Proyek Anda sudah siap!**

---

## 📞 Command Reference Quick

```bash
# Database
php artisan migrate:fresh --seed      # Reset + seed
php artisan migrate                  # Hanya migrate
php artisan db:seed                  # Hanya seed
php artisan optimize:clear           # Clear semua cache

# Server
php artisan serve                    # Start dev server
npm run dev                          # Build assets

# Debugging
curl http://localhost:8000/api/posts  # Test API
php artisan route:list                # List semua routes
```

---

## 🎓 Pelajaran Penting

1. **Middleware Auth** → Lindungi route yang perlu login
2. **Error Handling** → Selalu log error detail untuk debugging
3. **DevTools Console** → Teman terbaik Anda untuk debugging JS
4. **Database Migration** → Pastikan schema selalu up-to-date
5. **Asset Building** → Vite harus berjalan untuk CSS/JS processing

---

**Jika masih ada pertanyaan, buka file `TROUBLESHOOTING.md` untuk info lebih lengkap!**

Semoga bermanfaat! 🚀
