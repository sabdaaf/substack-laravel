# 🔍 LOGIN DEBUGGING GUIDE - Data Mismatch Troubleshooting

Panduan lengkap untuk debug masalah login dengan script Tinker step-by-step.

---

## 📊 MASALAH YANG SUDAH DIIDENTIFIKASI & DIFIX

### ❌ Problem 1: Email Tidak Di-Trim Saat Register

**Masalah:** Email "bambang@gmail.com " (dengan spasi) tersimpan di database
**Solusi:** ✅ Update register.js untuk `.trim()` + `.toLowerCase()`

### ❌ Problem 2: Email Tidak Di-Trim Saat Login

**Masalah:** User input "bambang@gmail.com" (tanpa spasi) tapi database punya " bambang@gmail.com" (dengan spasi) → tidak cocok
**Solusi:** ✅ Update auth.js untuk `.trim()` + `.toLowerCase()`

### ❌ Problem 3: Request Tidak Trim

**Masalah:** Backend menerima raw data dari frontend tanpa trim
**Solusi:** ✅ Add `prepareForValidation()` di LoginRequest & RegisterRequest

### ❌ Problem 4: Email Case Mismatch

**Masalah:** "Bambang@gmail.com" vs "bambang@gmail.com" → tidak match
**Solusi:** ✅ Add `.toLowerCase()` di register.js, auth.js, dan request classes

---

## 🎯 FIXES YANG SUDAH DITERAPKAN

| File                                             | Fix                                              | Status     |
| ------------------------------------------------ | ------------------------------------------------ | ---------- |
| `resources/js/register.js`                       | `.trim()` + `.toLowerCase()` pada email          | ✅ Applied |
| `resources/js/auth.js`                           | `.trim()` + `.toLowerCase()` pada email          | ✅ Applied |
| `app/Http/Requests/Api/Auth/LoginRequest.php`    | `prepareForValidation()` dengan trim             | ✅ Applied |
| `app/Http/Requests/Api/Auth/RegisterRequest.php` | `prepareForValidation()` dengan trim + lowercase | ✅ Applied |

---

## 🧪 DEBUGGING DENGAN TINKER - STEP BY STEP

### Step 1: Check Existing Data in Database

Jalankan di terminal:

```bash
php artisan tinker
```

Lalu ketik perintah ini **satu per satu**:

```php
# ========== STEP 1.1: Lihat semua user ==========
>>> App\Models\User::all();
# Output: Collection of users
# Harusnya: Lihat list user dengan id, name, email, password_hash

# ========== STEP 1.2: Cek user spesifik ==========
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();
>>> $user;
# Output: User object atau null

# ========== STEP 1.3: Lihat data raw (tanpa casting) ==========
>>> $user = App\Models\User::first();
>>> $user->email;  # Lihat ada spasi di ujung?
>>> strlen($user->email);  # Lihat berapa karakter
# Contoh output:
# "bambang@gmail.com " (18 karakter - ada spasi!)
# atau
# "bambang@gmail.com" (17 karakter - tidak ada spasi)

# ========== STEP 1.4: Cek password di database ==========
>>> $user->password;
# Output: "$2y$12$abc123xyz..." (hashed password)
# Atau: "bambang123" (NOT hashed - PROBLEM!)
```

---

### Step 2: Test Password Verification Manual

```php
# ========== STEP 2.1: Get user ==========
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();

# ========== STEP 2.2: Test password dengan benar ==========
>>> Hash::check('bambang123', $user->password);
# Output: true (password cocok ✅) atau false (password tidak cocok ❌)

# ========== STEP 2.3: Test password dengan salah ==========
>>> Hash::check('wrongpassword', $user->password);
# Output: false (seharusnya - password tidak cocok)
```

---

### Step 3: Test Email Lookup (Exact Matching)

