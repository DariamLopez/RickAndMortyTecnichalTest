import { favoriteRepository } from "@/repositories/favoriteRepository";
import { Character, Paginated } from "@/types/api";
import { defineStore } from "pinia";

interface FavoritesState {
    items: Character[];
    loaded: boolean;
    loading: boolean;
}
export const useFavoritesStore = defineStore("favorites", {
    state: (): FavoritesState => ({
        items: [],
        loaded: false,
        loading: false,
    }),

    getters: {
        isFavorite: (state) => (characterId: number) =>
            state.items.some((character) => character.id === characterId),
    },

    actions: {
        async fetch(force = false) {
            if (this.loaded && !force) {
                return;
            }

            this.loading = true;

            try {
                const response = await favoriteRepository.list();
                this.items = response.data;
                this.loaded = true;
            } finally {
                this.loading = false;
            }
        },

        async add(character: Character) {
            const alreadyFavorite = this.isFavorite(character.id);

            if (!alreadyFavorite) {
                this.items.push(character);
            }

            try {
                await favoriteRepository.add(character.id);
            } catch (error) {
                if (!alreadyFavorite) {
                    this.items = this.items.filter(
                        (item) => item.id !== character.id,
                    );
                }
                throw error;
            }
        },

        async remove(characterId: number) {
            const removed = this.items.find((item) => item.id === characterId);
            this.items = this.items.filter((item) => item.id !== characterId);

            try {
                await favoriteRepository.remove(characterId);
            } catch (error) {
                if (removed) {
                    this.items.push(removed);
                }
                throw error;
            }
        },

        reset() {
            this.items = [];
            this.loaded = false;
        },
    },
});
