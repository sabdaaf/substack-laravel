<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Foogu News</title>
    @vite(['resources/css/app.css', 'resources/js/register.js'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
    <div
        class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.18),_transparent_45%)] px-4 py-10">
        <div
            class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900/80 p-8 shadow-2xl shadow-slate-950/40 backdrop-blur">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-400">Substack News</p>
                <h1 class="mt-2 text-2xl font-semibold text-white">Buat akun admin</h1>
                <p class="mt-2 text-sm text-slate-400">Daftarkan akun admin untuk mengelola berita.</p>
            </div>

            <form id="form-register" class="mt-8 space-y-4">
                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-slate-300">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-white outline-none ring-0 transition focus:border-sky-500">
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email</label>
                    <input id="email" name="email" type="email" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-white outline-none ring-0 transition focus:border-sky-500">
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-300">Password</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-white outline-none ring-0 transition focus:border-sky-500">
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-300">Konfirmasi
                        Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-white outline-none ring-0 transition focus:border-sky-500">
                </div>

                <div id="error-message"
                    class="hidden rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-300">
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-400">
                    Daftar
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Sudah punya akun?
                <a href="/login" class="ml-1 text-sky-400 transition hover:text-sky-300">Masuk</a>
            </p>
        </div>
    </div>
</body>

</html>