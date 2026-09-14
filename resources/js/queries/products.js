import { useQuery } from "@tanstack/vue-query";
import { productsApi } from "../api/products";

export function useGetProducts() {
    return useQuery({
        queryKey: ["products"],
        queryFn: productsApi.getProducts,
    });
}
