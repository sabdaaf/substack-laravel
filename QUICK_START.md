# 🚀 Quick Start Guide - Homepage Substack News

## ⚡ Setup Cepat (5 Menit)

### 1. Pastikan Database Sudah Ada Data

```bash
# Terminal di root project
php artisan migrate:fresh --seed
```

Jika ingin membuat artikel dummy lebih banyak, buka `database/seeders/DatabaseSeeder.php` dan update:

```php
\App\Models\Post::factory(50)->create(); // Membuat 50 artikel dummy
```

Lalu run ulang seeder:

```bash
php artisan db:seed
```

### 2. Jalankan Development Server

```bash
php artisan serve
```

Atau gunakan Valet/Herd jika sudah setup.

### 3. Build Frontend Assets

Di terminal baru:

```bash
npm run dev
```

### 4. Buka di Browser

```
http://localhost:8000
```

✅ **Done!** Homepage sudah berjalan.

---

## 📋 Checklist Setup

- [ ] Database migrated (`php artisan migrate`)
- [ ] Ada data posts di database
- [ ] `php artisan serve` running
- [ ] `npm run dev` running (untuk Vite)
- [ ] Buka `http://localhost:8000`
- [ ] Homepage tampil dengan artikel
- [ ] Filter & search bekerja
- [ ] Klik artikel → halaman detail

---

## 🧪 Testing API Endpoint

Gunakan Postman atau Thunder Client:

### Get Posts

```
GET http://localhost:8000/api/posts?per_page=10&sort_by=created_at&order=desc
```

Expected Response:

```json
{
    "current_page": 1,
    "data": [
        {
            "id": "uuid",
            "title": "Judul Artikel",
            "slug": "judul-artikel",
            "body": "Isi artikel...",
            "author_id": "uuid",
            "created_at": "2026-07-14T10:00:00Z",
            "updated_at": "2026-07-14T10:00:00Z",
            "author": {
                "id": "uuid",
                "name": "Nama Penulis",
                "email": "penulis@example.com"
            }
        }
    ],
    "last_page": 5,
    "total": 47
}
```

### Get Single Post Detail

```
GET http://localhost:8000/api/posts/judul-artikel
```

---

## 📸 Preview Halaman

### Homepage

```
┌─────────────────────────────────────┐
│  Substack News      [Beranda] [Dashboard] │
├─────────────────────────────────────┤
│                                     │
│        Substack News                │
│   Portal berita terpercaya dengan   │
│  artikel berkualitas dari penulis   │
│                                     │
├─────────────────────────────────────┤
│  [Cari artikel.....] [Terbaru] [Terlama] [A-Z] │
├─────────────────────────────────────┤
│                                     │
│  ┌─────────┬─────────┬─────────┐   │
│  │ Artikel │ Artikel │ Artikel │   │
│  ├─────────┼─────────┼─────────┤   │
│  │ Artikel │ Artikel │ Artikel │   │
│  ├─────────┼─────────┼─────────┤   │
│  │ Artikel │ Artikel │ Artikel │   │
│  └─────────┴─────────┴─────────┘   │
│                                     │
│  [← Sebelumnya] Hal 1 dari 5 [Selanjutnya →] │
│                                     │
└─────────────────────────────────────┘
```

### Detail Artikel

```
┌─────────────────────────────────────┐
│  Substack News      [Beranda] [Dashboard] │
├─────────────────────────────────────┤
│  [← Kembali ke Beranda]             │
│                                     │
│  Judul Artikel Yang Sangat Panjang  │
│  Bisa Menjadi Dua Baris Atau Lebih  │
│                                     │
│  Nama Penulis                       │
│  email@example.com                  │
│  14 Jul 2026 pukul 10:00            │
│                                     │
├─────────────────────────────────────┤
│                                     │
│  Isi artikel dengan formatting yang │
│  sudah ada di database...           │
│                                     │
│                                     │
├─────────────────────────────────────┤
│                                     │
│  Tentang Penulis                    │
│  Nama Penulis                       │
│  email@example.com                  │
│                                     │
│  Artikel Lainnya                    │
│  ┌──────────────┐ ┌──────────────┐ │
│  │ Artikel Lain │ │ Artikel Lain │ │
│  └──────────────┘ └──────────────┘ │
│                                     │
└─────────────────────────────────────┘
```

---

## 🎨 Styling Customization

### Mengubah Warna Tema

Edit `tailwind.config.js` (jika ada):

```javascript
module.exports = {
    theme: {
        extend: {
            colors: {
                primary: "#0066cc", // Ubah warna utama
            },
        },
    },
};
```

Lalu replace semua `blue-600` dengan `primary` di file view.

### Mengubah Font

Tambah ke `resources/css/app.css`:

```css
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap");

@layer base {
    body {
        @apply font-sans;
    }
}
```

Dan di `tailwind.config.js`:

```javascript
fontFamily: {
  sans: ['Inter', 'sans-serif'],
}
```

### Mengubah Layout Grid

Cari di `index.blade.php`:

```html
<!-- Ubah dari 3 kolom jadi 4 kolom -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"></div>
```

---

## 🔧 Troubleshooting Tips

| Masalah            | Solusi                                                                   |
| ------------------ | ------------------------------------------------------------------------ |
| Posts tidak muncul | Buka DevTools → Console, cek error Axios. Pastikan API endpoint bekerja. |
| Styling jelek      | Run `npm run dev` dan clear cache browser (Ctrl+Shift+Delete).           |
| Link artikel 404   | Pastikan slug di URL sesuai dengan database.                             |
| Search tidak jalan | Cek query parameter di Network tab DevTools.                             |
| Pagination missing | API harus return pagination data (Laravel paginate() sudah default).     |

---

## 📚 File References

**Files yang di-edit/dibuat:**

1. `resources/views/index.blade.php` ← **Homepage**
2. `resources/views/posts/show.blade.php` ← **Detail Article**
3. `routes/web.php` ← **2 route baru**
4. `HOMEPAGE_SETUP.md` ← **Dokumentasi Lengkap**
5. `QUICK_START.md` ← **File ini**

**API yang digunakan:**

- `GET /api/posts` (dari `routes/api.php`)
- `GET /api/posts/{slug}` (dari `routes/api.php`)

---

## ✨ Extra Features

Halaman sudah include:

- ✅ Responsive design (mobile-first)
- ✅ Loading spinner
- ✅ Error messages
- ✅ Debounced search
- ✅ Active filter states
- ✅ Time formatting ("2 jam lalu", etc)
- ✅ Related articles
- ✅ HTML escaping (XSS prevention)
- ✅ Smooth scrolling
- ✅ Pagination

---

## 🆘 Need Help?

### Check Console Error

```javascript
// Buka DevTools (F12) → Console tab
// Lihat error message jika ada
```

### Test API Manually

```bash
# Terminal
curl "http://localhost:8000/api/posts"
```

### Debug JavaScript

```javascript
// Di DevTools Console:
console.log("Current sort:", currentSort);
console.log("Current page:", currentPage);
console.log("Current search:", currentSearch);
```

---

## 🎓 Learning Resources

Jika ingin belajar lebih dalam:

- **Blade Template**: https://laravel.com/docs/11/views
- **Axios**: https://axios-http.com/
- **Tailwind CSS v4**: https://tailwindcss.com/
- **Laravel Routing**: https://laravel.com/docs/11/routing

---

**Happy coding! 🚀**

Jika ada pertanyaan atau masalah, cek file `HOMEPAGE_SETUP.md` untuk dokumentasi yang lebih lengkap.
