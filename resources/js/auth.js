import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/api";

const form = document.getElementById("form-login");
const errorBox = document.getElementById("error-message");

if (form) {
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const name = document.getElementById("name").value.trim();
        const password = document.getElementById("password").value;

        try {
            errorBox.classList.add("hidden");
            errorBox.innerText = "";

            const response = await window.axios.post("/login", {
                name,
                password,
                device_name: "browser",
            });

            const token = response.data.token;
            localStorage.setItem("api_token", token);

            window.location.href = "/dashboard";
        } catch (error) {
            errorBox.classList.remove("hidden");

            if (error.response && error.response.status === 429) {
                errorBox.innerText =
                    "Terlalu banyak percobaan login. Silakan tunggu sebentar.";
            } else if (error.response?.data?.errors) {
                const firstError = Object.values(
                    error.response.data.errors,
                )[0]?.[0];
                errorBox.innerText =
                    firstError ||
                    "Login gagal. Periksa nama dan password Anda.";
            } else {
                errorBox.innerText =
                    error.response?.data?.message ||
                    "Login gagal. Periksa nama dan password Anda.";
            }
        }
    });
}
