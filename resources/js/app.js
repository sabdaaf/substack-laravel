import "./bootstrap";
import axios from "axios";

// Daftarkan Axios ke window agar bisa dipakai di file JS lain
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/api"; // Mempersingkat URL API

// Interceptor: Otomatis tempelkan Token Sanctum jika ada di localStorage
window.axios.interceptors.request.use(
    function (config) {
        const token = localStorage.getItem("api_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    function (error) {
        return Promise.reject(error);
    },
);

window.addEventListener("DOMContentLoaded", () => {
    const app = document.getElementById("app");
    const postsStatus = document.getElementById("posts-status");
    const postsList = document.getElementById("posts-list");

    if (!app || !postsStatus || !postsList) {
        return;
    }

    postsStatus.textContent = "Memuat data dari backend...";

    window.axios
        .get("/posts")
        .then(({ data }) => {
            const posts = Array.isArray(data) ? data : data.data || [];

            if (!posts.length) {
                postsStatus.textContent = "Belum ada post yang tersedia.";
                postsList.innerHTML =
                    '<p class="text-sm text-slate-500">Tambahkan post lewat API untuk melihat hasilnya di sini.</p>';
                return;
            }

            postsStatus.textContent = `Berhasil terhubung ke backend. ${posts.length} post ditampilkan.`;
            postsList.innerHTML = posts
                .map(
                    (post) => `
                <article class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <h2 class="font-semibold text-slate-800">${post.title || "Tanpa judul"}</h2>
                    <p class="mt-2 text-sm text-slate-600">${post.body ? post.body.substring(0, 140) : "Tidak ada konten."}</p>
                    <p class="mt-3 text-xs uppercase tracking-wide text-slate-400">Slug: ${post.slug || "-"}</p>
                </article>
            `,
                )
                .join("");
        })
        .catch((error) => {
            console.error(error);
            postsStatus.textContent = "Gagal terhubung ke backend.";
            postsList.innerHTML =
                '<p class="text-sm text-red-600">Pastikan Laravel berjalan di port 8000.</p>';
        });
});
