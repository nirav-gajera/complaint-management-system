<template>
    <AppShell :section="layoutSection" title="Profile" subtitle="Manage your personal details and password.">
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold">Personal details</h3>
                <form class="mt-6 grid gap-4" @submit.prevent="updateProfile">
                    <label class="grid gap-2 text-sm">
                        <span>Name</span>
                        <input v-model="profile.name"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"
                            placeholder="Name" />
                        <small class="text-rose-500">{{ profileError('name') }}</small>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span>Email</span>
                        <input v-model="profile.email"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"
                            placeholder="Email" />
                        <small class="text-rose-500">{{ profileError('email') }}</small>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span>Phone</span>
                        <input v-model="profile.phone"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"
                            placeholder="Phone" />
                        <small class="text-rose-500">{{ profileError('phone') }}</small>
                    </label>
                    <button
                        class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white cursor-pointer">Save
                        profile</button>
                </form>
            </section>
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold">Change password</h3>
                <form class="mt-6 grid gap-4" @submit.prevent="updatePassword">
                    <label class="grid gap-2 text-sm">
                        <span>Current password</span>
                        <input v-model="password.current_password" type="password"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"
                            placeholder="Current password" />
                        <small class="text-rose-500">{{ passwordError('current_password') }}</small>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span>New password</span>
                        <input v-model="password.password" type="password"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"
                            placeholder="New password" />
                        <small class="text-rose-500">{{ passwordError('password') }}</small>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span>Confirm password</span>
                        <input v-model="password.password_confirmation" type="password"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"
                            placeholder="Confirm password" />
                    </label>
                    <button
                        class="rounded-full bg-teal-500 px-5 py-3 text-sm font-semibold text-white cursor-pointer">Update password</button>
                </form>
            </section>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import AppShell from '../../layouts/AppShell.vue';
import { useAuthStore } from '../../stores/auth';
import { useToastStore } from '../../stores/toasts';
import { extractMessage } from '../../utils/http';

const auth = useAuthStore();
const toast = useToastStore();
const profile = reactive({ name: '', email: '', phone: '' });
const password = reactive({ current_password: '', password: '', password_confirmation: '' });
const profileErrors = ref({});
const passwordErrors = ref({});
const layoutSection = computed(() => (auth.isStaff ? 'admin' : 'user'));

onMounted(() => {
    profile.name = auth.user?.name || '';
    profile.email = auth.user?.email || '';
    profile.phone = auth.user?.phone || '';
});

function profileError(key) {
    return profileErrors.value[key]?.[0] ?? '';
}

function passwordError(key) {
    return passwordErrors.value[key]?.[0] ?? '';
}

async function updateProfile() {
    profileErrors.value = {};

    try {
        const { data } = await axios.patch('/api/profile', profile);
        auth.user = data.data;
        toast.success(data.message || 'Profile updated successfully.');
    } catch (error) {
        profileErrors.value = error.response?.data?.errors ?? {};
        toast.error(extractMessage(error, 'Unable to update profile.'));
    }
}

async function updatePassword() {
    passwordErrors.value = {};

    try {
        const { data } = await axios.put('/api/profile/password', password);
        password.current_password = '';
        password.password = '';
        password.password_confirmation = '';
        toast.success(data.message || 'Password updated successfully.');
    } catch (error) {
        passwordErrors.value = error.response?.data?.errors ?? {};
        toast.error(extractMessage(error, 'Unable to update password.'));
    }
}
</script>
