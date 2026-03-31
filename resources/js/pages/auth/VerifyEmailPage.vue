<template>
    <GuestLayout title="Verify email" subtitle="Certain actions are locked until your email is verified.">
        <div class="mt-8 grid gap-4 text-sm text-slate-200">
            <p v-if="verified" class="rounded-2xl bg-emerald-500/20 px-4 py-3 text-emerald-200">Your email is verified. You can continue to the portal.</p>
            <p v-else class="rounded-2xl bg-white/10 px-4 py-3">Check your inbox for the verification link. If you did not receive it, request another email below.</p>
            <p v-if="notice" class="rounded-2xl bg-white/10 px-4 py-3">{{ notice }}</p>
            <button v-if="!verified"
                class="rounded-full bg-teal-400 px-5 py-3 text-sm font-semibold text-slate-950 cursor-pointer"
                @click="resend">Resend verification email</button>
            <button
                class="inline-flex rounded-full border border-white/20 px-5 py-3 text-sm font-semibold text-white cursor-pointer"
                @click="continueToPortal">{{ verified ? 'Continue to portal' : 'Refresh verification status' }}</button>
            <button
                class="inline-flex rounded-full border border-rose-300 px-5 py-3 text-sm font-semibold text-rose-200 cursor-pointer"
                @click="logout">Sign out</button>
        </div>
    </GuestLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
import GuestLayout from '../../layouts/GuestLayout.vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toasts';
import { extractMessage } from '../../utils/http';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const toast = useToastStore();
const verified = computed(() => !!auth.user?.email_verified_at);
const notice = ref('');

async function refreshStatus(showSuccess = false) {
    await auth.refresh();

    if (verified.value && showSuccess) {
        toast.success('Email verified successfully.');
    }
}

onMounted(async () => {
    try {
        await refreshStatus(route.query.verified === '1');

        if (route.query.verified === '1') {
            notice.value = 'Your email has been verified. You can continue now.';
        }
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to refresh verification status.'));
    }
});

async function resend() {
    try {
        const { data } = await axios.post('/api/auth/email/verification-notification');
        notice.value = data.mail_error ? `${data.message} ${data.mail_error}` : data.message;
        toast.success(data.message);
    } catch (error) {
        notice.value = extractMessage(error, 'Unable to resend verification email.');
        const mailError = error?.response?.data?.mail_error;

        if (mailError) {
            notice.value = `${notice.value} ${mailError}`;
        }

        toast.error(notice.value);
    }
}

async function continueToPortal() {
    try {
        await refreshStatus();

        if (!verified.value) {
            notice.value = 'Your email is still not verified. Open the mail link first, then continue.';
            toast.error(notice.value);
            return;
        }

        await router.push(auth.user?.is_staff ? '/admin/dashboard' : '/dashboard');
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to continue right now.'));
    }
}

async function logout() {
    try {
        await auth.logout();
        router.push('/login');
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to sign out right now.'));
    }
}
</script>