<template>
    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(15,118,110,0.15),_transparent_32%),linear-gradient(180deg,_#0f172a,_#111827)] px-4 py-10 text-white sm:px-6">
        <div class="mx-auto max-w-md rounded-[2rem] border border-white/10 bg-white/10 p-8 shadow-2xl backdrop-blur-xl">
            <p class="text-xs uppercase tracking-[0.4em] text-teal-300">Complaint Management System</p>
            <h1 class="mt-3 text-3xl font-semibold">{{ title }}</h1>
            <p v-if="subtitle" class="mt-2 text-sm text-slate-300">{{ subtitle }}</p>
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
});
</script>
