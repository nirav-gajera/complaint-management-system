<template>
    <div class="pointer-events-none fixed right-4 top-4 z-50 grid w-full max-w-sm gap-3">
        <div v-for="toast in store.items" :key="toast.id" class="rounded-2xl px-4 py-3 text-sm font-medium shadow-xl"
            :class="toast.type === 'error' ? 'bg-rose-600 text-white' : 'bg-slate-950 text-white'">
            {{ toast.message }}
        </div>
    </div>
</template>

<script setup>
import { useToastStore } from '../stores/toasts';

const store = useToastStore();
</script>
