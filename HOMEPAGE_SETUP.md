# Dokumentasi Homepage Substack News

## 📋 Daftar File yang Dibuat

### 1. **resources/views/index.blade.php**

File utama homepage dengan struktur lengkap:

- Navbar dengan logo "Substack News" dan menu navigasi
- Hero section dengan judul dan tagline
- Search bar dan filter buttons (Terbaru, Terlama, Judul A-Z)
- Grid artikel 3 kolom (responsive)
- Pagination untuk navigasi halaman
- Vanilla JavaScript dengan Axios untuk fetch data API

### 2. **resources/views/posts/show.blade.php**

Halaman detail artikel dengan:

- Navigation kembali ke beranda
- Tampilan full artikel dengan body lengkap
- Informasi penulis dan tanggal publikasi
- Section "Artikel Lainnya" yang dimuat via Axios

### 3. **routes/web.php** (Updated)

Penambahan route:

```php
Route::get('/', function () {
    return view('index');
});

Route::get('/posts/{post:slug}', function (\App\Models\Post $post) {
    return view('posts.show', compact('post'));
})->name('posts.show');
```

---

## 🚀 Cara Menjalankan

### 1. **Setup Database (Jika Belum)**

```bash
php artisan migrate:fresh --seed
```

Atau jika hanya ingin reset dan seed:

```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

### 2. **Jalankan Development Server**

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

### 3. **Build Assets (jika menggunakan Vite)**

Di terminal terpisah:

```bash
npm run dev
```

Untuk production build:

```bash
npm run build
```

### 4. **Akses Halaman**

- Homepage: `http://localhost:8000/`
- Detail artikel: `http://localhost:8000/posts/{slug}`

---

## 🎨 Fitur & Struktur

### Navbar

- Logo/Brand name "Substack News" yang bisa diklik kembali ke home
- Menu "Beranda" dan "Dashboard" di kanan
- Sticky di atas saat scroll

### Hero Section

- Judul besar "Substack News"
- Tagline: "Portal berita terpercaya dengan artikel berkualitas dari penulis profesional"
- Background gradient subtle (white to gray-50)

### Search & Filter

- **Search Input**: "Cari artikel..." dengan debounce 300ms
- **Filter Buttons**:
    - Terbaru (sort by created_at DESC) - Default
    - Terlama (sort by created_at ASC)
    - Judul A-Z (sort by title ASC)
- Button yang aktif ditandai dengan background biru

### Grid Artikel

- **Responsive**: 1 kolom mobile, 2 kolom tablet, 3 kolom desktop
- **Setiap Card Menampilkan**:
    - Badge waktu (misal: "2 jam lalu", "3 hari lalu", dll)
    - Judul artikel (tebal, maksimal 2 baris)
    - Excerpt artikel (150 karakter pertama, maksimal 3 baris)
    - Nama penulis dan tanggal publikasi
    - Hover effect: shadow meningkat dan link aktif
    - Seluruh card adalah link ke `/posts/{slug}`

### Pagination

- Tombol "← Sebelumnya" dan "Selanjutnya →"
- Info halaman: "Halaman X dari Y"
- Auto scroll ke atas saat pindah halaman

### Data dari API

Fetch dari `GET /api/posts` dengan parameters:

- `page`: nomor halaman
- `per_page`: 9 artikel per halaman
- `sort_by`: field sorting (created_at atau title)
- `order`: asc atau desc
- `search`: keyword pencarian (opsional)

Response API sudah include relasi `author` dengan fields: id, name, email

---

## ⚙️ Kustomisasi

### Mengubah Jumlah Artikel per Halaman

Buka `resources/views/index.blade.php`, cari:

```javascript
const POSTS_PER_PAGE = 9;
```

Ubah angka sesuai kebutuhan.

### Mengubah Nama Website

Cari semua text "Substack News" di file:

- `resources/views/index.blade.php`
- `resources/views/posts/show.blade.php`

Replace dengan nama website Anda.

### Mengubah Warna Tema

Tailwind CSS v4 menggunakan CSS variables. Warna utama saat ini adalah blue-600 untuk accent.
Untuk mengubah warna, update class utility Tailwind atau modifikasi `tailwind.config.js`.

### Menambah Kolom Grid

Di `resources/views/index.blade.php`, cari:

```html
<div
    id="postsContainer"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
></div>
```

