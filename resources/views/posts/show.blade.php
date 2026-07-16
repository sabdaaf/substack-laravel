<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} - Substack News</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                        <a href="/" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Beranda</a>
                        <a href="/dashboard"
                            class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Article Container -->
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <div class="mb-8">
            <a href="/" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <!-- Article Header -->
        <header class="mb-8 pb-8 border-b border-gray-200">
            <h1 class="text-5xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>

            <!-- Meta Information -->
            <div class="flex flex-col gap-4 text-gray-600">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="font-medium text-gray-900">{{ $post->author->name }}</p>
                        <p class="text-sm text-gray-500">{{ $post->author->email }}</p>
                    </div>
                </div>

                <div class="text-sm">
                    <p class="text-gray-500">
                        Dipublikasikan pada {{ $post->created_at->format('d M Y') }} pukul
                        {{ $post->created_at->format('H:i') }}
                    </p>
                    @if ($post->updated_at->ne($post->created_at))
                        <p class="text-gray-500">
                            Diperbarui pada {{ $post->updated_at->format('d M Y') }} pukul
                            {{ $post->updated_at->format('H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </header>

        <!-- Article Content -->
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            {!! $post->body !!}
        </div>

        <!-- Footer -->
        <footer class="mt-12 pt-8 border-t border-gray-200">
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Tentang Penulis</h3>
                <p class="text-gray-600 mb-4">
                    <strong>{{ $post->author->name }}</strong>
                </p>
                <p class="text-gray-600">{{ $post->author->email }}</p>
            </div>
        </footer>

        <!-- Related Articles (Optional) -->
        <section class="mt-12 pt-8 border-t border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Artikel Lainnya</h3>
            <div id="relatedArticles" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Related articles will be loaded here -->
            </div>
        </section>
    </article>

    <script>
        // Load related articles (excluding current post)
        const currentSlug = '{{ $post->slug }}';

        async function loadRelatedArticles() {
            try {
                const response = await axios.get('/posts', {
                    params: {
                        per_page: 4,
                        sort_by: 'created_at',
                        order: 'desc'
                    }
                });

                const relatedArticles = response.data.data.filter(post => post.slug !== currentSlug).slice(0, 2);
                const container = document.getElementById('relatedArticles');

                if (relatedArticles.length === 0) {
                    container.innerHTML = '<p class="text-gray-500">Tidak ada artikel lain yang tersedia</p>';
                    return;
                }

                container.innerHTML = relatedArticles.map(post => `
                    <a href="/posts/${post.slug}" class="block group">
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                            <h4 class="font-bold text-gray-900 group-hover:text-blue-600 transition line-clamp-2 mb-2">
                                ${escapeHtml(post.title)}
                            </h4>
                            <p class="text-sm text-gray-600 line-clamp-2">
                                ${escapeHtml(post.body.substring(0, 100))}...
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                ${escapeHtml(post.author.name)} • ${formatDate(new Date(post.created_at))}
                            </p>
                        </div>
                    </a>
                `).join('');
            } catch (error) {
                console.error('Error loading related articles:', error);
            }
        }

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

        function formatDate(date) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }).format(date);
        }

        document.addEventListener('DOMContentLoaded', loadRelatedArticles);
    </script>
</body>

</html>