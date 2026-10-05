<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    folder: { type: Object, required: true },
    breadcrumbs: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    allowedExtensions: { type: Array, default: () => [] },
    maxSizeKb: { type: Number, required: true },
});

const form = useForm({
    folder_id: props.folder.id,
    title: '',
    department_id: '',
    file: null,
});

// <input accept=".pdf,.docx,..."> built from the same list the server validates against
const accept = computed(() => props.allowedExtensions.map((extension) => `.${extension}`).join(','));

// Suggest the file name (without extension) as the title when the title is still empty.
watch(
    () => form.file,
    (file) => {
        if (file && form.title === '') {
            form.title = file.name.replace(/\.[^.]+$/, '');
        }
    },
);

function submit() {
    form.post(route('documents.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Upload file" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Upload file</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl space-y-4 px-4 sm:px-6 lg:px-8">
                <Breadcrumbs :items="breadcrumbs" link-last />

                <form
                    class="space-y-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200"
                    @submit.prevent="submit"
                >
                    <div>
                        <InputLabel value="Folder" />
                        <p class="mt-1 text-sm text-gray-900">{{ folder.name }}</p>
                        <InputError class="mt-2" :message="form.errors.folder_id" />
                    </div>

                    <div>
                        <InputLabel for="title" value="Title" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="255"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="department" value="Department" />
                        <select
                            id="department"
                            v-model="form.department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="" disabled>Select a department</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.department_id" />
                    </div>

                    <div>
                        <InputLabel value="File" />
                        <div class="mt-1">
                            <FileDropzone v-model="form.file" :accept="accept" :error="form.errors.file" />
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Allowed: {{ allowedExtensions.join(', ') }}. Maximum size {{ maxSizeKb / 1024 }} MB.
                        </p>
                    </div>

                    <progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                        class="h-2 w-full overflow-hidden rounded"
                    >
                        {{ form.progress.percentage }}%
                    </progress>

                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('folders.show', folder.id)" class="text-sm text-gray-600 hover:underline">
                            Cancel
                        </Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Upload
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
