import { api } from "./client";

export const productsApi = {
    async getProducts() {
        const response = await api.get("/products");

        return response.data;
    },
};
