<template>
    <AppShell section="user" title="Complaints" subtitle="Search, filter, and review every complaint you've submitted.">
        <template #actions>
            <RouterLink to="/complaints/new"
                class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white">New Complaint</RouterLink>
        </template>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-3 md:grid-cols-7">
                <input v-model="filters.search" placeholder="Search"
                    class="md:col-span-2 rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                <select v-model="filters.status"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select v-model="filters.category_id"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                    <option value="">All categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}
                    </option>
                </select>
                <input v-model="filters.date_from" type="date"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                <input v-model="filters.date_to" type="date"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                <div class="flex gap-3">
                    <button
                        class="flex-1 rounded-full bg-teal-500 px-5 py-3 text-sm font-semibold text-white cursor-pointer"
                        @click="loadComplaints">Apply</button>
                    <button class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold cursor-pointer"
                        @click="resetFilters">Reset</button>
                </div>
            </div>

            <div v-if="complaints.length" class="mt-6 grid gap-3">
                <RouterLink v-for="complaint in complaints" :key="complaint.id" :to="`/complaints/${complaint.id}`"
                    class="rounded-[1.5rem] border border-slate-100 px-4 py-4 hover:border-teal-500">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">{{ complaint.title }}</p>
                            <p class="text-sm text-slate-500">{{ complaint.reference }} • {{ complaint.category?.name }}
                            </p>
                        </div>
                        <StatusBadge :status="complaint.status" />
                    </div>
                </RouterLink>
            </div>
            <p v-else class="mt-6 rounded-2xl bg-slate-50 px-4 py-6 text-sm text-slate-500">No complaints match the current filters.</p>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import AppShell from '../../layouts/AppShell.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useToastStore } from '../../stores/toasts';
import { extractMessage, normalizeItems } from '../../utils/http';

const toast = useToastStore();
const complaints = ref([]);
const categories = ref([]);
const initialFilters = { search: '', status: '', category_id: '', date_from: '', date_to: '' };
const filters = reactive({ ...initialFilters });

async function loadCategories() {
    const { data } = await axios.get('/api/categories');
    categories.value = normalizeItems(data);
}

async function loadComplaints() {
    try {
        const { data } = await axios.get('/api/complaints', { params: filters });
        complaints.value = normalizeItems(data);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load complaints.'));
    }
}

function resetFilters() {
    Object.assign(filters, initialFilters);
    loadComplaints();
}

onMounted(async () => {
    try {
        await Promise.all([loadCategories(), loadComplaints()]);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load complaint filters.'));
    }
});
</script>
