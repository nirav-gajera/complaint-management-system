<template>
    <AppShell section="user" title="New complaint" subtitle="Describe the issue clearly so the team can act quickly.">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <ComplaintForm :categories="categories" :errors="errors" :submitting="submitting" @submit="submit" />
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import AppShell from '../../layouts/AppShell.vue';
import ComplaintForm from '../../components/ComplaintForm.vue';
import { useToastStore } from '../../stores/toasts';
import { extractMessage, normalizeItems } from '../../utils/http';

const router = useRouter();
const toast = useToastStore();
const categories = ref([]);
const errors = ref({});
const submitting = ref(false);

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/categories');
        categories.value = normalizeItems(data);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load categories.'));
    }
});

async function submit(payload) {
    submitting.value = true;
    errors.value = {};

    const formData = new FormData();
    Object.entries(payload).forEach(([key, value]) => {
        if (key === 'attachments') {
            value.forEach((file) => formData.append('attachments[]', file));
        } else {
            formData.append(key, value ?? '');
        }
    });

    try {
        const { data } = await axios.post('/api/complaints', formData);
        toast.success(data.message || 'Complaint submitted successfully.');
        router.push(`/complaints/${data.data.id}`);
    } catch (error) {
        errors.value = error.response?.data?.errors ?? {};
        toast.error(extractMessage(error, 'Unable to submit complaint.'));
    } finally {
        submitting.value = false;
    }
}
</script>
