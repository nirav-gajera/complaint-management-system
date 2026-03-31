<template>
    <AppShell section="admin" title="Complaint management"
        subtitle="Search and review all complaints across the system.">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-3 md:grid-cols-5">
                <input v-model="filters.search" placeholder="Search title, reference, user"
                    class="md:col-span-2 rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                <input v-model="filters.assigned_to" placeholder="Assigned user id"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                <select v-model="filters.status"
                    class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <div class="flex gap-3">
                    <button
                        class="flex-1 rounded-full bg-teal-500 px-5 py-3 text-sm font-semibold text-white cursor-pointer"
                        @click="load">Apply</button>
                    <button class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold cursor-pointer"
                        @click="resetFilters">Reset</button>
                </div>
            </div>
            <div v-if="complaints.length" class="mt-6 grid gap-3">
                <RouterLink v-for="complaint in complaints" :key="complaint.id"
                    :to="`/admin/complaints/${complaint.id}`"
                    class="rounded-[1.5rem] border border-slate-100 px-4 py-4 hover:border-teal-500">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold">{{ complaint.title }}</p>
                            <p class="text-sm text-slate-500">{{ complaint.reference }} • {{ complaint.user?.name }}</p>
                        </div>
                        <StatusBadge :status="complaint.status" />
                    </div>
                </RouterLink>
            </div>
            <p v-else class="mt-6 rounded-2xl bg-slate-50 px-4 py-6 text-sm text-slate-500">No complaints match the
                current filters.</p>
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
const initialFilters = ({ search: '', status: '', assigned_to: '' });

const filters = reactive({ ...initialFilters });

async function load() {
    try {
        const { data } = await axios.get('/api/admin/complaints', { params: filters });
        complaints.value = normalizeItems(data);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load complaints.'));
    }
}
function resetFilters() {
    Object.assign(filters, initialFilters);
    load();
}

onMounted(load);
</script>