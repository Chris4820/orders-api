<script setup lang="ts">
import { computed } from "vue";

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
    cart: CartItem[];
}>();

const emit = defineEmits<{
    removeFromCart: [product: Product];
    increaseQuantity: [productId: number];
    decreaseQuantity: [productId: number];
}>();

const cartTotal = computed(() => {
    return props.cart.reduce((total, item) => {
        return total + item.product.price * item.quantity;
    }, 0);
});

const cartItemsCount = computed(() => {
    return props.cart.reduce((total, item) => {
        return total + item.quantity;
    }, 0);
});
</script>

<template>
    <section
        v-if="cartItemsCount <= 0"
        class="mt-10 rounded-lg border p-6 text-center"
    >
        <p>O carrinho está vazio.</p>
    </section>

    <section v-else class="mt-10 rounded-lg border p-6">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold">Carrinho</h2>

            <span>
                {{ cartItemsCount }}
                {{ cartItemsCount === 1 ? "item" : "itens" }}
            </span>
        </div>

        <!-- Items -->
        <div class="space-y-4">
            <article
                v-for="item in props.cart"
                :key="item.product.id"
                class="flex items-center justify-between border-b pb-4"
            >
                <div>
                    <h3 class="font-semibold">
                        {{ item.product.name }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        {{ item.product.price.toFixed(2) }} € / unidade
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Diminuir -->
                    <button
                        class="cursor-pointer rounded border px-3 py-1"
                        :disabled="item.quantity <= 1"
                        @click="emit('decreaseQuantity', item.product.id)"
                    >
                        -
                    </button>

                    <!-- Quantidade -->
                    <span class="min-w-6 text-center">
                        {{ item.quantity }}
                    </span>

                    <!-- Aumentar -->
                    <button
                        class="cursor-pointer rounded border px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="item.quantity >= item.product.stock"
                        @click="emit('increaseQuantity', item.product.id)"
                    >
                        +
                    </button>

                    <!-- Remover -->
                    <button
                        class="ml-3 cursor-pointer rounded bg-red-500 px-3 py-1 text-white"
                        @click="emit('removeFromCart', item.product)"
                    >
                        Remover
                    </button>
                </div>
            </article>
        </div>

        <!-- Total -->
        <div class="mt-6 flex items-center justify-between">
            <span class="text-xl font-bold">Total</span>

            <span class="text-xl font-bold">
                {{ cartTotal.toFixed(2) }} €
            </span>
        </div>

        <!-- Checkout -->
        <button
            class="mt-6 w-full cursor-pointer rounded-lg bg-black px-4 py-3 text-white"
        >
            Finalizar encomenda
        </button>
    </section>
</template>
