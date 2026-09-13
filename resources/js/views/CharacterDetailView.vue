<script setup lang="ts">
defineProps<{ id: string }>();
</script>

<template>
    <v-container>
        <h1 class="text-h4 mb-4">Character detail</h1>
        <p class="text-body-1">Phase 8 will show character #{{ id }} with its episodes and locations.</p>
    </v-container>
</template>
