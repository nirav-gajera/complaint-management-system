<template>
    <GuestLayout title="Create account" subtitle="Start submitting and tracking complaints online.">
        <form class="mt-8 grid gap-4" @submit.prevent="submit">
            <label class="grid gap-2 text-sm"><span>Name</span><input v-model="form.name"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /><small class="text-rose-300">{{
                    error('name') }}</small></label>
            <label class="grid gap-2 text-sm"><span>Email</span><input v-model="form.email" type="email"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /><small class="text-rose-300">{{
                    error('email') }}</small></label>
            <label class="grid gap-2 text-sm"><span>Phone</span><input v-model="form.phone"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <label class="grid gap-2 text-sm"><span>Password</span><input v-model="form.password" type="password"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /><small class="text-rose-300">{{
                    error('password') }}</small></label>
            <label class="grid gap-2 text-sm"><span>Confirm password</span><input v-model="form.password_confirmation"
                    type="password" class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <button class="rounded-full bg-teal-400 px-5 py-3 text-sm font-semibold text-slate-950 cursor-pointer">{{
                loading ? 'Creating...' : 'Register' }}</button>
        </form>
        <p class="mt-6 text-sm text-slate-300">Already registered? <RouterLink class="font-semibold text-white"
                to="/login">Login</RouterLink>
        </p>
    </GuestLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import GuestLayout from '../../layouts/GuestLayout.vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toasts';

const auth = useAuthStore();
const router = useRouter();
const toast = useToastStore();
const loading = ref(false);
const errors = ref({});
const form = reactive({ name: '', email: '', phone: '', password: '', password_confirmation: '' });

function error(key) {
    return errors.value[key]?.[0] ?? '';
}

async function submit() {
    loading.value = true;
    errors.value = {};

    try {
        const data = await auth.register(form);

        if (data.mail_warning) {
            const message = data.mail_error ? `${data.mail_warning} ${data.mail_error}` : data.mail_warning;
            toast.error(message);
        } else {
            toast.success('Registration completed. Check your inbox for the verification link.');
        }

        router.push('/verify-email');
    } catch (error) {
        errors.value = error.response?.data?.errors ?? {};
    } finally {
        loading.value = false;
    }
}
</script>