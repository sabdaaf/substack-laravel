# 🔍 TINKER DEBUG COMMANDS - COPY & PASTE

Quick reference dengan command Tinker yang siap di-copy & paste untuk debug login issues.

---

## ⚡ QUICK DEBUG (Jalankan ini)

Buka terminal:

```bash
php artisan tinker
```

Lalu copy-paste command di bawah **satu per satu**, tekan Enter setelah tiap command:

---

## 1️⃣ CHECK USER EXISTS

```php
>>> App\Models\User::all();
```

Expected: Lihat list semua user. Jika kosong, database mungkin belum ter-seed.

---

## 2️⃣ CHECK USER BY EMAIL

Ganti `bambang@gmail.com` dengan email Anda:

```php
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();
>>> $user;
```

Expected: Lihat User object dengan id, name, email, etc.

**Jika return `null`** → User tidak ada di database ❌

---

## 3️⃣ CHECK EMAIL MISMATCH (CRITICAL!)

```php
>>> $user = App\Models\User::first();
>>> $user->email;
```

Copy output dan paste di sini untuk check:

```php
# Contoh: Jika output adalah "bambang@gmail.com " (dengan spasi)
# Coba cari dengan spasi:
>>> $user = App\Models\User::where('email', 'bambang@gmail.com ')->first();
>>> $user;
# Jika return User object → EMAIL TERSIMPAN DENGAN SPASI ❌
```

---

## 4️⃣ CHECK PASSWORD HASHING

```php
>>> $user = App\Models\User::first();
>>> $user->password;
```

Expected format: `$2y$12$abc123xyz...` (bcrypt hash)

**Jika plain text** (bukan $2y$...) → Password TIDAK di-hash ❌

---

## 5️⃣ TEST PASSWORD VERIFICATION

Ganti `bambang123` dengan password yang benar:

```php
>>> $user = App\Models\User::where('email', 'bambang@gmail.com')->first();
>>> Hash::check('bambang123', $user->password);
```

Expected: `true` ✅

**Jika return `false`** → Password tidak cocok ❌

---

## 6️⃣ FULL LOGIN SIMULATION (PALING PENTING!)

Ini adalah test keseluruhan proses login:

```php
>>> $email = 'bambang@gmail.com';
>>> $password = 'bambang123';
>>> $user = App\Models\User::where('email', trim(strtolower($email)))->first();
>>> if ($user && Hash::check($password, $user->password)) { echo "✅ LOGIN SUCCESS"; } else { echo "❌ LOGIN FAILED"; }
```

Expected: **✅ LOGIN SUCCESS**

**Jika ❌ LOGIN FAILED** → Cek step 2-5 di atas

---

## 7️⃣ CHECK EMAIL STRING LENGTH (Untuk detect spasi)

```php
>>> $user = App\Models\User::first();
>>> strlen($user->email);
```

Bandingkan dengan expected length:

- `bambang@gmail.com` = 17 karakter ✅
- `bambang@gmail.com ` = 18 karakter (1 spasi di akhir) ❌

---

## 8️⃣ FIX: TRIM SEMUA EMAIL (Jika ada spasi)

```php
>>> User::query()->update(['email' => DB::raw('LOWER(TRIM(email))')]);
```

Command ini akan:

- TRIM() spasi di awal/akhir
- LOWER() convert ke lowercase

**Hasil:** Semua email clean ✅

Verify:

```php
>>> App\Models\User::all()->map(fn($u) => "$u->name: $u->email");
```

---

## 9️⃣ FIX: UPDATE SINGLE USER (Jika hanya satu user)

```php
>>> $user = App\Models\User::where('email', 'bambang@gmail.com ')->first();
>>> $user->update(['email' => trim(strtolower($user->email))]);
>>> $user->refresh();
>>> $user->email;
```

---

## 🔟 FIX: RE-HASH PASSWORD (Jika tidak ter-hash)

Ganti `bambang123` dengan password yang benar:

```php
>>> $user = App\Models\User::first();
>>> $user->update(['password' => Hash::make('bambang123')]);
>>> $user->refresh();
>>> Hash::check('bambang123', $user->password);
```

Expected last line: `true` ✅

---

## EXIT TINKER

```php
>>> exit
```

---

## 📋 COMMAND REFERENCE (COPY-PASTE)

Salin seluruh block ini untuk cepat:

```php
// 1. Check semua user
App\Models\User::all();

// 2. Find specific user
$user = App\Models\User::where('email', 'bambang@gmail.com')->first();
$user;

// 3. Check email length (detect spasi)
strlen($user->email);

// 4. Check password hash
$user->password;

// 5. Verify password
Hash::check('bambang123', $user->password);

// 6. Full login test
$user = App\Models\User::where('email', trim(strtolower('bambang@gmail.com')))->first();
if ($user && Hash::check('bambang123', $user->password)) { echo "✅ LOGIN SUCCESS"; } else { echo "❌ LOGIN FAILED"; }

// 7. Fix all emails
User::query()->update(['email' => DB::raw('LOWER(TRIM(email))')]);

// 8. Exit
exit
```

---

## ❓ TROUBLESHOOTING

### "Model not found" error

→ Make sure you're in `php artisan tinker`, not regular bash

### "Class not found"

→ You need: `php artisan tinker` (with full Laravel context)

### Command tidak bekerja

→ Try:

```bash
php artisan tinker
>>> use App\Models\User;
>>> App\Models\User::all();
```

---

## 🎯 EXPECTED FLOW

```
1. php artisan tinker
2. $user = App\Models\User::first();
3. $user->email;  → Should be clean (no spaces)
4. Hash::check('password', $user->password);  → Should be true
5. Full test: ... → Should show ✅ LOGIN SUCCESS
6. exit
```

---

## 📞 IF STILL ERROR

Report these outputs:

1. `$user->email;` output
2. `strlen($user->email);` output
3. `$user->password;` output
4. `Hash::check('password', $user->password);` output
5. Full login test output (step 6️⃣ above)

---

**Ready? Open terminal, run `php artisan tinker` and follow commands above!** 🚀
