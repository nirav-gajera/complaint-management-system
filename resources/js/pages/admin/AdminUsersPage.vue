<template>
    <AppShell section="admin" title="Users" subtitle="Activate, deactivate, re-role, and trigger password resets.">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-3 md:grid-cols-5">
                <input v-model="filters.search" placeholder="Search name, email, phone"
                    class="md:col-span-2 rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                <select v-model="filters.role"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                    <option value="">All roles</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="support_agent">Support Agent</option>
                    <option value="user">User</option>
                </select>
                <select v-model="filters.is_active"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                    <option value="">All statuses</option>
                    <option value="true">Active</option>
                    <option value="false">Inactive</option>
                </select>
                <div class="flex gap-3">
                    <button
                        class="flex-1 rounded-full bg-teal-500 px-5 py-3 text-sm font-semibold text-white cursor-pointer"
                        @click="load">Apply</button>
                    <button class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold cursor-pointer"
                        @click="resetFilters">Reset</button>
                </div>
            </div>

            <div v-if="users.length" class="mt-6 grid gap-4">
                <div v-for="user in users" :key="user.id" class="rounded-[1.5rem] border border-slate-100 px-4 py-4">
                    <div class="grid gap-4 lg:grid-cols-[1.3fr_auto_auto_auto] lg:items-center">
                        <div>
                            <p class="font-semibold">{{ user.name }}</p>
                            <p class="text-sm text-slate-500">{{ user.email }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">
                                {{ user.phone || 'No phone' }} • {{ user.email_verified_at ? 'Verified' : 'Unverified'
                                }}
                            </p>
                        </div>
                        <select v-model="user.role"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="support_agent">Support Agent</option>
                            <option value="user">User</option>
                        </select>
                        <select v-model="user.is_active"
                            class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                        <div class="flex gap-2">
                            <button
                                class="rounded-full bg-slate-950 px-4 py-2 text-xs font-semibold text-white cursor-pointer"
                                @click="save(user)">Save</button>
                            <button
                                class="rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold cursor-pointer"
                                @click="resetPassword(user)">Reset Password</button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="mt-6 rounded-2xl bg-slate-50 px-4 py-6 text-sm text-slate-500">No users match the current
                filters.</p>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import AppShell from '../../layouts/AppShell.vue';
import { useToastStore } from '../../stores/toasts';
import { extractMessage, normalizeItems } from '../../utils/http';

const toast = useToastStore();
const users = ref([]);
const initialFilters = { search: '', role: '', is_active: '' };
const filters = reactive({ ...initialFilters });

async function load() {
    try {
        const { data } = await axios.get('/api/admin/users', { params: filters });
        users.value = normalizeItems(data);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load users.'));
    }
}

function resetFilters() {
    Object.assign(filters, initialFilters);
    load();
}

async function save(user) {
    try {
        const { data } = await axios.patch(`/api/admin/users/${user.id}`, {
            role: user.role,
            is_active: user.is_active,
        });

        toast.success(data.message || 'User updated successfully.');
        await load();
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to update user.'));
    }
}

async function resetPassword(user) {
    try {
        const { data } = await axios.post(`/api/admin/users/${user.id}/reset-password`);
        const message = data.mail_error ? `${data.message} ${data.mail_error}` : data.message;
        toast.success(message || 'Password reset link processed.');
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to send password reset link.'));
    }
}

onMounted(load);
</script>