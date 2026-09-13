<script setup lang="ts">
import { onMounted } from 'vue';
import { useCharacterList } from '@/composables/useCharacterList';
import { useFavoriteToggle } from '@/composables/useFavoriteToggle';
import { useAuthStore } from '@/stores/auth';
import { useFavoritesStore } from '@/stores/favorites';

const { name, status, species, gender, page, characters, meta, loading, errorMessage } = useCharacterList();
const { isFavorite, toggle, canToggle } = useFavoriteToggle();
const authStore = useAuthStore();
const favoritesStore = useFavoritesStore();

const statusOptions = ['Alive', 'Dead', 'unknown'];
const genderOptions = ['Male', 'Female', 'Genderless', 'unknown'];

onMounted(() => {
    if (authStore.isAuthenticated) {
        favoritesStore.fetch();
    }
});
</script>

<template>
    <v-container>
        <h1 class="text-h4 mb-4">Characters</h1>

        <v-row class="mb-2">
            <v-col cols="12" sm="3">
                <v-text-field v-model="name" label="Name" density="compact" clearable />
            </v-col>
            <v-col cols="12" sm="3">
                <v-select v-model="status" :items="statusOptions" label="Status" density="compact" clearable />
            </v-col>
            <v-col cols="12" sm="3">
                <v-text-field v-model="species" label="Species" density="compact" clearable />
            </v-col>
            <v-col cols="12" sm="3">
                <v-select v-model="gender" :items="genderOptions" label="Gender" density="compact" clearable />
            </v-col>
        </v-row>

        <v-alert v-if="errorMessage" type="error" class="mb-4">{{ errorMessage }}</v-alert>

        <v-row v-if="loading">
            <v-col v-for="n in 6" :key="n" cols="12" sm="6" md="4">
                <v-skeleton-loader type="card" />
            </v-col>
        </v-row>

        <v-alert v-else-if="characters.length === 0" type="info">No characters match these filters.</v-alert>

        <v-row v-else>
            <v-col v-for="character in characters" :key="character.id" cols="12" sm="6" md="4">
                <v-card>
                    <v-img :src="character.image" height="200" cover />
                    <v-card-title>{{ character.name }}</v-card-title>
                    <v-card-subtitle>{{ character.species }} · {{ character.status }}</v-card-subtitle>
                    <v-card-actions>
                        <v-btn :to="{ name: 'characters.show', params: { id: character.id } }" variant="text">
                            Details
                        </v-btn>
                        <v-spacer />
                        <v-btn
                            v-if="canToggle()"
                            :icon="isFavorite(character.id) ? 'mdi-heart' : 'mdi-heart-outline'"
                            :color="isFavorite(character.id) ? 'red' : undefined"
                            variant="text"
                            @click="toggle(character)"
                        />
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>

        <div v-if="meta && meta.last_page > 1" class="d-flex justify-center mt-4">
            <v-pagination v-model="page" :length="meta.last_page" />
        </div>
    </v-container>
</template>
