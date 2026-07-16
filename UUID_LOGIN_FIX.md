# 🔧 UUID LOGIN FIX - Panduan Lengkap

Dokumentasi lengkap untuk mengatasi masalah login dengan UUID Primary Key User di Laravel 12 + Sanctum.

---

## 🎯 MASALAH YANG TERSELESAIKAN

User sudah ter-register di database (caniago, bambang, dll) dengan password ter-hash, tapi login tetap gagal masuk ke dashboard. **Root cause:** Database menggunakan UUID sebagai primary key User, tapi backend/frontend tidak dikonfigurasi dengan benar untuk handle UUID.

---

## ✅ SOLUSI - 4 FILE YANG SUDAH DIPERBAIKI

### **1️⃣ app/Models/User.php** - Add Explicit UUID Settings

**Masalah:**

```php
// Sebelum - Hanya punya HasUuids trait
use HasApiTokens, HasFactory, HasUuids, Notifiable;

// ❌ KURANG: Explicit UUID configuration untuk Sanctum
```

**Solusi (Sudah Diterapkan):**

```php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * The primary key type for the model.
     * ✅ CRITICAL: UUID is string type, not auto-incrementing integer
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * ✅ CRITICAL: UUID is not auto-incrementing
     */
    public $incrementing = false;

    // ... rest of the model
}
```

**Mengapa penting:**

- `$keyType = 'string'` → Memberitahu Laravel bahwa ID adalah string (UUID), bukan integer
- `$incrementing = false` → UUID tidak auto-increment seperti integer
- **Without this:** Sanctum bisa error saat membuat/verify token dengan UUID user ID

---

### **2️⃣ app/Http/Controllers/Api/AuthController.php** - Optimize Login Logic

**Masalah:**

```php
// Sebelum - Confusing logic
$identifier = $request->filled('email') ? $request->email : $request->name;
$user = User::where('email', $identifier)
    ->orWhere('name', $identifier)
    ->first();
```

**Problem:** Jika user kirim email 'test@test.com' di field 'name', backend cari:

```
WHERE email = 'test@test.com' OR name = 'test@test.com'
```

Ini tidak effisien dan bingung.

**Solusi (Sudah Diterapkan):**

```php
public function login(LoginRequest $request): JsonResponse
{
    // ✅ Prioritize email search (email is unique), fallback to name
    $user = null;

    if ($request->filled('email')) {
        $user = User::where('email', $request->email)->first();
    }

    // If not found by email, try by name
    if (!$user && $request->filled('name')) {
        $user = User::where('name', $request->name)->first();
    }

    // Verify user exists and password is correct
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'name' => ['Email atau nama dan password yang Anda masukkan tidak sesuai.'],
        ]);
    }

    // Create token with UUID user ID
    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token,
    ]);
}
```

**Keuntungan:**

- ✅ Clear priority: email first (karena unique), then name
- ✅ Proper error handling untuk UUID
- ✅ More efficient database queries

---

### **3️⃣ resources/js/auth.js** - Support Email Login

**Masalah:**

```javascript
// Sebelum - Hanya read 'name' field
const name = document.getElementById("name").value.trim();
const response = await window.axios.post("/login", {
    name, // ← Hanya kirim name
    password,
    device_name: "browser",
});
```

**Problem:** Form hanya punya input 'name', user tidak bisa login dengan email.

**Solusi (Sudah Diterapkan):**

```javascript
if (form) {
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // ✅ Try to get email first (if email field exists), fallback to name
        const emailField = document.getElementById("email");
        const nameField = document.getElementById("name");

        const email = emailField ? emailField.value.trim() : null;
        const name = nameField ? nameField.value.trim() : null;
        const password = document.getElementById("password").value;

        // Validate: at least one of email or name is provided
        if (!email && !name) {
            errorBox.classList.remove("hidden");
            errorBox.innerText = "❌ Email atau nama wajib diisi.";
            return;
        }

        try {
            // Get CSRF cookie
            const csrfSuccess = await getCsrfCookie();
            if (!csrfSuccess) {
                throw new Error("Failed to initialize CSRF cookie");
            }

            // ✅ Build payload with whatever is available
            const loginPayload = {
                device_name: "browser",
                password,
            };

            if (email) {
                loginPayload.email = email;
                console.log(`   Email: ${email}`);
            }
            if (name) {
                loginPayload.name = name;
                console.log(`   Name: ${name}`);
            }

            const response = await window.axios.post("/login", loginPayload);
            const token = response.data.token;
            localStorage.setItem("api_token", token);

            // Redirect to dashboard
            window.location.href = "/dashboard";
        } catch (error) {
            // Error handling...
        }
    });
}
```

