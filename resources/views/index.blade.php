<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Substack News - Portal Berita Terpercaya</title>
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
                    <div class="flex-shrink-0">
                        <a href="/" class="text-2xl font-bold text-gray-900">Substack News</a>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-center space-x-8">
                        <a href="/" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition">Beranda</a>
                        <a href="/dashboard"
                            class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-white to-gray-50 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl text-center">
            <h1 class="text-5xl font-bold text-gray-900 mb-4">Substack News</h1>
            <p class="text-xl text-gray-600 mb-8">Portal berita terpercaya dengan artikel berkualitas dari penulis
                profesional</p>
        </div>
    </section>

    <!-- Search & Filter Bar -->
    <section class="bg-white px-4 py-8 sm:px-6 lg:px-8 border-b border-gray-200">
        <div class="mx-auto max-w-7xl">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <input type="text" id="searchInput" placeholder="Cari artikel..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-2">
                    <button
                        class="filter-btn flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                        data-sort="newest">
                        Terbaru
                    </button>
                    <button
                        class="filter-btn flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                        data-sort="oldest">
                        Terlama
                    </button>
                </div>

                <!-- Title A-Z Filter -->
                <button
                    class="filter-btn px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    data-sort="title-asc">
                    Judul A-Z
                </button>
            </div>
        </div>
    </section>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="hidden text-center py-12">
        <div class="inline-flex items-center justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
        </div>
    </div>

    <!-- Posts Grid -->
    <section class="px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div id="postsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Posts will be loaded here by JavaScript -->
            </div>
        </div>
    </section>

    <!-- No Results Message -->
    <div id="noResults" class="hidden text-center py-12 px-4">
        <p class="text-gray-500 text-lg">Tidak ada artikel yang ditemukan</p>
    </div>

    <!-- Error Message -->
    <div id="errorMessage"
        class="hidden px-4 py-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded mx-4 mt-4">
        <p id="errorText"></p>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="flex justify-center items-center gap-2 py-8 px-4">
        <!-- Pagination will be loaded here -->
    </div>

    <script>
        // Configuration
        // NOTE: resources/js/app.js sudah men-set window.axios.defaults.baseURL = '/api'
        // secara global, jadi semua path Axios di sini HARUS relatif (tanpa '/api')
        // agar tidak menjadi double prefix '/api/api/...'.
        const POSTS_PER_PAGE = 9;

        // State
        let currentPage = 1;
        let currentSort = 'newest';
        let currentSearch = '';
        let totalPages = 1;

        // DOM Elements
        const postsContainer = document.getElementById('postsContainer');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const noResults = document.getElementById('noResults');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const searchInput = document.getElementById('searchInput');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const paginationContainer = document.getElementById('paginationContainer');

        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            loadPosts();
            setupEventListeners();
        });

        // Setup Event Listeners
        function setupEventListeners() {
            // Search input - debounced
            let searchTimeout;
            searchInput.addEventListener('input', function (e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentSearch = e.target.value.trim();
                    currentPage = 1;
                    loadPosts();
                }, 300);
            });

            // Filter buttons
            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Remove active state from all buttons
                    filterButtons.forEach(btn => {
                        btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                        btn.classList.add('border-gray-300', 'text-gray-700');
                    });

                    // Add active state to clicked button
                    this.classList.remove('border-gray-300', 'text-gray-700');
                    this.classList.add('bg-blue-600', 'text-white', 'border-blue-600');

                    currentSort = this.dataset.sort;
                    currentPage = 1;
                    loadPosts();
                });
            });

            // Set initial active button
            filterButtons[0].classList.remove('border-gray-300', 'text-gray-700');
            filterButtons[0].classList.add('bg-blue-600', 'text-white', 'border-blue-600');
        }

        // Get Sort Parameters
        function getSortParams() {
            switch (currentSort) {
                case 'newest':
                    return { sort_by: 'created_at', order: 'desc' };
                case 'oldest':
                    return { sort_by: 'created_at', order: 'asc' };
                case 'title-asc':
                    return { sort_by: 'title', order: 'asc' };
                default:
                    return { sort_by: 'created_at', order: 'desc' };
            }
        }

        // Load Posts
        async function loadPosts() {
            try {
                showLoading(true);
                hideError();

                const sortParams = getSortParams();
                const params = {
                    page: currentPage,
                    per_page: POSTS_PER_PAGE,
                    ...sortParams
                };

                if (currentSearch) {
                    params.search = currentSearch;
                }

                const response = await axios.get('/posts', { params });
                const data = response.data;

                totalPages = data.last_page;
                renderPosts(data.data);
                renderPagination(data);

                if (data.data.length === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }

            } catch (error) {
                // URL lengkap yang sebenarnya ditembak Axios (baseURL + path + query)
                const requestedUrl = `${error.config?.baseURL ?? ''}${error.config?.url ?? ''}`;

                console.error('=== AXIOS ERROR DETAILS ===');
                console.error('Requested URL:', requestedUrl || '/posts');
                console.error('Error Object:', error);
                console.error('Response Status:', error.response?.status);
                console.error('Response Data:', error.response?.data);
                console.error('Error Message:', error.message);
                console.error('Error Config:', error.config);
                console.error('=== END ERROR ===');

                let errorMsg = 'Gagal memuat artikel. Silakan coba lagi.';

                // Custom error messages based on status code
                if (error.response) {
                    // Server responded with error status code
                    switch (error.response.status) {
                        case 400:
                            errorMsg = 'Request tidak valid. Periksa parameter pencarian.';
                            break;
                        case 401:
                            errorMsg = 'Anda harus login terlebih dahulu.';
                            break;
                        case 403:
                            errorMsg = 'Anda tidak memiliki akses ke resource ini.';
                            break;
                        case 404:
                            // 404 di sini berarti REQUEST BERHASIL SAMPAI ke server,
                            // tapi server tidak menemukan route-nya. Kemungkinan penyebab:
                            // 1. Bukan diakses lewat `php artisan serve` / document root
                            //    web server tidak diarahkan ke folder `public/`.
                            // 2. Diakses lewat Vite dev server (port 5173) yang tidak
                            //    mem-proxy `/api/*` ke Laravel.
                            // 3. Route cache basi -> jalankan `php artisan route:clear`.
                            console.error(
                                '404: Route ditemukan sampai server, tapi Laravel tidak punya route ini. ' +
                                'Cek: (a) akses via php artisan serve / document root = public/, ' +
                                '(b) bukan lewat Vite dev server, (c) jalankan php artisan route:clear.'
                            );
                            errorMsg = `Endpoint API tidak ditemukan (404) di ${requestedUrl}. Pastikan server Laravel berjalan lewat "php artisan serve" dan tidak diakses lewat Vite dev server.`;
                            break;
                        case 429:
                            errorMsg = 'Terlalu banyak request. Silakan tunggu beberapa saat.';
                            break;
                        case 500:
                        case 502:
                        case 503:
                            errorMsg = 'Server error. Silakan coba lagi nanti.';
                            break;
                        default:
                            errorMsg = `Error ${error.response.status}: ${error.response.statusText}`;
                    }
                } else if (error.request) {
                    // Request made but no response received
                    errorMsg = 'Tidak ada respons dari server. Pastikan API server berjalan di port 8000.';
                } else {
                    // Error in request setup
                    errorMsg = `Error: ${error.message}`;
                }

                showError(errorMsg);
                postsContainer.innerHTML = '';
            } finally {
                showLoading(false);
            }
        }

        // Render Posts
        function renderPosts(posts) {
            postsContainer.innerHTML = '';

            posts.forEach(post => {
                const card = createPostCard(post);
                postsContainer.appendChild(card);
            });
        }

        // Create Post Card
        function createPostCard(post) {
            const article = document.createElement('article');
            article.className = 'group bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300';

            // Format date
            const publishDate = new Date(post.created_at);
            const timeAgo = getTimeAgo(publishDate);

            // Extract excerpt from body (first 150 chars)
            const excerpt = post.body.substring(0, 150).replace(/<[^>]*>/g, '').trim() + '...';

            article.innerHTML = `
                <a href="/posts/${post.slug}" class="block h-full no-underline">
                    <div class="flex flex-col h-full p-5">
                        <!-- Time Badge -->
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                ${timeAgo}
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition">
                            ${escapeHtml(post.title)}
                        </h3>

                        <!-- Excerpt -->
                        <p class="text-sm text-gray-600 mb-4 flex-grow line-clamp-3">
                            ${escapeHtml(excerpt)}
                        </p>

                        <!-- Author & Date Footer -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div>
                                <p class="text-xs font-medium text-gray-900">
                                    ${escapeHtml(post.author.name || 'Unknown')}
                                </p>
                                <p class="text-xs text-gray-500">
                                    ${formatDate(publishDate)}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            `;

            return article;
        }

        // Format Date
        function formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }).format(date);
        }

        // Get Time Ago
        function getTimeAgo(date) {
            const now = new Date();
            const diffMs = now - new Date(date);
            const diffSecs = Math.floor(diffMs / 1000);
            const diffMins = Math.floor(diffSecs / 60);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);
            const diffWeeks = Math.floor(diffDays / 7);

            if (diffSecs < 60) return 'baru saja';
            if (diffMins < 60) return `${diffMins} menit lalu`;
            if (diffHours < 24) return `${diffHours} jam lalu`;
            if (diffDays < 7) return `${diffDays} hari lalu`;
            if (diffWeeks < 4) return `${diffWeeks} minggu lalu`;

            return formatDate(date);
        }

        // Render Pagination
        function renderPagination(data) {
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous Button
            if (data.prev_page_url) {
                const prevBtn = createPaginationButton('← Sebelumnya', () => {
                    currentPage--;
                    loadPosts();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                paginationContainer.appendChild(prevBtn);
            }

            // Page Info
            const pageInfo = document.createElement('span');
            pageInfo.className = 'text-sm text-gray-600 px-2';
            pageInfo.textContent = `Halaman ${data.current_page} dari ${data.last_page}`;
            paginationContainer.appendChild(pageInfo);

            // Next Button
            if (data.next_page_url) {
                const nextBtn = createPaginationButton('Selanjutnya →', () => {
                    currentPage++;
                    loadPosts();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
                paginationContainer.appendChild(nextBtn);
            }
        }

        // Create Pagination Button
        function createPaginationButton(text, onclick) {
            const btn = document.createElement('button');
            btn.textContent = text;
            btn.className = 'px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition';
            btn.onclick = onclick;
            return btn;
        }

        // Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Show Loading
        function showLoading(show) {
            if (show) {
                loadingSpinner.classList.remove('hidden');
                postsContainer.classList.add('opacity-50', 'pointer-events-none');
            } else {
                loadingSpinner.classList.add('hidden');
                postsContainer.classList.remove('opacity-50', 'pointer-events-none');
            }
        }

        // Show Error
        function showError(message) {
            errorText.textContent = message;
            errorMessage.classList.remove('hidden');
        }

        // Hide Error
        function hideError() {
            errorMessage.classList.add('hidden');
        }
    </script>
</body>

</html>