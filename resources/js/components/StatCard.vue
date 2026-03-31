<template>
    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">{{ label }}</p>
        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ value }}</p>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
});
</script>
