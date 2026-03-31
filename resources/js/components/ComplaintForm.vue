<template>
    <form class="grid gap-4" @submit.prevent="submitForm">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="grid gap-2 text-sm">
                <span class="font-medium text-slate-700">Title</span>
                <input v-model="form.title"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none ring-0 focus:border-teal-500"
                    type="text" />
                <small class="text-rose-600">{{ errors.title?.[0] }}</small>
            </label>
            <label class="grid gap-2 text-sm">
                <span class="font-medium text-slate-700">Category</span>
                <select v-model="form.complaint_category_id"
                    class="rounded-2xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-teal-500">
                    <option value="">Select a category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}
                    </option>
                </select>
                <small class="text-rose-600">{{ errors.complaint_category_id?.[0] }}</small>
            </label>
        </div>

        <label class="grid gap-2 text-sm">
            <span class="font-medium text-slate-700">Description</span>
            <textarea v-model="form.description" rows="8"
                class="rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 outline-none focus:border-teal-500"></textarea>
            <small class="text-rose-600">{{ errors.description?.[0] }}</small>
        </label>

        <label class="grid gap-2 text-sm">
            <span class="font-medium text-slate-700">Attachments</span>
            <input multiple accept=".jpg,.jpeg,.png,.pdf"
                class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-3" type="file"
                @change="captureFiles" />
            <small class="text-slate-500">Up to 5 files. Images and PDF only.</small>
            <small class="text-rose-600">{{ errors['attachments.0']?.[0] || errors.attachments?.[0] }}</small>
        </label>

        <button :disabled="submitting"
            class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white disabled:opacity-60 cursor-pointer">
            {{ submitting ? 'Saving...' : submitLabel }}
        </button>
    </form>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    complaint: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    submitting: { type: Boolean, default: false },
    submitLabel: { type: String, default: 'Submit complaint' },
});

const emit = defineEmits(['submit']);

const form = reactive({
    title: '',
    complaint_category_id: '',
    description: '',
    attachments: [],
});

watch(() => props.complaint,
    (complaint) => {
        form.title = complaint?.title ?? '';
        form.complaint_category_id = complaint?.category?.id ?? '';
        form.description = complaint?.description ?? '';
        form.attachments = [];
    },
    { immediate: true },
);

function captureFiles(event) {
    form.attachments = Array.from(event.target.files || []);
}

function submitForm() {
    emit('submit', { ...form });
}
</script>
