import { useAuthStore } from '@/stores/auth';
import { useFavoritesStore } from '@/stores/favorites';
import type { Character } from '@/types/api';

export function useFavoriteToggle() {
    const authStore = useAuthStore();
    const favoritesStore = useFavoritesStore();

    function isFavorite(characterId: number): boolean {
        return favoritesStore.isFavorite(characterId);
    }

    function canToggle(): boolean {
        return authStore.isAuthenticated;
    }

    async function toggle(character: Character) {
        if (!canToggle()) {
            return;
        }

        if (isFavorite(character.id)) {
            await favoritesStore.remove(character.id);
        } else {
            await favoritesStore.add(character);
        }
    }

    return { isFavorite, toggle, canToggle };
}
