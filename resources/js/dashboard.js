import "./bootstrap";
import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/api";

if (!window.__dashboardAxiosInterceptorInstalled) {
    window.axios.interceptors.request.use(
        (config) => {
            const token = localStorage.getItem("api_token");
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        },
        (error) => Promise.reject(error),
    );

    window.__dashboardAxiosInterceptorInstalled = true;
}

const state = {
    currentPage: 1,
    searchQuery: "",
    sort: "latest",
};

const searchInput = document.getElementById("search-input");
const searchButton = document.getElementById("search-button");
const filterButtons = document.querySelectorAll("[data-sort]");
const tableBody = document.getElementById("posts-table-body");
const emptyState = document.getElementById("empty-state");
const paginationContainer = document.getElementById("pagination-container");
const modal = document.getElementById("article-modal");
const modalTitle = document.getElementById("modal-title");
const articleForm = document.getElementById("article-form");
const articleTitleInput = document.getElementById("article-title");
const articleSlugInput = document.getElementById("article-slug-input");
const articleBodyInput = document.getElementById("article-body");
const articleSlugHidden = document.getElementById("article-slug");
const formError = document.getElementById("form-error");
const openCreateModalButton = document.getElementById("open-create-modal");
const closeModalButton = document.getElementById("close-modal");
const cancelModalButton = document.getElementById("cancel-modal");

function getSortConfig(sort) {
    switch (sort) {
        case "oldest":
            return { sort_by: "created_at", order: "asc" };
        case "az":
            return { sort_by: "title", order: "asc" };
        case "za":
            return { sort_by: "title", order: "desc" };
        case "latest":
        default:
            return { sort_by: "created_at", order: "desc" };
    }
}