```php
# ========== STEP 3.1: Search dengan exact email ==========
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();
>>> $user;
# Jika return User object → email cocok ✅
# Jika return null → email tidak cocok ❌

# ========== STEP 3.2: Lihat raw email value ==========
>>> $user = App\Models\User::first();
>>> var_dump($user->email);
# Output: string(17) "bambang@gmail.com " (PERHATIKAN SPASI!)

# ========== STEP 3.3: Try dengan spasi ==========
>>> $user = App\Models\User::where('email', 'bambang@gmail.com ')->first();
# (note: spasi di akhir sebelum quote)
>>> $user;
# Jika return User object → email tersimpan DENGAN SPASI ❌

# ========== STEP 3.4: Check apakah spasi ada ==========
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();
>>> strlen($user->email);
# Jika output 18 → ada 1 spasi di akhir ❌
# Jika output 17 → tidak ada spasi ✅
```

---

### Step 4: Test Manual Login Flow

Simulasi proses login:

```php
# ========== STEP 4.1: Ambil email yang akan di-test ==========
>>> $email = 'bambang@gmail.com';  # Input dari user
>>> $password = 'bambang123';      # Input password user

# ========== STEP 4.2: Simulate LoginRequest prepareForValidation() ==========
# Backend akan trim & lowercase email
>>> $email_cleaned = trim(strtolower($email));
>>> $password_cleaned = $password;
>>> echo "Cleaned email: '$email_cleaned'";
# Output: Cleaned email: 'bambang@gmail.com'

# ========== STEP 4.3: Search user dengan cleaned email ==========
>>> $user = App\Models\User::where('email', $email_cleaned)->first();
>>> $user;
# Jika null → email tidak cocok dengan database ❌
# Jika User object → email cocok ✅

# ========== STEP 4.4: Verify password ==========
>>> Hash::check($password_cleaned, $user->password);
# Output: true atau false

# ========== STEP 4.5: Full auth flow ==========
# (Kombinasi dari step sebelumnya)
>>> $user = App\Models\User::where('email', trim(strtolower('bambang@gmail.com')))->first();
>>> if ($user && Hash::check('bambang123', $user->password)) {
>>>     echo "✅ AUTH SUCCESS - User dapat login";
>>> } else {
>>>     echo "❌ AUTH FAILED";
>>> }
```

---

### Step 5: Fix Data di Database (Jika Ada Spasi)

Jika Step 3.3 menemukan email tersimpan dengan spasi, fix:

```php
# ========== STEP 5.1: Update user dengan email ter-spasi ==========
>>> $user = App\Models\User::where('email', 'bambang@gmail.com ')->first();
# (note: spasi di akhir)

# ========== STEP 5.2: Trim & update ==========
>>> $user->update([
>>>     'name' => trim($user->name),
>>>     'email' => trim(strtolower($user->email))
>>> ]);

# ========== STEP 5.3: Verify update ==========
>>> $user->refresh();
>>> $user->email;
# Output: harusnya sekarang "bambang@gmail.com" (tanpa spasi)

# ========== STEP 5.4: Keluar tinker ==========
>>> exit
```

---

### Step 6: Fix Semua User Data Sekaligus

Jika banyak user dengan spasi:

```php
# ========== STEP 6.1: Lihat berapa user yang ter-spasi ==========
>>> DB::select("SELECT id, email, LENGTH(email) as len FROM users");
# Output: Lihat mana yang panjangnya aneh

# ========== STEP 6.2: Update semua user ==========
>>> User::query()->update([
>>>     'email' => DB::raw('LOWER(TRIM(email))')
>>> ]);
# Query ini akan:
# 1. TRIM() - remove spasi di awal/akhir
# 2. LOWER() - convert ke lowercase

# ========== STEP 6.3: Verify update ==========
>>> App\Models\User::all()->map(fn($u) => "$u->name: $u->email");
# Lihat semua email sekarang clean
```

---

## 📋 QUICK DEBUG CHECKLIST

Jalankan ini di Tinker untuk quick diagnosis:

