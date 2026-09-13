<script setup lang="ts">
export interface NavItem {
    label: string;
    to: string | { name: string };
}

withDefaults(
    defineProps<{
        title?: string;
        navItems?: NavItem[];
    }>(),
    {
        title: 'App',
        navItems: () => [],
    },
);
</script>

<template>
    <v-app>
        <v-app-bar>
            <v-app-bar-title>
                <slot name="brand">
                    <router-link to="/" class="text-decoration-none text-white">{{ title }}</router-link>
                </slot>
            </v-app-bar-title>

            <v-btn v-for="item in navItems" :key="item.label" :to="item.to" variant="text">
                {{ item.label }}
            </v-btn>

            <v-spacer />

            <slot name="actions" />
        </v-app-bar>

        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
