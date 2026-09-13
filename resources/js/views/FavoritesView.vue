<script setup lang="ts">
import { onMounted } from 'vue';
import { useFavoritesStore } from '@/stores/favorites';

const favoritesStore = useFavoritesStore();

onMounted(() => {
    favoritesStore.fetch();
});
</script>

<template>
    <v-container>
        <h1 class="text-h4 mb-4">My favorites</h1>

        <v-row v-if="favoritesStore.loading">
            <v-col v-for="n in 3" :key="n" cols="12" sm="6" md="4">
                <v-skeleton-loader type="card" />
            </v-col>
        </v-row>

        <v-alert v-else-if="favoritesStore.items.length === 0" type="info">
            You haven't added any favorite characters yet.
        </v-alert>

        <v-row v-else>
            <v-col v-for="character in favoritesStore.items" :key="character.id" cols="12" sm="6" md="4">
                <v-card>
                    <v-img :src="character.image" height="200" cover />
                    <v-card-title>{{ character.name }}</v-card-title>
                    <v-card-subtitle>{{ character.species }} · {{ character.status }}</v-card-subtitle>
                    <v-card-actions>
                        <v-btn :to="{ name: 'characters.show', params: { id: character.id } }" variant="text">
                            Details
                        </v-btn>
                        <v-spacer />
                        <v-btn icon="mdi-heart" color="red" variant="text" @click="favoritesStore.remove(character.id)" />
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
