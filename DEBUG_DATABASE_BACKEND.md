# 🔍 DEBUG BACKEND & DATABASE - PANDUAN LENGKAP

Panduan sistematis untuk diagnosa masalah login/register di database dan backend.

---

## 📋 Checklist Debugging Urut

Ikuti checklist ini **dalam urutan** sampai masalah ditemukan:

- [ ] **1. Database ter-migrate?** → Check users table ada
- [ ] **2. User tersimpan?** → Check user di database via tinker
- [ ] **3. Password ter-hash?** → Verify password hashing
- [ ] **4. CSRF cookie dipanggil?** → Check register.js
- [ ] **5. Login logic benar?** → Verify backend AuthController
- [ ] **6. Test end-to-end** → Coba register + login dari browser

---

## 🔍 STEP 1: Cek Database Ter-Migrate

### Command 1: Check Users Table Exists

Terminal:

```bash
php artisan tinker
```

Di prompt tinker:

```php
>>> Schema::hasTable('users')
# Output: true atau false
# Harusnya: true (jika belum true, lanjut ke "Reset Database")

>>> exit
```

### Command 2: List All Users di Database

Terminal:

```bash
php artisan tinker
```

Di prompt tinker:

```php
>>> App\Models\User::all()
# Atau
>>> App\Models\User::all()->toArray()
# Output akan menampilkan array user
# Harusnya: Minimal 1-2 user, atau empty array []

>>> exit
```

### Jika Users Table Tidak Ada:

Reset database:

```bash
php artisan migrate:fresh --seed
```

**Output yang diharapkan:**

```
Rolling back: 2026_01_10_063551_create_posts_table
Rolling back: 2026_01_10_062855_create_personal_access_tokens_table
...
Migration table created successfully
Rolled back all migrations

Migrating: 0001_01_01_000000_create_users_table
Migrated:  0001_01_01_000000_create_users_table
...
Database seeding completed successfully.
```

---

## 🔍 STEP 2: Cek User Tersimpan di Database

### Scenario A: Cari User Berdasarkan Email

Terminal:

```bash
php artisan tinker
```

```php
# Ganti 'test@test.com' dengan email yang Anda gunakan saat register
>>> $user = App\Models\User::where('email', 'test@test.com')->first();
>>> $user;
# Output:
# Jika ada:
# App\Models\User {
#   id: "uuid...",
#   name: "testuser",
#   email: "test@test.com",
#   password: "$2y$12$...", (hashed)
#   created_at: "2026-07-14 10:00:00",
#   ...
# }
#
# Jika tidak ada:
# null

>>> exit
```

### Scenario B: Cari User Berdasarkan Name

Terminal:

```bash
php artisan tinker
```

```php
# Ganti 'testuser' dengan name yang Anda gunakan saat register
>>> $user = App\Models\User::where('name', 'testuser')->first();
>>> $user;
# Sama seperti scenario A

>>> exit
```

### Scenario C: Cek Semua User (List Lengkap)

Terminal:

```bash
php artisan tinker
```

```php
# Lihat semua user
>>> App\Models\User::all()
# Output: Collection of users

# Atau dengan format lebih rapi
>>> App\Models\User::all()->toArray()
# Output: Array of users

# Atau hitung jumlah user
>>> App\Models\User::count()
# Output: Jumlah user (int)

>>> exit
```

### Jika User Tidak Ditemukan:

1. **Pastikan database sudah di-migrate:**

    ```bash
    php artisan migrate
    ```

2. **Buat user test secara manual:**

    ```bash
    php artisan tinker
    >>> App\Models\User::create([
        'name' => 'testuser',
        'email' => 'test@test.com',
        'password' => Hash::make('testpass123')
    ]);
    >>> exit
    ```

3. **Atau reset + seed:**
    ```bash
    php artisan migrate:fresh --seed
    ```

---

## 🔍 STEP 3: Verifikasi Password Hashing

### Check 1: Password Sudah Di-Hash?

Terminal:

```bash
php artisan tinker
```

```php
# Get user
>>> $user = App\Models\User::where('email', 'test@test.com')->first();

# Lihat password di database
>>> $user->password
# Output akan seperti: $2y$12$abc123xyz...
# Ini berarti password SUDAH di-hash ✅

# JIKA output berupa plain text password (bukan $2y$...), itu BERMASALAH ❌

>>> exit
```

### Check 2: Password Correct?

Terminal:

```bash
php artisan tinker
```

```php
# Get user
>>> $user = App\Models\User::where('email', 'test@test.com')->first();

# Test password yang benar
>>> Hash::check('testpass123', $user->password)
# Output: true ✅

# Test password yang salah
>>> Hash::check('wrongpass', $user->password)
# Output: false ❌

>>> exit
```

### Jika Password Tidak Ter-hash:

Kemungkinan besar user dibuat tanpa `Hash::make()`.

**Fix:** Buat ulang user dengan hash:

```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'test@test.com')->first();
>>> $user->update(['password' => Hash::make('testpass123')]);
>>> $user->password
# Seharusnya sekarang $2y$...
>>> exit
```

Atau reset + seed:

```bash
php artisan migrate:fresh --seed
```

---

## 🔍 STEP 4: Verifikasi Backend Register Function

### File: `app/Http/Controllers/Api/AuthController.php`

**Register function yang benar:**

```php
public function register(RegisterRequest $request): JsonResponse
{
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),  // ✅ Hash::make() ada
    ]);

    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
        'token' => $token,
    ], 201);
}
```

**Status Anda:** ✅ BENAR - Password di-hash dengan `Hash::make()`

