<template>
    <AppShell section="admin" title="Admin dashboard"
        subtitle="Live overview of complaint load and resolution progress.">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <StatCard label="Total" :value="stats.total" />
            <StatCard label="Pending" :value="stats.pending" />
            <StatCard label="In Progress" :value="stats.in_progress" />
            <StatCard label="Resolved" :value="stats.resolved" />
            <StatCard label="Rejected" :value="stats.rejected" />
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-[1.2fr_1fr]">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl font-semibold">Complaint trend</h3>
                    <select v-model="selectedPeriod"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 outline-none focus:border-teal-500">
                        <option value="day">Day</option>
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                        <option value="year">Year</option>
                    </select>
                </div>
                <div class="mt-6 grid max-h-[28rem] gap-4 overflow-y-auto pr-1">
                    <div v-for="item in trend" :key="item.key" class="grid gap-2">
                        <div class="flex items-center justify-between text-sm text-slate-600"><span>{{ item.label
                                }}</span><span>{{ item.total }}</span></div>
                        <div class="h-3 rounded-full bg-slate-100">
                            <div class="h-3 rounded-full bg-teal-500"
                                :style="{ width: `${Math.max((item.total / maxTrend) * 100, item.total ? 6 : 0)}%` }">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xl font-semibold">Recent complaints</h3>
                <div class="mt-4 grid gap-3">
                    <RouterLink v-for="complaint in complaints" :key="complaint.id"
                        :to="`/admin/complaints/${complaint.id}`"
                        class="rounded-2xl border border-slate-100 px-4 py-4 hover:border-teal-500">
                        <p class="font-semibold">{{ complaint.title }}</p>
                        <p class="text-sm text-slate-500">{{ complaint.user?.name }}</p>
                    </RouterLink>
                </div>
            </section>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { RouterLink } from 'vue-router';
import AppShell from '../../layouts/AppShell.vue';
import StatCard from '../../components/StatCard.vue';
import { useToastStore } from '../../stores/toasts';
import { extractMessage } from '../../utils/http';

const toast = useToastStore();
const stats = ref({ total: 0, pending: 0, in_progress: 0, resolved: 0, rejected: 0 });
const trend = ref([]);
const complaints = ref([]);
const selectedPeriod = ref('month');
const maxTrend = computed(() => Math.max(...trend.value.map((item) => Number(item.total)), 1));

async function loadDashboard() {
    try {
        const { data } = await axios.get('/api/admin/dashboard', {
            params: { period: selectedPeriod.value },
        });

        stats.value = data.stats;
        trend.value = data.trend;
        complaints.value = data.recent_complaints;
        selectedPeriod.value = data.selected_period || selectedPeriod.value;
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load dashboard.'));
    }
}

watch(selectedPeriod, loadDashboard);

onMounted(loadDashboard);
</script>
