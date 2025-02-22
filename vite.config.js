import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "public/assets/css/nucleo-icons.css",
                "public/assets/css/nucleo-svg.css",
                "public/assets/css/soft-ui-dashboard.css",
                "public/assets/js/core/popper.min.js",
                "public/assets/js/core/bootstrap.min.js",
                "public/assets/js/plugins/perfect-scrollbar.min.js",
                "public/assets/js/plugins/smooth-scrollbar.min.js",
                "public/assets/js/plugins/fullcalendar.min.js",
                "public/assets/js/plugins/chartjs.min.js",
                "public/assets/js/soft-ui-dashboard.min.js",
                //'resources/js/extractFields.js',
                //'resources/js/fillPDF.js'
            ],
            refresh: true,
        }),
    ],
});