---

## 🔍 STEP 5: Verifikasi Backend Login Function

### File: `app/Http/Controllers/Api/AuthController.php`

**Login function yang benar:**

```php
public function login(LoginRequest $request): JsonResponse
{
    // Ambil identifier (email atau name)
    $identifier = $request->filled('email') ? $request->email : $request->name;

    // Cari user by email OR name
    $user = User::where('email', $identifier)
        ->orWhere('name', $identifier)
        ->first();

    // Verify user ada dan password cocok
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'name' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Create token
    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token,
    ]);
}
```

**Status Anda:** ✅ BENAR

- ✅ Mencari by email OR name
- ✅ Verify dengan `Hash::check()`
- ✅ Return token jika berhasil

---

## 🔍 STEP 6: Cek CSRF Cookie di register.js

### File: `resources/js/register.js`

**Yang seharusnya ada:**

```javascript
// ✅ Enable credentials
window.axios.defaults.withCredentials = true;

// ✅ CSRF cookie function
async function getCsrfCookie() {
    await window.axios.get("/sanctum/csrf-cookie", { baseURL: "/" });
}

// ✅ Dalam form submit:
// STEP 1: Ambil CSRF cookie
await getCsrfCookie();

// STEP 2: Register
await window.axios.post("/register", payload);

// STEP 3: Login
await window.axios.post("/login", {...});

// STEP 4: Redirect
```

**Status Anda:** ✅ SUDAH DIPERBAIKI - Register.js sekarang include getCsrfCookie()

---

## 🧪 STEP 7: Full Test End-to-End

Setelah semua di-verify, lakukan test lengkap:

### Test 1: Via Browser

1. **Clear data lama:**

    ```bash
    php artisan migrate:fresh --seed
    ```

2. **Start servers:**

    ```bash
    # Terminal 1
    php artisan serve

    # Terminal 2
    npm run dev
    ```

3. **Buka browser:**

    ```
    http://localhost:8000/register
    ```

4. **Isi form:**
    - Name: newuser
    - Email: newuser@test.com
    - Password: testpass123
    - Confirm: testpass123
    - Click: Daftar

5. **Lihat DevTools Console (F12):**

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

6. **Should redirect to dashboard** ✅

### Test 2: Verify Data di Database

```bash
php artisan tinker
>>> App\Models\User::where('email', 'newuser@test.com')->first()
# Harusnya menampilkan user baru yang tadi di-register
>>> exit
```

---

## 🆘 Common Database Issues & Fixes

### Issue 1: "no such table: users"

**Symptom:** Error when trying to query users table

**Fix:**

```bash
php artisan migrate
```

---

### Issue 2: "Table already exists"

**Symptom:** Migration error saat jalankan migrate

**Fix:**

```bash
php artisan migrate:reset
php artisan migrate
```

Atau:

```bash
php artisan migrate:fresh --seed
```

---

### Issue 3: Email Already Registered

**Symptom:** Register error "Email sudah terdaftar"

**Fix:** Gunakan email baru atau clear users:

```bash
php artisan tinker
>>> DB::table('users')->truncate()  # Hapus semua user
>>> exit
```

---

### Issue 4: User Ada Tapi Login Gagal

**Symptom:** User di database tapi password tidak cocok

**Debug:**

```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->password  # Check format (harusnya $2y$...)
>>> Hash::check('password_yang_ditest', $user->password)  # true atau false?
>>> exit
```

**Fix:** Update password dengan hash:

```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->update(['password' => Hash::make('newpass123')]);
>>> exit
```

---

## 📊 Database Query Commands

### Lihat struktur users table

```bash
php artisan tinker
>>> Schema::getColumns('users')
# Atau
>>> DB::getSchemaBuilder()->getColumnListing('users')
>>> exit
```

### Lihat semua data users

```bash
php artisan tinker
>>> DB::table('users')->get()
# Atau
>>> App\Models\User::with('tokens')->get()  # Lihat dengan tokens
>>> exit
```

### Delete user tertentu

```bash
php artisan tinker
>>> App\Models\User::where('email', 'test@test.com')->delete()
>>> exit
```

### Update user

```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->update(['name' => 'newname']);
>>> exit
```

---

## ✅ Checklist Sebelum Test

- [ ] Database migrated: `php artisan migrate`
- [ ] User ada di database: `php artisan tinker` → `App\Models\User::all()`
- [ ] Password ter-hash: tinker → `$user->password` (check format $2y$...)
- [ ] register.js updated: punya `getCsrfCookie()` function
- [ ] auth.js updated: punya `withCredentials = true`
- [ ] bootstrap.js updated: punya `withCredentials = true`
- [ ] .env correct: `APP_URL=http://localhost:8000`
- [ ] Server running: `php artisan serve`
- [ ] Assets built: `npm run dev`

---

## 📞 Debug Commands Quick Reference

```bash
# Check users exist
php artisan tinker
>>> App\Models\User::all();

# Create test user
>>> App\Models\User::create(['name'=>'test','email'=>'test@test.com','password'=>Hash::make('pass123')]);

# Check user password
>>> $u = App\Models\User::first();
>>> Hash::check('pass123', $u->password);

# Reset database
php artisan migrate:fresh --seed

# Check tables
>>> Schema::hasTable('users');
>>> DB::table('users')->count();

# Clear users
>>> DB::table('users')->truncate();

# Exit tinker
>>> exit
```

---

**Semoga debugging ini membantu! Ikuti step-by-step dan lapor di step mana error muncul. 🚀**
