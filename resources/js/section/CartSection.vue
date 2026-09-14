<script setup lang="ts">
import { computed } from "vue";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import { z } from "zod";

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
    isPending: boolean;
}>();

const emit = defineEmits<{
    removeFromCart: [product: Product];
    increaseQuantity: [productId: number];
    decreaseQuantity: [productId: number];
    checkout: [customer_name: string, customer_email: string];
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

/*
|--------------------------------------------------------------------------
| Checkout form
|--------------------------------------------------------------------------
*/

const checkoutSchema = toTypedSchema(
    z.object({
        customer_name: z
            .string()
            .trim()
            .min(2, "O nome deve ter pelo menos 2 caracteres")
            .max(255, "O nome não pode ter mais de 255 caracteres"),

        customer_email: z
            .string()
            .trim()
            .email("Introduz um email válido")
            .max(255, "O email não pode ter mais de 255 caracteres"),
    }),
);

const { defineField, handleSubmit, errors } = useForm({
    validationSchema: checkoutSchema,
});

const [customerName] = defineField("customer_name");
const [customerEmail] = defineField("customer_email");

const onSubmit = handleSubmit((values) => {
    emit("checkout", values.customer_name, values.customer_email);
});
</script>

<template>
    <!-- Carrinho vazio -->
    <section
        v-if="cartItemsCount <= 0"
        class="mt-10 rounded-lg border p-6 text-center"
    >
        <p>O carrinho está vazio.</p>
    </section>

    <!-- Carrinho -->
    <section v-else class="mt-10 rounded-lg border p-6">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold">Carrinho</h2>

            <span>
                {{ cartItemsCount }}
                {{ cartItemsCount === 1 ? "item" : "itens" }}
            </span>
        </div>

        <!-- Produtos -->
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
                        type="button"
                        class="cursor-pointer rounded border px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="item.quantity <= 1"
                        @click="emit('decreaseQuantity', item.product.id)"
                    >
                        -
                    </button>

                    <span class="min-w-6 text-center">
                        {{ item.quantity }}
                    </span>

                    <!-- Aumentar -->
                    <button
                        type="button"
                        class="cursor-pointer rounded border px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="item.quantity >= item.product.stock"
                        @click="emit('increaseQuantity', item.product.id)"
                    >
                        +
                    </button>

                    <!-- Remover -->
                    <button
                        type="button"
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
            <span class="text-xl font-bold"> Total </span>

            <span class="text-xl font-bold">
                {{ cartTotal.toFixed(2) }} €
            </span>
        </div>

        <!-- Checkout -->
        <form class="mt-6 space-y-4" @submit="onSubmit">
            <!-- Nome -->
            <div>
                <label for="customer_name" class="mb-1 block font-medium">
                    Nome
                </label>

                <input
                    id="customer_name"
                    v-model="customerName"
                    type="text"
                    autocomplete="name"
                    placeholder="O teu nome"
                    class="w-full rounded-lg border px-4 py-2"
                    :disabled="props.isPending"
                />

                <p
                    v-if="errors.customer_name"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ errors.customer_name }}
                </p>
            </div>

            <!-- Email -->
            <div>
                <label for="customer_email" class="mb-1 block font-medium">
                    Email
                </label>

                <input
                    id="customer_email"
                    v-model="customerEmail"
                    type="email"
                    autocomplete="email"
                    placeholder="o-teu@email.com"
                    class="w-full rounded-lg border px-4 py-2"
                    :disabled="props.isPending"
                />

                <p
                    v-if="errors.customer_email"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ errors.customer_email }}
                </p>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="w-full cursor-pointer rounded-lg bg-black px-4 py-3 text-white disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="props.isPending"
            >
                <span v-if="props.isPending"> A processar encomenda... </span>

                <span v-else> Finalizar encomenda </span>
            </button>
        </form>
    </section>
</template>
