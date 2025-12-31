import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import Components from "unplugin-vue-components/vite";
import { AntDesignVueResolver } from "unplugin-vue-components/resolvers";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/app.js"],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        Components({
            resolvers: [
                AntDesignVueResolver({
                    importStyle: false, // css in js
                }),
            ],
        }),
    ],
    // build: {
    //     // chunkSizeWarningLimit: 900000,
    //     rollupOptions: {
    //         output: {
    //             entryFileNames: `assets/[name].js`,
    //             chunkFileNames: function (file) {
    //                 return `assets/[name].js`;
    //             },
    //             assetFileNames: (assetInfo) => {
    //                 return `assets/[name][extname]`;
    //             },
    //             manualChunks: {
    //                 ant_design_vue: ["ant-design-vue"],
    //             },
    //         },
    //     },
    // },
    server: {
        hmr: {
            host: "localhost",
        },
    },
});
