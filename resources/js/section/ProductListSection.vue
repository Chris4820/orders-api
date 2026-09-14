<script setup lang="ts">
import ProductCard from "../components/ProductCard.vue";
import ProductSkeleton from "../components/ProductSkeleton.vue";

type Product = {
    id: number;
    name: string;
    price: number;
    stock: number;
};

type CartItem = {
    product: Product;
    quantity: number;
};

const props = defineProps<{
    isPending: boolean;
    cart: CartItem[];
    products: Product[];
}>();

const emit = defineEmits<{
    addToCart: [product: Product];
    removeFromCart: [product: Product];
}>();

const isInCart = (product: Product) => {
    return props.cart.some((item) => item.product.id === product.id);
};
</script>

<template>
    <ul class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Loading -->
        <template v-if="props.isPending">
            <ProductSkeleton v-for="i in 5" :key="i" />
        </template>

        <template v-else-if="props.products.length <= 0">
            <h1>Não existem produtos no momento!</h1>
        </template>
        <!-- Produtos -->
        <template v-else>
            <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                :is-in-cart="isInCart(product)"
                @add-to-cart="emit('addToCart', $event)"
                @remove-from-cart="emit('removeFromCart', $event)"
            />
        </template>
    </ul>
</template>
