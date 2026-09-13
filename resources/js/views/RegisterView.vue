<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { ApiError } from '@/lib/http/ApiError';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const fieldErrors = ref<Record<string, string[]>>({});

async function submit() {
    loading.value = true;
    errorMessage.value = null;
    fieldErrors.value = {};

    try {
        await authStore.register({
            name: name.value,
            email: email.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        router.push({ name: 'characters.index' });
    } catch (error) {
        if (error instanceof ApiError) {
            errorMessage.value = error.message;
            fieldErrors.value = error.errors ?? {};
        } else {
            errorMessage.value = 'Something went wrong. Please try again.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <v-container class="fill-height" fluid>
        <v-responsive class="align-center mx-auto" max-width="400">
            <h1 class="text-h4 mb-4">Register</h1>

            <v-alert v-if="errorMessage" type="error" class="mb-4" density="compact">{{ errorMessage }}</v-alert>

            <v-form @submit.prevent="submit">
                <v-text-field v-model="name" label="Name" :error-messages="fieldErrors.name" required />
                <v-text-field v-model="email" label="Email" type="email" :error-messages="fieldErrors.email" required />
                <v-text-field
                    v-model="password"
                    label="Password"
                    type="password"
                    :error-messages="fieldErrors.password"
                    required
                />
                <v-text-field v-model="passwordConfirmation" label="Confirm password" type="password" required />
                <v-btn type="submit" color="primary" block class="mt-4" :loading="loading">Register</v-btn>
            </v-form>

            <p class="text-center mt-4">
                Already have an account?
                <router-link :to="{ name: 'login' }">Log in</router-link>
            </p>
        </v-responsive>
    </v-container>
</template>