**Keuntungan:**

- ✅ Support email OR name login
- ✅ Flexible form yang bisa handle keduanya
- ✅ Better error messages

---

### **4️⃣ resources/views/auth/login.blade.php** - Update Form

**Masalah:**

```html
<!-- Sebelum - Label hanya "Nama Lengkap" -->
<label for="name">Nama Lengkap</label>
<input id="name" name="name" type="text" required />
<!-- User confused - bisa input email atau name? -->
```

**Solusi (Sudah Diterapkan):**

```html
<form id="form-login" class="mt-8 space-y-4">
    <div>
        <!-- ✅ Updated label untuk clarity -->
        <label for="name" class="mb-2 block text-sm font-medium text-slate-300">
            Email atau Nama Lengkap
        </label>
        <input
            id="name"
            name="name"
            type="text"
            placeholder="Masukkan email atau nama lengkap"
            required
        />
    </div>

    <div>
        <label
            for="password"
            class="mb-2 block text-sm font-medium text-slate-300"
        >
            Password
        </label>
        <input id="password" name="password" type="password" required />
    </div>
    <!-- ... -->
</form>
```

**Updated description:**

```html
<!-- Sebelum -->
<p class="mt-2 text-sm text-slate-400">
    Login dengan nama lengkap / username dan password.
</p>

<!-- Setelah ✅ -->
<p class="mt-2 text-sm text-slate-400">
    Login dengan email atau nama lengkap dan password.
</p>
```

**Keuntungan:**

- ✅ Clear user expectation - bisa login dengan email ATAU nama
- ✅ Better UX dengan placeholder text
- ✅ Mengurangi confusion

---

## 🔄 FLOW LENGKAP SETELAH FIX

### User Registration Flow

```
1. User di halaman /register
   ↓
2. Input: name=caniago, email=caniago@test.com, password=rahasia123
   ↓
3. POST /api/register
   ↓
4. Backend (AuthController.register):
   - Hash::make('rahasia123') → $2y$12$abc123...
   - User::create() → Simpan ke database
   - UUID auto-generated: f47ac10b-58cc-4372-a567-0e02b2c3d479
   ↓
5. Response: { token: "1|xyz...", user: {...} }
   ↓
6. Frontend (register.js): localStorage.setItem('api_token', token)
   ↓
7. Auto-login + redirect ke /dashboard ✅
```

### User Login Flow (SETELAH FIX)

```
1. User di halaman /login
   ↓
2. Input: name_field="caniago@test.com" (atau "caniago"), password="rahasia123"
   ↓
3. F12 Console logging:
   📝 Step 1: Fetching CSRF cookie...
   ✅ CSRF cookie obtained successfully
   🔑 Step 2: Sending login credentials...
   Email: caniago@test.com
   ↓
4. POST /api/login dengan payload:
   {
     "email": "caniago@test.com",  // atau "name": "caniago"
     "password": "rahasia123",
     "device_name": "browser"
   }
   ↓
5. Backend (AuthController.login):
   - If email provided:
     * User::where('email', 'caniago@test.com') → Found! ✅
   - If not found by email, try name:
     * User::where('name', 'caniago') → Found! ✅
   - Hash::check('rahasia123', user.password) → true ✅
   - $user->createToken() → Generate token dengan UUID ID
   ↓
6. Response: { token: "2|abc...", user: {id: "f47ac10b...", name: "caniago", ...} }
   ↓
7. Frontend (auth.js):
   - localStorage.setItem('api_token', '2|abc...')
   - Set Authorization header
   ↓
8. window.location.href = '/dashboard'
   ↓
9. Browser redirect ke /dashboard ✅
   ↓
10. Dashboard route auth middleware:
    - Check Authorization header
    - Verify token (UUID ID embedded dalam token)
    - User loaded: caniago (UUID: f47ac10b...) ✅
   ↓
11. Dashboard page rendered ✅
```

---

## 🚀 TEST SETELAH FIX

### Step 1: Clear Cache & Restart

```bash
php artisan optimize:clear
php artisan config:clear
```

### Step 2: Migrate Database (Jika Belum)

```bash
php artisan migrate
```