```php
# 1. Check user exists
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();
>>> echo $user ? "✅ User found" : "❌ User not found";

# 2. Check email exact match
>>> echo strlen($user->email) == strlen('bambang@gmail.com') ? "✅ Email exact match" : "❌ Email length mismatch";

# 3. Check password is hashed
>>> echo str_starts_with($user->password, '$2y$') ? "✅ Password hashed" : "❌ Password not hashed";

# 4. Check password verification
>>> echo Hash::check('bambang123', $user->password) ? "✅ Password correct" : "❌ Password incorrect";

# 5. Full login simulation
>>> $email = 'bambang@gmail.com';
>>> $password = 'bambang123';
>>> $user = App\Models\User::where('email', trim(strtolower($email)))->first();
>>> echo ($user && Hash::check($password, $user->password)) ? "✅ LOGIN OK" : "❌ LOGIN FAIL";

# 6. Exit
>>> exit
```

---

## 🔧 FIX SUMMARY

Setelah semua fixes diterapkan, proses akan seperti ini:

### Registration Flow (SETELAH FIX)

```
1. User di /register
2. Input: name="bambang", email="Bambang@GMAIL.COM  " (dengan spasi & caps)
3. register.js trim() → "bambang", "bambang@gmail.com"
4. Backend RegisterRequest prepareForValidation() → trim + lowercase
5. Simpan ke DB: email="bambang@gmail.com" ✅ (clean)
6. Auto-login dengan email ter-trim & ter-lowercase
```

### Login Flow (SETELAH FIX)

```
1. User di /login
2. Input: name/email="Bambang@GMAIL.COM  " (autofill dengan spasi & caps)
3. auth.js trim() + toLowerCase() → "bambang@gmail.com"
4. POST /api/login
5. Backend LoginRequest prepareForValidation() → trim + lowercase
6. Query: WHERE email = 'bambang@gmail.com' → FOUND ✅
7. Hash::check('password', hashed_password) → true ✅
8. Token created + redirect to dashboard ✅
```

---

## ✅ TESTING SETELAH FIX

### Test 1: Fresh Registration

```bash
php artisan migrate:fresh --seed
# atau jika ingin keep existing:
# php artisan migrate
```

1. Go to `/register`
2. Input data (bisa dengan extra spaces/caps):
    - Name: " BamBang " (dengan spasi)
    - Email: "Bambang@GMAIL.COM " (dengan spasi & caps)
    - Password: "bambang123"
3. Check F12 Console:
    ```
    📋 Form data (after trim):
       Name: "BamBang"
       Email: "bambang@gmail.com"
    ```
4. Should auto-login & redirect to dashboard ✅

### Test 2: Login dengan Data Messy

1. Go to `/login`
2. Input (dari autofill dengan extra spaces):
    - Email: " Bambang@GMAIL.COM "
    - Password: "bambang123"
3. Check F12 Console:
    ```
    📋 Login form data (after trim):
       Email: "bambang@gmail.com"
       Name: "null"
    ```
4. Should login & redirect to dashboard ✅

---

## ❌ COMMON ERRORS & FIXES

| Error                        | Cause                         | Fix                                                                         |
| ---------------------------- | ----------------------------- | --------------------------------------------------------------------------- |
| "Password tidak cocok"       | Password tidak ter-hash di DB | Re-register atau update: `User::update(['password' => Hash::make('pass')])` |
| "Email/nama tidak ditemukan" | Email tersimpan dengan spasi  | Run: `User::query()->update(['email' => DB::raw('LOWER(TRIM(email))')])`    |
| Redirect tidak ke dashboard  | Token tidak tersimpan         | Check localStorage: `localStorage.getItem('api_token')`                     |
| CSRF error (419)             | Cookie issue                  | Clear cookies, restart servers                                              |

---

## 📝 FINAL VERIFICATION

Setelah semua fixes & testing, pastikan:

- [x] Email di-trim saat register (register.js)
- [x] Email di-lowercase saat register (register.js)
- [x] Email di-trim saat login (auth.js)
- [x] Email di-lowercase saat login (auth.js)
- [x] Backend trim email saat register (RegisterRequest)
- [x] Backend trim email saat login (LoginRequest)
- [x] Password ter-hash di database
- [x] Hash::check() return true untuk correct password
- [x] Can login dengan email
- [x] Can login dengan name
- [x] Redirect to dashboard after login
- [x] Token saved in localStorage

---

**Laporkan hasil testing dan output Tinker jika masih ada error!** 🔍
