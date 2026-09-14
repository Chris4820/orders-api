import { useMutation } from "@tanstack/vue-query";
import { ordersApi } from "../api/order";

interface CreateOrderProps {
    onSuccess: () => void;
}
export function useCreateOrder(props: CreateOrderProps) {
    return useMutation({
        mutationFn: ordersApi.createOrder,
        onSuccess() {
            props.onSuccess();
        },
    });
}
