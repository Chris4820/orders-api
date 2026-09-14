<script setup lang="ts">
type Product = {
    id: number;
    name: string;
    price: number;
    stock: number;
};

const props = defineProps<{
    product: Product;
    isInCart: boolean;
}>();

const emit = defineEmits<{
    addToCart: [product: Product];
    removeFromCart: [product: Product];
}>();
</script>

<template>
    <li class="rounded-lg border bg-muted p-4">
        <h2 class="text-lg font-semibold">
            {{ product.name }}
        </h2>

        <p class="mt-2">{{ product.price.toFixed(2) }} €</p>

        <p class="mt-1 text-sm text-gray-500">Stock: {{ product.stock }}</p>

        <button
            class="mt-4 cursor-pointer rounded-lg px-4 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50"
            :class="isInCart ? 'bg-red-600' : 'bg-black'"
            :disabled="!isInCart && product.stock <= 0"
            @click="
                isInCart
                    ? emit('removeFromCart', product)
                    : emit('addToCart', product)
            "
        >
            {{
                isInCart
                    ? "Remover do carrinho"
                    : product.stock > 0
                      ? "Adicionar ao carrinho"
                      : "Sem stock"
            }}
        </button>
    </li>
</template>
