<template>
    <AppShell section="user" title="Dashboard" subtitle="A quick snapshot of your complaint activity.">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <StatCard label="Total" :value="stats.total" />
            <StatCard label="Pending" :value="stats.pending" />
            <StatCard label="In Progress" :value="stats.in_progress" />
            <StatCard label="Resolved" :value="stats.resolved" />
            <StatCard label="Rejected" :value="stats.rejected" />
        </div>

        <section class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold">Recent complaints</h3>
                <RouterLink to="/complaints" class="text-sm font-semibold text-teal-700">View all</RouterLink>
            </div>
            <div v-if="complaints.length" class="grid gap-3">
                <RouterLink v-for="complaint in complaints" :key="complaint.id" :to="`/complaints/${complaint.id}`"
                    class="flex items-center justify-between rounded-2xl border border-slate-100 px-4 py-4 hover:border-teal-500">
                    <div>
                        <p class="font-medium">{{ complaint.title }}</p>
                        <p class="text-sm text-slate-500">{{ complaint.reference }}</p>
                    </div>
                    <StatusBadge :status="complaint.status" />
                </RouterLink>
            </div>
            <p v-else class="rounded-2xl bg-slate-50 px-4 py-6 text-sm text-slate-500">No complaints found yet. Submit one from the complaints section.</p>
        </section>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import AppShell from '../../layouts/AppShell.vue';
import StatCard from '../../components/StatCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useToastStore } from '../../stores/toasts';
import { extractMessage, normalizeItems } from '../../utils/http';

const toast = useToastStore();
const stats = ref({ total: 0, pending: 0, in_progress: 0, resolved: 0, rejected: 0 });
const complaints = ref([]);

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/dashboard');
        stats.value = data.stats;
        complaints.value = normalizeItems(data.recent_complaints);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load dashboard.'));
    }
});
</script>
