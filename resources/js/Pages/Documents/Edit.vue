<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    document: { type: Object, required: true },
    departments: { type: Array, default: () => [] },
});

const form = useForm({
    title: props.document.title,
    department_id: props.document.department_id,
});

function submit() {
    form.put(route('documents.update', props.document.id));
}
</script>

<template>
    <Head title="Edit file" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit file</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form
                    class="space-y-6 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200"
                    @submit.prevent="submit"
                >
                    <div>
                        <InputLabel value="File" />
                        <p class="mt-1 break-all text-sm text-gray-900">
                            {{ document.file_name }}
                            <span v-if="document.folder" class="text-gray-500">in {{ document.folder.name }}</span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            To change the file itself, upload a new file and delete this one.
                        </p>
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
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.department_id" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('documents.show', document.id)" class="text-sm text-gray-600 hover:underline">
                            Cancel
                        </Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Save changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
