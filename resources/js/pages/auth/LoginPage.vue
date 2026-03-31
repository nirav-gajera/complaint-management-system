<template>
    <GuestLayout title="Welcome back" subtitle="Sign in to track and manage your complaints.">
        <form class="mt-8 grid gap-4" @submit.prevent="submit">
            <label class="grid gap-2 text-sm">
                <span>Email</span>
                <input v-model="form.email" type="email"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" />
                <small class="text-rose-300">{{ error('email') }}</small>
            </label>
            <label class="grid gap-2 text-sm">
                <span>Password</span>
                <input v-model="form.password" type="password"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" />
                <small class="text-rose-300">{{ error('password') }}</small>
            </label>
            <button class="rounded-full bg-teal-400 px-5 py-3 text-sm font-semibold text-slate-950 cursor-pointer">{{
                loading ? 'Signing in...' : 'Login' }}</button>
        </form>

        <p class="mt-6 text-sm text-slate-300">
            Need an account?
            <RouterLink class="font-semibold text-white" to="/register">Register</RouterLink>
        </p>
        <RouterLink class="mt-2 inline-block text-sm text-teal-300" to="/forgot-password">Forgot password?</RouterLink>
    </GuestLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import GuestLayout from '../../layouts/GuestLayout.vue';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const loading = ref(false);
const errors = ref({});
const form = reactive({ email: '', password: '' });

function error(key) {
    return errors.value[key]?.[0] ?? '';
}

async function submit() {
    loading.value = true;
    errors.value = {};

    try {
        await auth.login(form);
        router.push(auth.user?.is_staff ? '/admin/dashboard' : '/dashboard');
    } catch (error) {
        errors.value = error.response?.data?.errors ?? {};
    } finally {
        loading.value = false;
    }
}
</script>
