<template>
    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(20,184,166,0.14),_transparent_28%),linear-gradient(180deg,_#f8fafc,_#eef2ff_42%,_#d6e0ff8c)] text-slate-900">
        <div class="mx-auto flex min-h-screen flex-col lg:flex-row">
            <aside
                class="border-b border-white/60 bg-slate-950 px-6 py-6 text-white shadow-2xl lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r lg:px-8">
                <div class="flex items-center justify-between lg:block">
                    <div>
                        <p class="text-xs uppercase tracking-[0.3em] text-teal-300">CMS Portal</p>
                        <h1 class="mt-2 text-2xl font-semibold">{{ section === 'admin' ? 'Admin Panel' : 'User Portal'
                            }}</h1>
                    </div>
                    <RouterLink v-if="section === 'admin'" to="/dashboard" class="text-sm text-slate-300 lg:hidden">User
                    </RouterLink>
                </div>

                <nav class="mt-8 grid gap-2">
                    <RouterLink v-for="item in links" :key="item.to" :to="item.to"
                        class="rounded-2xl px-4 py-3 text-sm transition"
                        :class="$route.path === item.to ? 'bg-teal-400 text-slate-950' : 'bg-white/5 text-slate-200 hover:bg-white/12'">
                        {{ item.label }}
                    </RouterLink>
                </nav>

                <div class="mt-8 rounded-2xl bg-white/5 p-4 text-sm text-slate-300">
                    <p class="font-medium text-white">{{ auth.user?.name }}</p>
                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">{{ auth.user?.role }}</p>
                    <button
                        class="mt-4 rounded-full bg-white px-4 py-2 text-xs font-semibold text-slate-950 cursor-pointer"
                        @click="logout">Logout</button>
                </div>
            </aside>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-semibold tracking-tight">{{ title }}</h2>
                        <p v-if="subtitle" class="mt-1 text-sm text-slate-600">{{ subtitle }}</p>
                    </div>
                    <slot name="actions" />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToastStore } from '../stores/toasts';
import { extractMessage } from '../utils/http';

const props = defineProps({
    section: { type: String, default: 'user' },
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
});

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const toast = useToastStore();

const userLinks = [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/complaints', label: 'Complaints' },
    { to: '/complaints/new', label: 'New Complaint' },
    { to: '/profile', label: 'Profile' },
];

const adminLinks = [
    { to: '/admin/dashboard', label: 'Dashboard' },
    { to: '/admin/complaints', label: 'Complaints' },
    { to: '/admin/categories', label: 'Categories' },
    { to: '/admin/settings', label: 'Settings' },
    { to: '/admin/users', label: 'Users' },
    { to: '/profile', label: 'Profile' },
];

const links = computed(() => (props.section === 'admin' ? adminLinks : userLinks));

async function logout() {
    try {
        await auth.logout();
        toast.success('Logged out successfully.');
        router.push('/login');
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to logout right now.'));
    }
}
</script>
