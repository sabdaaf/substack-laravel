<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - Substack News</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="bg-white">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo & Brand Name -->
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-gray-900">Substack News</a>
                </div>

                <!-- Navigation Menu -->
                <div class="flex items-center gap-4">
                    <a href="/" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">
                        ← Kembali ke Beranda
                    </a>
                    <button id="logoutButton" class="text-sm font-medium text-gray-600 hover:text-red-600 transition">
                        Logout
                    </button>
                    <!-- Profile Initial Circle -->
                    <div id="profileInitial" title="Memuat profil..."
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white">
                        ?
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Kelola Artikel</h1>
            <button id="openCreateModal" type="button"
                class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition">
                + Buat Artikel Baru
            </button>
        </div>

        <!-- Error Message -->
        <div id="errorMessage"
            class="hidden px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded-lg">
            <p id="errorText"></p>
        </div>

        <!-- Filter & Search Bar -->
        <div
            class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full sm:max-w-sm">
                <input type="text" id="searchInput" placeholder="Cari artikel..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="flex gap-2">
                <button type="button" data-sort="newest"
                    class="sort-btn px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Terbaru
                </button>
                <button type="button" data-sort="oldest"
                    class="sort-btn px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Terlama
                </button>
            </div>
        </div>

        <!-- Articles Table -->
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-sm text-gray-500">
                        <th class="px-6 py-3 font-medium">Judul</th>
                        <th class="px-6 py-3 font-medium">Tanggal</th>
                        <th class="px-6 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody id="postsTableBody" class="divide-y divide-gray-200 bg-white">
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                            Memuat artikel...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="emptyState"
            class="hidden mt-6 rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">
            Belum ada artikel yang cocok.
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="flex justify-center items-center gap-2 py-8"></div>
    </main>

    <!-- Create/Edit Modal -->
    <div id="articleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
        <div class="w-full max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-xl">
            <div class="mb-6 flex items-center justify-between">
                <h2 id="modalTitle" class="text-xl font-semibold text-gray-900">Buat Artikel Baru</h2>
                <button id="closeModal" type="button"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                    Tutup
                </button>
            </div>

            <form id="articleForm" class="space-y-4">
                <input id="articleSlug" type="hidden" name="slug">
                <div>
                    <label for="articleTitle" class="mb-2 block text-sm font-medium text-gray-700">Judul</label>
                    <input id="articleTitle" name="title" type="text" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="articleBody" class="mb-2 block text-sm font-medium text-gray-700">Konten</label>
                    <textarea id="articleBody" name="body" rows="8" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div id="formError"
                    class="hidden rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700"></div>
                <div class="flex justify-end gap-3">
                    <button id="cancelModal" type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700 transition">
                        Simpan Artikel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // NOTE: resources/js/app.js sudah men-set window.axios.defaults.baseURL = '/api'
        // secara global, jadi semua path Axios di sini HARUS relatif (tanpa '/api')
        // agar tidak menjadi double prefix '/api/api/...'.
        const PER_PAGE = 10;

        // State
        let currentUser = null;
        let currentPage = 1;
        let currentSort = 'newest';
        let currentSearch = '';
        let totalPages = 1;

        // DOM Elements
        const profileInitial = document.getElementById('profileInitial');
        const logoutButton = document.getElementById('logoutButton');
        const postsTableBody = document.getElementById('postsTableBody');
        const emptyState = document.getElementById('emptyState');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const searchInput = document.getElementById('searchInput');
        const sortButtons = document.querySelectorAll('.sort-btn');
        const paginationContainer = document.getElementById('paginationContainer');

        const articleModal = document.getElementById('articleModal');
        const modalTitle = document.getElementById('modalTitle');
        const articleForm = document.getElementById('articleForm');
        const articleSlugInput = document.getElementById('articleSlug');
        const articleTitleInput = document.getElementById('articleTitle');
        const articleBodyInput = document.getElementById('articleBody');
        const formError = document.getElementById('formError');

        // --- Auth Helpers ---
        // NOTE: Backend proyek ini menggunakan Sanctum token-based auth (bukan
        // session/cookie SPA), sehingga otentikasi memakai Bearer token yang
        // disimpan di localStorage saat login (lihat resources/js/auth.js),
        // bukan withCredentials + CSRF cookie.
        function getToken() {
            return localStorage.getItem('api_token');
        }

        function authHeaders() {
            return { Authorization: `Bearer ${getToken()}` };
        }

        function requireAuthOrRedirect() {
            if (!getToken()) {
                window.location.href = '/login';
                return false;
            }
            return true;
        }

        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
        }

        function hideError() {
            errorMessage.classList.add('hidden');
        }

        // --- Load Current User (Profile Initial) ---
        async function loadCurrentUser() {
            try {
                const response = await axios.get('/me', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('api_token')}` }
                });

                currentUser = response.data.user;
                const name = currentUser?.name || '';
                profileInitial.textContent = name.trim().charAt(0).toUpperCase() || '?';
                profileInitial.title = name;

                loadPosts();
            } catch (error) {
                if (error.response?.status === 401) {
                    localStorage.removeItem('api_token');
                    window.location.href = '/login';
                    return;
                }
                showError('Gagal memuat profil user.');
            }
        }

        // --- Sort Params ---
        function getSortParams() {
            return currentSort === 'oldest'
                ? { sort_by: 'created_at', order: 'asc' }
                : { sort_by: 'created_at', order: 'desc' };
        }

        // --- Load Posts (milik user yang sedang login) ---
        async function loadPosts() {
            try {
                hideError();
                postsTableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">Memuat artikel...</td>
                    </tr>
                `;

                const params = {
                    page: currentPage,
                    per_page: PER_PAGE,
                    author_id: currentUser?.id,
                    ...getSortParams()
                };

                if (currentSearch) {
                    params.search = currentSearch;
                }

                const response = await axios.get('/posts', {
                    params,
                    headers: authHeaders()
                });

                const data = response.data;
                totalPages = data.last_page;
                renderTable(data.data);
                renderPagination(data);
            } catch (error) {
                if (error.response?.status === 401) {
                    localStorage.removeItem('api_token');
                    window.location.href = '/login';
                    return;
                }
                showError('Gagal memuat artikel. Silakan coba lagi.');
                postsTableBody.innerHTML = '';
            }
        }

        // --- Render Table ---
        function renderTable(posts) {
            postsTableBody.innerHTML = '';

            if (posts.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            posts.forEach(post => {
                const row = document.createElement('tr');
                row.dataset.slug = post.slug;

                row.innerHTML = `
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">${escapeHtml(post.title)}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${formatDate(new Date(post.created_at))}</td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex gap-2">
                            <button type="button" data-action="edit"
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                Edit
                            </button>
                            <button type="button" data-action="delete"
                                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition">
                                Hapus
                            </button>
                        </div>
                    </td>
                `;

                row.querySelector('[data-action="edit"]').addEventListener('click', () => openEditModal(post));
                row.querySelector('[data-action="delete"]').addEventListener('click', () => deletePost(post.slug, row));

                postsTableBody.appendChild(row);
            });
        }

        // --- Pagination ---
        function renderPagination(data) {
            paginationContainer.innerHTML = '';
            if (totalPages <= 1) return;

            if (data.prev_page_url) {
                paginationContainer.appendChild(createPaginationButton('← Sebelumnya', () => {
                    currentPage--;
                    loadPosts();
                }));
            }

            const pageInfo = document.createElement('span');
            pageInfo.className = 'text-sm text-gray-600 px-2';
            pageInfo.textContent = `Halaman ${data.current_page} dari ${data.last_page}`;
            paginationContainer.appendChild(pageInfo);

            if (data.next_page_url) {
                paginationContainer.appendChild(createPaginationButton('Selanjutnya →', () => {
                    currentPage++;
                    loadPosts();
                }));
            }
        }

        function createPaginationButton(text, onclick) {
            const btn = document.createElement('button');
            btn.textContent = text;
            btn.className = 'px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition';
            btn.onclick = onclick;
            return btn;
        }

        // --- Delete Post ---
        async function deletePost(slug, row) {
            if (!confirm('Yakin ingin menghapus artikel ini?')) {
                return;
            }

            try {
                await axios.delete(`/posts/${slug}`, {
                    headers: authHeaders()
                });

                row.remove();

                if (postsTableBody.children.length === 0) {
                    emptyState.classList.remove('hidden');
                }
            } catch (error) {
                showError(error.response?.data?.message || 'Gagal menghapus artikel.');
            }
        }

        // --- Modal Handling ---
        function openCreateModal() {
            modalTitle.textContent = 'Buat Artikel Baru';
            articleForm.reset();
            articleSlugInput.value = '';
            formError.classList.add('hidden');
            articleModal.classList.remove('hidden');
            articleModal.classList.add('flex');
        }

        function openEditModal(post) {
            modalTitle.textContent = 'Edit Artikel';
            articleSlugInput.value = post.slug;
            articleTitleInput.value = post.title;
            articleBodyInput.value = post.body;
            formError.classList.add('hidden');
            articleModal.classList.remove('hidden');
            articleModal.classList.add('flex');
        }

        function closeArticleModal() {
            articleModal.classList.add('hidden');
            articleModal.classList.remove('flex');
        }

        // --- Create/Update Submit ---
        articleForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            formError.classList.add('hidden');

            const slug = articleSlugInput.value;
            const payload = {
                title: articleTitleInput.value.trim(),
                body: articleBodyInput.value.trim()
            };

            try {
                if (slug) {
                    await axios.patch(`/posts/${slug}`, payload, {
                        headers: authHeaders()
                    });
                } else {
                    await axios.post('/posts', payload, {
                        headers: authHeaders()
                    });
                }

                closeArticleModal();
                currentPage = 1;
                loadPosts();
            } catch (error) {
                const message = error.response?.data?.errors
                    ? Object.values(error.response.data.errors)[0]?.[0]
                    : error.response?.data?.message || 'Gagal menyimpan artikel.';
                formError.textContent = message;
                formError.classList.remove('hidden');
            }
        });

        // --- Event Listeners ---
        document.getElementById('openCreateModal').addEventListener('click', openCreateModal);
        document.getElementById('closeModal').addEventListener('click', closeArticleModal);
        document.getElementById('cancelModal').addEventListener('click', closeArticleModal);

        let searchTimeout;
        searchInput.addEventListener('input', function (e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadPosts();
            }, 300);
        });

        sortButtons.forEach(button => {
            button.addEventListener('click', function () {
                sortButtons.forEach(btn => {
                    btn.classList.remove('bg-gray-900', 'text-white', 'border-gray-900');
                    btn.classList.add('border-gray-300', 'text-gray-700');
                });
                this.classList.remove('border-gray-300', 'text-gray-700');
                this.classList.add('bg-gray-900', 'text-white', 'border-gray-900');

                currentSort = this.dataset.sort;
                currentPage = 1;
                loadPosts();
            });
        });
        sortButtons[0].classList.remove('border-gray-300', 'text-gray-700');
        sortButtons[0].classList.add('bg-gray-900', 'text-white', 'border-gray-900');

        logoutButton.addEventListener('click', async function () {
            const token = getToken();
            try {
                if (token) {
                    await axios.post('/logout', {}, { headers: authHeaders() });
                }
            } catch (error) {
                // Ignore errors, proceed to clear session anyway
            } finally {
                localStorage.removeItem('api_token');
                window.location.href = '/';
            }
        });

        // --- Utils ---
        function formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }).format(date);
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // --- Init ---
        document.addEventListener('DOMContentLoaded', function () {
            if (!requireAuthOrRedirect()) return;
            loadCurrentUser();
        });
    </script>
</body>

</html>