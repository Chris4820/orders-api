import { api } from "./client";

export const ordersApi = {
    async createOrder(order: {
        customer_name: string;
        customer_email: string;
        products: {
            product_id: number;
            quantity: number;
        }[];
    }) {
        const response = await api.post("/orders", order);

        return response.data;
    },
};