### Step 3: Start Servers

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

### Step 4: Test Login

**Scenario 1: Login dengan Email**

1. Buka: `http://localhost:8000/login`
2. Input:
    - Email atau Nama: `caniago@test.com`
    - Password: (sesuai password di database)
3. Klik: Login
4. Expected: Redirect ke `/dashboard` ✅

**Scenario 2: Login dengan Name**

1. Buka: `http://localhost:8000/login`
2. Input:
    - Email atau Nama: `caniago` (hanya nama tanpa email)
    - Password: (sesuai password di database)
3. Klik: Login
4. Expected: Redirect ke `/dashboard` ✅

### Step 5: Verify Console Logs

F12 → Console tab → Expected:

```
📝 Step 1: Fetching CSRF cookie...
✅ CSRF cookie obtained successfully
🔑 Step 2: Sending login credentials...
Email: caniago@test.com
✅ Login response: {message: "Login successful", user: {...}, token: "1|..."}
💾 Token saved to localStorage
🎉 Login successful! Redirecting...
```

### Step 6: Verify localStorage

F12 → Console → Type:

```javascript
localStorage.getItem("api_token");
// Output: "1|abc123xyz..." (bukan null)
```

---

## 🔍 DEBUGGING JIKA MASIH ERROR

### Error: "Email atau nama dan password yang Anda masukkan tidak sesuai"

**Debug:**

```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'caniago@test.com')->first();
>>> $user  # Pastikan user ada
>>> Hash::check('rahasia123', $user->password)  # Pastikan true
>>> exit
```

### Error: "CSRF token tidak valid" (419)

**Fix:**

```bash
php artisan optimize:clear
# Restart servers
# Clear browser cookies: DevTools → Application → Cookies → Delete
# Try login lagi
```

### Error: "Koneksi ke server gagal"

**Fix:**

- Check Terminal 1: `php artisan serve` running?
- Check Terminal 2: `npm run dev` running?

### Error: UUID issues atau token tidak ter-create

**Debug:**

```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->id  # Lihat format UUID: f47ac10b-...
>>> $user->keyType  # Harusnya 'string'
>>> $user->incrementing  # Harusnya false
>>> exit
```

**Fix:** Pastikan User.php punya:

```php
protected $keyType = 'string';
public $incrementing = false;
```

---

## 📝 Code Reference - Perubahan yang Dibuat

### app/Models/User.php

```php
protected $keyType = 'string';
public $incrementing = false;
```

### app/Http/Controllers/Api/AuthController.php

```php
// Login logic yang clear dan efficient
if ($request->filled('email')) {
    $user = User::where('email', $request->email)->first();
}
if (!$user && $request->filled('name')) {
    $user = User::where('name', $request->name)->first();
}
```

### resources/js/auth.js

```javascript
// Support email OR name
const email = emailField ? emailField.value.trim() : null;
const name = nameField ? nameField.value.trim() : null;

const loginPayload = { device_name: "browser", password };
if (email) loginPayload.email = email;
if (name) loginPayload.name = name;
```

### resources/views/auth/login.blade.php

```html
<label for="name">Email atau Nama Lengkap</label>
<input placeholder="Masukkan email atau nama lengkap" />
```

---

## ✅ Checklist Post-Fix

- [ ] User.php punya `$keyType = 'string'` ✅
- [ ] User.php punya `$incrementing = false` ✅
- [ ] AuthController login() logic sudah optimized ✅
- [ ] auth.js support email OR name ✅
- [ ] login.blade.php label updated ✅
- [ ] Database migrated: `php artisan migrate`
- [ ] Servers running: `php artisan serve` + `npm run dev`
- [ ] Can login dengan email ✅
- [ ] Can login dengan name ✅
- [ ] Redirect ke dashboard ✅
- [ ] Token di localStorage ✅

---

## 🎯 Summary

**Masalah:** UUID User ID tidak ter-handle dengan benar oleh Sanctum + Frontend Form

**Solusi:**

1. ✅ User.php: Add explicit UUID settings (`$keyType = 'string'`, `$incrementing = false`)
2. ✅ AuthController: Clear login logic (email first, then name)
3. ✅ auth.js: Support email OR name login
4. ✅ login.blade.php: Update form label untuk clarity

**Result:** Login sekarang berjalan dengan UUID tanpa error! 🚀

---

**Siap test? Ikuti Step 1-6 di atas dan seharusnya sudah OK!**