function formatDate(value) {
    if (!value) {
        return "-";
    }

    return new Date(value).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

function renderPosts(posts) {
    if (!posts.length) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500">
                    Tidak ada artikel yang ditemukan.
                </td>
            </tr>
        `;
        emptyState.classList.remove("hidden");
        return;
    }

    emptyState.classList.add("hidden");
    tableBody.innerHTML = posts
        .map((post) => {
            const title = post.title || "Tanpa judul";
            const slug = post.slug || "";
            const createdAt = formatDate(post.created_at);

            return `
                <tr class="border-b border-slate-800/80 text-sm text-slate-300">
                    <td class="px-4 py-4">${title}</td>
                    <td class="px-4 py-4">${createdAt}</td>
                    <td class="px-4 py-4">
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-edit-slug="${slug}" class="rounded-lg border border-sky-500/30 bg-sky-500/10 px-3 py-1.5 text-xs font-semibold text-sky-300 transition hover:bg-sky-500/20">
                                Edit
                            </button>
                            <button type="button" data-delete-slug="${slug}" class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 transition hover:bg-rose-500/20">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        })
        .join("");
}

function renderPagination(meta) {
    if (!meta || !meta.last_page || meta.last_page <= 1) {
        paginationContainer.innerHTML = "";
        return;
    }

    const pages = [];
    for (let page = 1; page <= meta.last_page; page += 1) {
        pages.push(`
            <button
                type="button"
                data-page="${page}"
                class="rounded-lg border px-3 py-2 text-sm ${page === meta.current_page ? "border-sky-500 bg-sky-500 text-white" : "border-slate-700 bg-slate-900 text-slate-300 hover:border-sky-500/50 hover:text-sky-300"}"
            >
                ${page}
            </button>
        `);
    }

    paginationContainer.innerHTML = `
        <div class="flex flex-wrap items-center justify-center gap-2">
            ${pages.join("")}
        </div>
    `;
}

window.fetchPosts = async function fetchPosts(
    page = 1,
    searchQuery = "",
    sort = "latest",
) {
    state.currentPage = page;
    state.searchQuery = searchQuery;
    state.sort = sort;

    const sortConfig = getSortConfig(sort);

    tableBody.innerHTML = `
        <tr>
            <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500">
                Memuat artikel...
            </td>
        </tr>
    `;

    try {
        const response = await window.axios.get("/posts", {
            params: {
                page,
                search: searchQuery,
                sort,
                sort_by: sortConfig.sort_by,
                order: sortConfig.order,
            },
        });

        const posts = Array.isArray(response.data?.data)
            ? response.data.data
            : [];
        renderPosts(posts);
        renderPagination(response.data);
    } catch (error) {
        console.error(error);

        if (error.response?.status === 401) {
            localStorage.removeItem("api_token");
            window.location.href = "/login";
            return;
        }

        tableBody.innerHTML = `
            <tr>
                <td colspan="3" class="px-4 py-10 text-center text-sm text-rose-400">
                    Gagal memuat artikel dari API.
                </td>
            </tr>
        `;
    }
};

window.deletePost = async function deletePost(slug) {
    if (!slug) {
        return;
    }

    const confirmed = window.confirm(
        "Apakah Anda yakin ingin menghapus artikel ini?",
    );
    if (!confirmed) {
        return;
    }

    try {
        await window.axios.delete(`/posts/${slug}`);
        await window.fetchPosts(
            state.currentPage,
            state.searchQuery,
            state.sort,
        );
    } catch (error) {
        console.error(error);
        window.alert(
            error.response?.data?.message || "Gagal menghapus artikel.",
        );
    }
};

function setActiveSortButton(activeSort) {
    filterButtons.forEach((button) => {
        const isActive = button.dataset.sort === activeSort;
        button.classList.toggle("bg-sky-500", isActive);
        button.classList.toggle("text-white", isActive);
        button.classList.toggle("border-sky-500", isActive);
        button.classList.toggle("text-slate-300", !isActive);
        button.classList.toggle("border-slate-700", !isActive);
    });
}

function resetForm() {
    articleForm.reset();
    articleSlugHidden.value = "";
    formError.classList.add("hidden");
    formError.innerText = "";
    modalTitle.textContent = "Buat Artikel Baru";
}

function openModal(mode = "create", post = null) {
    resetForm();

    if (mode === "edit" && post) {
        modalTitle.textContent = "Edit Artikel";
        articleSlugHidden.value = post.slug || "";
        articleTitleInput.value = post.title || "";
        articleSlugInput.value = post.slug || "";
        articleBodyInput.value = post.body || "";
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    articleTitleInput?.focus();
}

function closeModal() {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    resetForm();
}

async function handleSubmit(event) {
    event.preventDefault();
    formError.classList.add("hidden");
    formError.innerText = "";

    const payload = {
        title: articleTitleInput.value.trim(),
        body: articleBodyInput.value.trim(),
    };

    const customSlug = articleSlugInput.value.trim();
    if (customSlug) {
        payload.slug = customSlug;
    }

    try {
        if (articleSlugHidden.value) {
            await window.axios.put(
                `/posts/${articleSlugHidden.value}`,
                payload,
            );
        } else {
            await window.axios.post("/posts", payload);
        }

        closeModal();
        await window.fetchPosts(
            state.currentPage,
            state.searchQuery,
            state.sort,
        );
    } catch (error) {
        console.error(error);
        const messages = error.response?.data?.errors;

        if (messages) {
            const firstError = Object.values(messages)[0]?.[0];
            formError.innerText = firstError || "Gagal menyimpan artikel.";
        } else {
            formError.innerText =
                error.response?.data?.message || "Gagal menyimpan artikel.";
        }

        formError.classList.remove("hidden");
    }
}

function bindEvents() {
    if (searchInput) {
        searchInput.addEventListener("input", (event) => {
            window.fetchPosts(1, event.target.value.trim(), state.sort);
        });
    }

    if (searchButton) {
        searchButton.addEventListener("click", () => {
            window.fetchPosts(1, searchInput?.value.trim() || "", state.sort);
        });
    }

    filterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const nextSort = button.dataset.sort || "latest";
            setActiveSortButton(nextSort);
            window.fetchPosts(1, searchInput?.value.trim() || "", nextSort);
        });
    });

    openCreateModalButton?.addEventListener("click", () => openModal("create"));
    closeModalButton?.addEventListener("click", closeModal);
    cancelModalButton?.addEventListener("click", closeModal);
    modal?.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
    articleForm?.addEventListener("submit", handleSubmit);

    document.addEventListener("click", async (event) => {
        const deleteButton = event.target.closest("[data-delete-slug]");
        if (deleteButton) {
            const slug = deleteButton.getAttribute("data-delete-slug");
            window.deletePost(slug);
            return;
        }

        const editButton = event.target.closest("[data-edit-slug]");
        if (editButton) {
            const slug = editButton.getAttribute("data-edit-slug");
            try {
                const response = await window.axios.get(`/posts/${slug}`);
                openModal("edit", response.data?.post || null);
            } catch (error) {
                console.error(error);
                window.alert("Gagal memuat data artikel untuk diedit.");
            }
        }
    });

    paginationContainer?.addEventListener("click", (event) => {
        const targetButton = event.target.closest("[data-page]");
        if (targetButton) {
            window.fetchPosts(
                Number(targetButton.dataset.page),
                searchInput?.value.trim() || "",
                state.sort,
            );
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    bindEvents();
    setActiveSortButton(state.sort);
    window.fetchPosts(1, "", state.sort);
});