Ubah `lg:grid-cols-3` menjadi jumlah kolom yang diinginkan.

### Mengubah Excerpt Length

Di file `index.blade.php`, cari:

```javascript
const excerpt =
    post.body
        .substring(0, 150)
        .replace(/<[^>]*>/g, "")
        .trim() + "...";
```

Ubah angka `150` ke jumlah karakter yang diinginkan.

---

## 📱 Responsive Design

Halaman sudah fully responsive dengan breakpoints Tailwind:

- **Mobile** (< 768px): 1 kolom, navbar simplified
- **Tablet** (768px - 1024px): 2 kolom, navbar lengkap
- **Desktop** (> 1024px): 3 kolom, layout optimal

---

## ✨ Fitur Khusus

### Time Ago Format

Artikel menampilkan waktu relatif:

- "baru saja" (< 1 menit)
- "X menit lalu"
- "X jam lalu"
- "X hari lalu"
- "X minggu lalu"
- Format tanggal jika > 4 minggu

### Debounced Search

Search input memiliki debounce 300ms untuk menghindari terlalu banyak request ke API saat user mengetik.

### Error Handling

Menampilkan pesan error jika API fail atau tidak ada artikel ditemukan.

### Loading State

Loading spinner dan opacity effect pada grid saat data sedang dimuat.

---

## 🔗 Integrasi dengan API Existing

Halaman ini sudah fully integrated dengan API yang ada di `routes/api.php`:

```php
// GET /api/posts - Menampilkan list posts
Route::get('/api/posts', [PostController::class, 'index']);

// GET /api/posts/{post:slug} - Menampilkan detail post (untuk halaman show)
Route::get('/api/posts/{post:slug}', [PostController::class, 'show']);
```

API sudah support:

- Pagination
- Sorting (by created_at, title, etc)
- Search (by title atau body)
- Author relationship

---

## 📦 Dependencies

File sudah include:

- **Tailwind CSS v4** (via Vite)
- **Axios** (CDN dari jsDelivr)
- **Blade Template** (Laravel native)

Setup awal Tailwind CSS v4 sudah seharusnya ada di project karena file `resources/css/app.css` sudah ada.

---

## 🐛 Troubleshooting

### Posts tidak muncul?

1. Pastikan database sudah dimigrasi dan ada data posts
2. Buka DevTools Console untuk melihat error dari Axios
3. Pastikan API endpoint `/api/posts` berjalan (test di Postman)

### Styling tidak tampak?

1. Pastikan sudah run `npm run dev` (untuk Vite)
2. Clear browser cache (Ctrl+Shift+Delete)
3. Periksa bahwa `@vite` directive ada di head tag

### Link artikel 404?

1. Pastikan route `Route::get('/posts/{post:slug}', ...)` sudah ditambah di web.php
2. Pastikan slug di database valid (tidak ada special characters yang problematic)

### Axios error CORS?

1. Pastikan API di `routes/api.php` sudah enable CORS (biasanya Laravel 11+ sudah default)
2. Jika perlu, install package: `composer require laravel/cors`
3. Jalankan: `php artisan config:publish cors`

---

## 📄 File Structure Akhir

```
resources/
├── views/
│   ├── index.blade.php          ← Homepage (BARU)
│   ├── posts/
│   │   └── show.blade.php       ← Detail article (BARU)
│   ├── auth/
│   ├── dashboard.blade.php
│   └── welcome.blade.php
├── css/
│   └── app.css                  (Tailwind CSS v4)
└── js/
    └── app.js                   (Vite entry point)

routes/
├── web.php                      ← UPDATED dengan 2 route baru
├── api.php                      (Existing, tidak perlu ubah)
└── console.php
```

---

## 🎉 Selesai!

Halaman homepage Anda sudah siap! Desainnya:

- ✅ Minimalis dengan background putih/abu-abu terang
- ✅ Navbar sticky dengan brand name dan menu
- ✅ Hero section dengan tagline
- ✅ Search & filter yang functional
- ✅ Grid artikel 3 kolom responsive
- ✅ Card artikel dengan semua info (time, title, excerpt, author, date)
- ✅ Pagination
- ✅ Fully integrated dengan API existing
- ✅ Vanilla JS + Axios (tanpa React/Vue)
- ✅ Tailwind CSS v4
- ✅ Blade template

Selamat menggunakan! 🚀
