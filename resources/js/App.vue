<script setup lang="ts">
import { useAuthStore } from "./stores/auth";
import { computed } from "vue";
import { NavItem } from "./layouts/AppLayout.vue";
import AppLayout from "./layouts/AppLayout.vue";
import { useRouter } from "vue-router";

const router = useRouter();
const authStore = useAuthStore();

const navItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { label: "Characters", to: { name: "characters.index" } },
    ];

    if (authStore.isAuthenticated) {
        items.push({ label: "Favorites", to: { name: "favorites.index" } });
    }

    return items;
});

async function logout() {
    await authStore.logout();
    router.push({ name: "login" });
}
</script>

<template>
    <AppLayout title="Rick and Morty App" :nav-items="navItems">
        <template #actions>
            <template v-if="authStore.isAuthenticated">
                <span class="mr-2">{{ authStore.user?.name }}</span>
                <v-btn variant="text" @click="logout">Log out</v-btn>
            </template>
            <template v-else>
                <v-btn :to="{ name: 'login' }" variant="text">Log in</v-btn>
                <v-btn :to="{ name: 'register' }" variant="text"
                    >Register</v-btn
                >
            </template>
        </template>

        <router-view />
    </AppLayout>
</template>
