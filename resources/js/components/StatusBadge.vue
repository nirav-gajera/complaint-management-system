<template>
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]" :class="classes">
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
});

const classes = computed(() => ({
    pending: 'bg-amber-100 text-amber-800',
    in_progress: 'bg-sky-100 text-sky-800',
    resolved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-800',
}[props.status] || 'bg-slate-100 text-slate-800'));

const label = computed(() => props.status.replace('_', ' '));
</script>
