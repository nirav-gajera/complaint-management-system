<template>
    <AppShell section="user" :title="complaint?.title || 'Complaint'"
        subtitle="Review details, track responses, and update the complaint while it is still pending.">
        <template #actions>
            <button v-if="complaint?.can_withdraw"
                class="rounded-full border border-rose-300 px-5 py-3 text-sm font-semibold text-rose-700 cursor-pointer hover:bg-rose-400 hover:text-rose-100"
                @click="confirmWithdraw">Withdraw</button>
        </template>

        <div v-if="complaint" class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">{{ complaint.reference }}</p>
                        <h3 class="mt-2 text-2xl font-semibold">{{ complaint.title }}</h3>
                    </div>
                    <StatusBadge :status="complaint.status" />
                </div>
                <p class="mt-4 whitespace-pre-line text-slate-700">{{ complaint.description }}</p>

                <div v-if="complaint.can_edit" class="mt-8 border-t border-slate-100 pt-6">
                    <h4 class="mb-4 text-lg font-semibold">Edit complaint</h4>
                    <ComplaintForm :complaint="complaint" :categories="categories" :errors="errors"
                        :submitting="submitting" submit-label="Save changes" @submit="updateComplaint" />
                </div>
            </section>

            <section class="grid gap-6">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h4 class="text-lg font-semibold">Attachments</h4>
                    <div v-if="complaint.attachments.length" class="mt-4 grid gap-2 text-sm">
                        <a v-for="attachment in complaint.attachments" :key="attachment.id"
                            :href="attachment.download_url"
                            class="rounded-2xl border border-slate-100 px-4 py-3 hover:border-teal-500">{{
                            attachment.name
                            }}</a>
                    </div>
                    <p v-else class="mt-4 text-sm text-slate-500">No attachments uploaded.</p>
                </div>

                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h4 class="text-lg font-semibold">Responses</h4>
                    <div v-if="complaint.messages.length" class="mt-4 grid gap-3">
                        <div v-for="message in complaint.messages" :key="message.id"
                            class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-sm font-semibold">{{ message.user?.name }}</p>
                            <p class="mt-2 text-sm text-slate-700">{{ message.message }}</p>
                        </div>
                    </div>
                    <p v-else class="mt-4 text-sm text-slate-500">No admin responses yet.</p>
                </div>
            </section>
        </div>

        <div v-else class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm text-sm text-slate-500">
            Complaint could
            not be loaded.</div>

        <div v-if="showWithdrawConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-slate-800">Withdraw Complaint</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Are you sure you want to withdraw this complaint? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="cancelWithdraw"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button @click="proceedWithdraw"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                        Withdraw
                    </button>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
import AppShell from '../../layouts/AppShell.vue';
import ComplaintForm from '../../components/ComplaintForm.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import { useToastStore } from '../../stores/toasts';
import { extractMessage, normalizeItems } from '../../utils/http';

const route = useRoute();
const router = useRouter();
const toast = useToastStore();
const complaint = ref(null);
const categories = ref([]);
const errors = ref({});
const submitting = ref(false);
const showWithdrawConfirm = ref(false);

async function load() {
    try {
        const [{ data: complaintData }, { data: categoryData }] = await Promise.all([
            axios.get(`/api/complaints/${route.params.id}`),
            axios.get('/api/categories'),
        ]);
        complaint.value = complaintData.data;
        categories.value = normalizeItems(categoryData);
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to load complaint details.'));
        router.push('/complaints');
    }
}

async function updateComplaint(payload) {
    submitting.value = true;
    errors.value = {};
    const formData = new FormData();
    formData.append('_method', 'PUT');
    Object.entries(payload).forEach(([key, value]) => {
        if (key === 'attachments') {
            value.forEach((file) => formData.append('attachments[]', file));
        } else {
            formData.append(key, value ?? '');
        }
    });

    try {
        const { data } = await axios.post(`/api/complaints/${route.params.id}`, formData);
        complaint.value = data.data;
        toast.success(data.message || 'Complaint updated successfully.');
    } catch (error) {
        errors.value = error.response?.data?.errors ?? {};
        toast.error(extractMessage(error, 'Unable to update complaint.'));
    } finally {
        submitting.value = false;
    }
}

function confirmWithdraw() {
    showWithdrawConfirm.value = true;
}

function cancelWithdraw() {
    showWithdrawConfirm.value = false;
}

async function proceedWithdraw() {
    try {
        const { data } = await axios.delete(`/api/complaints/${route.params.id}`);
        toast.success(data.message || 'Complaint withdrawn successfully.');
        router.push('/complaints');
    } catch (error) {
        toast.error(extractMessage(error, 'Unable to withdraw complaint.'));
    } finally {
        cancelWithdraw();
    }
}

onMounted(load);
</script>
