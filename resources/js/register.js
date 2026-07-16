import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/api";

const form = document.getElementById("form-register");
const errorBox = document.getElementById("error-message");

if (form) {
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // name & email di-cast eksplisit ke string murni (trim) supaya tidak
        // memicu error "the name field must be a string" pada RegisterRequest.
        const payload = {
            name: String(document.getElementById("name").value ?? "").trim(),
            email: String(document.getElementById("email").value ?? "").trim(),
            password: document.getElementById("password").value,
            password_confirmation: document.getElementById(
                "password_confirmation",
            ).value,
            device_name: "foogu-news-web",
        };

        errorBox.classList.add("hidden");
        errorBox.innerText = "";

        try {
            // Tidak ada withCredentials / GET /sanctum/csrf-cookie di sini.
            // Register + login keduanya memakai Sanctum Bearer token murni.
            const registerResponse = await window.axios.post(
                "/register",
                payload,
            );

            const loginResponse = await window.axios.post("/login", {
                email: payload.email,
                password: payload.password,
                device_name: payload.device_name,
            });

            const token = loginResponse.data.token;
            localStorage.setItem("api_token", token);

            window.location.href = "/dashboard";
        } catch (error) {
            errorBox.classList.remove("hidden");

            if (error.response?.status === 429) {
                errorBox.innerText =
                    "Terlalu banyak percobaan. Silakan tunggu sebentar.";
            } else if (error.response?.data?.errors) {
                const firstError = Object.values(
                    error.response.data.errors,
                )[0]?.[0];
                errorBox.innerText =
                    firstError || "Registrasi gagal. Periksa data Anda.";
            } else {
                errorBox.innerText =
                    error.response?.data?.message || "Registrasi gagal.";
            }
        }
    });
}
