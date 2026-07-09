<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Artikel | Substack News</title>
    @vite(['resources/css/app.css', 'resources/js/dashboard.js'])
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-4 py-8 sm:px-6 lg:px-8">
        <header
            class="mb-8 flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-2xl shadow-slate-950/40 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-400">Substack News</p>
                <h1 class="mt-2 text-3xl font-semibold text-white">Kelola Artikel</h1>
            </div>

            <button id="open-create-modal" type="button"
                class="rounded-xl bg-sky-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-400">
                Buat Artikel Baru
            </button>
        </header>

        <section class="rounded-2xl border border-slate-800 bg-slate-900/80 p-4 shadow-2xl shadow-slate-950/40 sm:p-6">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="w-full lg:max-w-xl">
                    <label for="search-input" class="mb-2 block text-sm font-medium text-slate-300">Cari artikel</label>
                    <div class="flex gap-2">
                        <input id="search-input" type="text" placeholder="Cari berdasarkan judul..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-white outline-none transition focus:border-sky-500">
                        <button id="search-button" type="button"
                            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-semibold text-slate-200 transition hover:border-sky-500 hover:text-sky-300">
                            Cari
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" data-sort="latest"
                        class="rounded-xl border border-sky-500 bg-sky-500 px-3 py-2 text-sm font-semibold text-white transition">
                        Terbaru
                    </button>
                    <button type="button" data-sort="oldest"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:border-sky-500 hover:text-sky-300">
                        Terlama
                    </button>
                    <button type="button" data-sort="az"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:border-sky-500 hover:text-sky-300">
                        A-Z
                    </button>
                    <button type="button" data-sort="za"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:border-sky-500 hover:text-sky-300">
                        Z-A
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead>
                        <tr class="text-left text-sm text-slate-400">
                            <th class="px-4 py-3 font-medium">Judul</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="posts-table-body" class="divide-y divide-slate-800/80">
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500">
                                Memuat artikel...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="empty-state"
                class="mt-6 hidden rounded-xl border border-dashed border-slate-700 p-6 text-center text-sm text-slate-400">
                Belum ada artikel yang cocok dengan pencarian Anda.
            </div>

            <div id="pagination-container" class="mt-6 flex justify-center"></div>
        </section>
    </div>

    <div id="article-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 px-4">
        <div class="w-full max-w-2xl rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-400">Artikel</p>
                    <h2 id="modal-title" class="mt-1 text-xl font-semibold text-white">Buat Artikel Baru</h2>
                </div>
                <button id="close-modal" type="button"
                    class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-300 hover:border-slate-500">
                    Tutup
                </button>
            </div>

            <form id="article-form" class="space-y-4">
                <input id="article-slug" type="hidden" name="slug">
                <div>
                    <label for="article-title" class="mb-2 block text-sm font-medium text-slate-300">Judul</label>
                    <input id="article-title" name="title" type="text" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white outline-none transition focus:border-sky-500">
                </div>
                <div>
                    <label for="article-slug-input" class="mb-2 block text-sm font-medium text-slate-300">Slug</label>
                    <input id="article-slug-input" name="slug_input" type="text"
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white outline-none transition focus:border-sky-500">
                </div>
                <div>
                    <label for="article-body" class="mb-2 block text-sm font-medium text-slate-300">Konten</label>
                    <textarea id="article-body" name="body" rows="8" required
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white outline-none transition focus:border-sky-500"></textarea>
                </div>
                <div id="form-error"
                    class="hidden rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-sm text-rose-300">
                </div>
                <div class="flex justify-end gap-3">
                    <button id="cancel-modal" type="button"
                        class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:border-slate-500">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-400">
                        Simpan Artikel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>