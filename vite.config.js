import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/scss/style.scss",
                "resources/js/app.js",
                "resources/js/main.js",
                "resources/js/validate.js",
            ],
            refresh: true,
        }),
    ],
});
