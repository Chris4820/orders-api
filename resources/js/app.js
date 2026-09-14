import { createApp } from "vue";
import App from "./App.vue";

import { QueryClient, VueQueryPlugin } from "@tanstack/vue-query";

const queryClient = new QueryClient();

createApp(App).use(VueQueryPlugin, { queryClient }).mount("#app");
