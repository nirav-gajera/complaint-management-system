<template>
    <AppShell section="admin" title="Settings" subtitle="Control system-wide notification and template preferences.">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4">
                <label v-for="setting in settings" :key="setting.id" class="grid gap-2 text-sm">
                    <span class="font-medium text-slate-700">{{ setting.label }}</span>
                    <input v-if="setting.type !== 'textarea'" v-model="settingValues[setting.id]"
                        class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-teal-500" />
                    <textarea v-else v-model="settingValues[setting.id]" rows="4"
                        class="rounded-[1.5rem] border border-slate-200 px-4 py-3 outline-none focus:border-teal-500"></textarea>
                </label>
            </div>
            <button class="mt-6 rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white cursor-pointer"
                @click="save">Save settings</button>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastStore } from '../../stores/toasts';
import AppShell from '../../layouts/AppShell.vue';
import { extractMessage, normalizeItems } from '../../utils/http';

const toast = useToastStore();
const settings = ref([]);
const settingValues = ref({});

async function load() {
    try {
        const { data } = await axios.get('/api/admin/settings');
        const items = normalizeItems(data);

        settings.value = items;
        settingValues.value = Object.fromEntries(items.map((setting) => [setting.id, setting.value?.body ?? setting.value ?? '']));
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load settings.'));
    }
}

async function save() {
    try {
        const payload = { settings: {} };

        for (const [id, value] of Object.entries(settingValues.value)) {
            payload.settings[id] = { value };
        }

        const { data } = await axios.put('/api/admin/settings', payload);
        toast.success(data.message || 'Settings saved successfully.');
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to save settings.'));
    }
}

onMounted(load);
</script>
