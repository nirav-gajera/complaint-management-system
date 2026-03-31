<template>
    <GuestLayout title="Set new password" subtitle="Enter the token from email and choose a new password.">
        <form class="mt-8 grid gap-4" @submit.prevent="submit">
            <label class="grid gap-2 text-sm"><span>Email</span><input v-model="form.email" type="email"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <label class="grid gap-2 text-sm"><span>Reset token</span><input v-model="form.token"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <label class="grid gap-2 text-sm"><span>Password</span><input v-model="form.password" type="password"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <label class="grid gap-2 text-sm"><span>Confirm password</span><input v-model="form.password_confirmation"
                    type="password" class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <button class="rounded-full bg-teal-400 px-5 py-3 text-sm font-semibold text-slate-950 cursor-pointer">Reset password</button>
        </form>
        <p v-if="message" class="mt-4 text-sm text-emerald-300">{{ message }}</p>
    </GuestLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import GuestLayout from '../../layouts/GuestLayout.vue';

const route = useRoute();
const params = new URLSearchParams(window.location.search);
const message = ref('');
const form = reactive({
    email: params.get('email') || '',
    token: route.params.token || '',
    password: '',
    password_confirmation: '',
});

async function submit() {
    const { data } = await axios.post('/api/auth/reset-password', form);
    message.value = data.message;
}
</script>
