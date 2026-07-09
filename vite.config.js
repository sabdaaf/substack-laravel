import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite"; // JANGAN DIHAPUS

export default defineConfig({
    plugins: [
        laravel({
            // Tambahkan file JS baru di sini agar ikut dikompilasi oleh Vite
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/auth.js",
                "resources/js/dashboard.js",
            ],
            refresh: true,
        }),
        tailwindcss(), // JANGAN DIHAPUS
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
        proxy: {
            "/api": {
                target: "http://127.0.0.1:8000",
                changeOrigin: true,
            },
        },
    },
});
