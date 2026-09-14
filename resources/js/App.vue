<script>
import { Toaster } from "vue-sonner";
import "vue-sonner/style.css";
import ProductsView from "./pages/ProductsView.vue";

export default {
    name: "app",
    components: {
        Toaster,
        ProductsView,
    },
};
</script>

<template>
    <Toaster
        position="bottom-right"
        :duration="3000"
        rich-colors
        close-button
    />
    <ProductsView />
</template>
