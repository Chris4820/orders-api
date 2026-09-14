<script setup lang="ts">
import { ref } from "vue";
import { useGetProducts } from "../queries/products.js";
import { toast } from "vue-sonner";
import ProductListSection from "../section/ProductListSection.vue";
import CartSection from "../section/CartSection.vue";

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

const { data: products, isPending, isError } = useGetProducts();

const cart = ref<CartItem[]>([]);

function addToCart(product: Product) {
    if (product.stock <= 0) {
        return;
    }

    const item = cart.value.find((item) => item.product.id === product.id);

    if (!item) {
        toast.success(`${product.name} adicionado ao carrinho!`);
        return cart.value.push({
            product,
            quantity: 1,
        });
    }
}

function removeFromCart(product: Product) {
    const item = cart.value.find((item) => item.product.id === product.id);

    if (item) {
        toast.success(`${product.name} removido do carrinho!`);
        cart.value = cart.value.filter(
            (item) => item.product.id !== product.id,
        );
    }
}

function increaseQuantity(productId: number) {
    const item = cart.value.find((item) => item.product.id === productId);

    if (!item) {
        return;
    }

    if (item.quantity < item.product.stock) {
        item.quantity++;
    }
}

function decreaseQuantity(productId: number) {
    const item = cart.value.find((item) => item.product.id === productId);

    if (!item) {
        return;
    }

    if (item.quantity > 1) {
        item.quantity--;
    }
}
</script>

<template>
    <main class="container mx-auto p-6">
        <h1 class="mb-6 text-3xl font-bold">Produtos</h1>

        <!-- Produtos -->
        <template v-if="isError">
            <h1>Ocorreu um erro ao puxar os produtos!</h1>
        </template>
        <ProductListSection
            v-else
            :is-pending="isPending"
            :cart="cart"
            :products="products"
            @add-to-cart="addToCart"
            @remove-from-cart="removeFromCart"
        />

        <CartSection
            :cart="cart"
            @remove-from-cart="removeFromCart"
            @increaseQuantity="increaseQuantity"
            @decreaseQuantity="decreaseQuantity"
        />
    </main>
</template>
