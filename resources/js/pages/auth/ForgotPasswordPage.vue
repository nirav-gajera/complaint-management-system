<template>
    <GuestLayout title="Reset link" subtitle="We will email you a password reset link.">
        <form class="mt-8 grid gap-4" @submit.prevent="submit">
            <label class="grid gap-2 text-sm"><span>Email</span><input v-model="email" type="email"
                    class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3" /></label>
            <button class="rounded-full bg-teal-400 px-5 py-3 text-sm font-semibold text-slate-950 cursor-pointer">Send
                reset link</button>
        </form>
        <p v-if="message" class="mt-4 text-sm text-emerald-300">{{ message }}</p>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import GuestLayout from '../../layouts/GuestLayout.vue';

const email = ref('');
const message = ref('');

async function submit() {
    const { data } = await axios.post('/api/auth/forgot-password', { email: email.value });
    message.value = data.message;
}
</script>
