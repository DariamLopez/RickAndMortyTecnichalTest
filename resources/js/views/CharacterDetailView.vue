<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import { characterRepository } from "@/repositories/characterRepository";
import { useFavoriteToggle } from "@/composables/useFavoriteToggle";
import { useAuthStore } from "@/stores/auth";
import { useFavoritesStore } from "@/stores/favorites";
import { ApiError } from "@/lib/http/ApiError";
import type { CharacterDetail } from "@/types/api";

const route = useRoute();
const authStore = useAuthStore();
const favoritesStore = useFavoritesStore();
const { isFavorite, toggle, canToggle } = useFavoriteToggle();

const character = ref<CharacterDetail | null>(null);
const loading = ref(true);
const errorMessage = ref<string | null>(null);

async function fetchCharacter() {
    loading.value = true;
    errorMessage.value = null;

    try {
        character.value = await characterRepository.show(
            Number(route.params.id),
        );
    } catch (error) {
        errorMessage.value =
            error instanceof ApiError ? error.message : "Something went wrong.";
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchCharacter();
    if (authStore.isAuthenticated) {
        favoritesStore.fetch();
    }
});
</script>

<template>
    <v-container>
        <v-btn :to="{ name: 'characters.index' }" variant="text" class="mb-4"
            >&larr; Back</v-btn
        >

        <v-skeleton-loader v-if="loading" type="article" />

        <v-alert v-else-if="errorMessage" type="error">{{
            errorMessage
        }}</v-alert>

        <v-card v-else-if="character" class="mx-auto" max-width="600">
            <v-img
                :src="character.image"
                width="200"
                height="200"
                cover
                class="rounded-lg mx-auto d-block mt-4"
            />
            <v-card-title class="d-flex align-center">
                {{ character.name }}
                <v-spacer />
                <v-btn
                    v-if="canToggle()"
                    :icon="
                        isFavorite(character.id)
                            ? 'mdi-heart'
                            : 'mdi-heart-outline'
                    "
                    :color="isFavorite(character.id) ? 'red' : undefined"
                    variant="text"
                    @click="toggle(character)"
                />
            </v-card-title>
            <v-card-subtitle
                >{{ character.species }} · {{ character.status }} ·
                {{ character.gender }}</v-card-subtitle
            >

            <v-card-text>
                <p>
                    <strong>Origin:</strong>
                    {{ character.origin?.name ?? "Unknown" }}
                </p>
                <p>
                    <strong>Current location:</strong>
                    {{ character.current_location?.name ?? "Unknown" }}
                </p>

                <h3 class="text-h6 mt-4 mb-2">Episodes</h3>
                <v-alert
                    v-if="character.episodes.length === 0"
                    type="info"
                    density="compact"
                >
                    No episodes recorded.
                </v-alert>
                <v-list v-else density="compact">
                    <v-list-item
                        v-for="episode in character.episodes"
                        :key="episode.id"
                    >
                        {{ episode.episode_code }} — {{ episode.name }} ({{
                            episode.air_date
                        }})
                    </v-list-item>
                </v-list>
            </v-card-text>
        </v-card>
    </v-container>
</template>
