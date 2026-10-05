<script setup>
import { reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import NameFormModal from '@/Components/NameFormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { plural } from '@/utils/format';

defineProps({
    departments: { type: Object, required: true }, // paginated: { data, links, meta }
});

/* ---------------------------------------------------------------- create / update */

const modal = reactive({ show: false, target: null }); // target = department being edited, null = creating
const form = useForm({ name: '' });

function openCreate() {
    form.reset();
    form.clearErrors();
    modal.target = null;
    modal.show = true;
}

function openEdit(department) {
    form.clearErrors();
    form.name = department.name;
    modal.target = department;
    modal.show = true;
}

function closeModal() {
    modal.show = false;
}

function submit() {
    const options = { preserveScroll: true, onSuccess: closeModal };

    if (modal.target) {
        form.put(route('departments.update', modal.target.id), options);
    } else {
        form.post(route('departments.store'), options);
    }
}

/* ---------------------------------------------------------------- delete */

const deleteDialog = reactive({ show: false, item: null });
const deleting = ref(false);

function askDelete(department) {
    deleteDialog.item = department;
    deleteDialog.show = true;
}

function confirmDelete() {
    router.delete(route('departments.destroy', deleteDialog.item.id), {
        preserveScroll: true,
        onStart: () => (deleting.value = true),
        onFinish: () => {
            deleting.value = false;
            deleteDialog.show = false;
        },
    });
}
</script>

<template>
    <Head title="Departments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Departments</h2>
                <PrimaryButton type="button" @click="openCreate">New department</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-600">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Name</th>
                                    <th scope="col" class="px-4 py-3">Files</th>
                                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="department in departments.data" :key="department.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ department.name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ plural(department.documents_count, 'file') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            class="rounded px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100"
                                            @click="openEdit(department)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:text-gray-300 disabled:hover:bg-transparent"
                                            :disabled="department.documents_count > 0"
                                            :title="department.documents_count > 0 ? 'Still used by files' : 'Delete'"
                                            @click="askDelete(department)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!departments.data.length">
                                    <td colspan="3" class="p-8 text-center text-gray-500">No departments yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="departments.meta.last_page > 1"
                        class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 px-4 py-3"
                    >
                        <p class="text-xs text-gray-500">
                            Showing {{ departments.meta.from }}&ndash;{{ departments.meta.to }} of
                            {{ departments.meta.total }}
                        </p>
                        <Pagination :links="departments.meta.links" />
                    </div>
                </div>
            </div>
        </div>

        <NameFormModal
            v-model="form.name"
            :show="modal.show"
            :title="modal.target ? 'Edit department' : 'New department'"
            label="Department name"
            :submit-text="modal.target ? 'Save' : 'Create'"
            :error="form.errors.name"
            :processing="form.processing"
            @submit="submit"
            @close="closeModal"
        />

        <ConfirmDialog
            :show="deleteDialog.show"
            :title="`Delete department &quot;${deleteDialog.item?.name ?? ''}&quot;?`"
            message="Departments that are not used by any file can be deleted."
            :processing="deleting"
            @confirm="confirmDelete"
            @cancel="deleteDialog.show = false"
        />
    </AuthenticatedLayout>
</template>
