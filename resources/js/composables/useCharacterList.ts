import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { characterRepository } from '@/repositories/characterRepository';
import { ApiError } from '@/lib/http/ApiError';
import type { Character, CharacterFilters, PaginationMeta } from '@/types/api';

const PER_PAGE = 12;
const DEBOUNCE_MS = 350;

export function useCharacterList() {
    const route = useRoute();
    const router = useRouter();

    const name = ref((route.query.name as string) ?? '');
    const status = ref((route.query.status as string) ?? '');
    const species = ref((route.query.species as string) ?? '');
    const gender = ref((route.query.gender as string) ?? '');
    const page = ref(route.query.page ? Number(route.query.page) : 1);

    const characters = ref<Character[]>([]);
    const meta = ref<PaginationMeta | null>(null);
    const loading = ref(false);
    const errorMessage = ref<string | null>(null);

    let debounceTimer: ReturnType<typeof setTimeout>;

    function syncQuery() {
        router.replace({
            query: {
                ...(name.value ? { name: name.value } : {}),
                ...(status.value ? { status: status.value } : {}),
                ...(species.value ? { species: species.value } : {}),
                ...(gender.value ? { gender: gender.value } : {}),
                ...(page.value > 1 ? { page: String(page.value) } : {}),
            },
        });
    }

    async function fetchCharacters() {
        loading.value = true;
        errorMessage.value = null;

        try {
            const filters: CharacterFilters = {
                name: name.value || undefined,
                status: (status.value || undefined) as CharacterFilters['status'],
                species: species.value || undefined,
                gender: (gender.value || undefined) as CharacterFilters['gender'],
                page: page.value,
                per_page: PER_PAGE,
            };

            const response = await characterRepository.list(filters);
            characters.value = response.data;
            meta.value = response.meta;
        } catch (error) {
            errorMessage.value = error instanceof ApiError ? error.message : 'Something went wrong. Please try again.';
        } finally {
            loading.value = false;
        }
    }

    // Typing updates the fields immediately, but the URL (and the actual fetch) waits for a pause.
    watch([name, status, species, gender], () => {
        page.value = 1;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(syncQuery, DEBOUNCE_MS);
    });

    watch(page, syncQuery);

    watch(
        () => route.query,
        () => {
            page.value = route.query.page ? Number(route.query.page) : 1;
            fetchCharacters();
        },
    );

    onMounted(fetchCharacters);

    return { name, status, species, gender, page, characters, meta, loading, errorMessage };
}
