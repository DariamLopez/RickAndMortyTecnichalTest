import http from "@/lib/http";
import { Character, Paginated } from "@/types/api";


export const favoriteRepository = {
    async list(): Promise<Paginated<Character>> {
        const response = await http.get<Paginated<Character>>('/favorites');
        return response;
    },

    async add(characterId: number): Promise<void> {
        await http.post('/favorites', { characterId });
    },

    async remove(characterId: number): Promise<void> {
        await http.delete(`/favorites/${characterId}`);
    }
}
