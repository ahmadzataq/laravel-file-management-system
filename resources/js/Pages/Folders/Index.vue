<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import ButtonLink from '@/Components/ButtonLink.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import DocumentTable from '@/Components/DocumentTable.vue';
import NameFormModal from '@/Components/NameFormModal.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { plural } from '@/utils/format';

const props = defineProps({
    folder: { type: Object, default: null }, // null = top level
    breadcrumbs: { type: Array, default: () => [] },
    folders: { type: Array, default: () => [] }, // sub-folders of the current folder
    documents: { type: Object, required: true }, // paginated: { data, links, meta }
    departments: { type: Array, default: () => [] },
    filters: { type: Object, required: true }, // { q, department }
    searching: { type: Boolean, default: false },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.isAdmin);

/* ---------------------------------------------------------------- search & filter */

const search = reactive({
    q: props.filters.q ?? '',
    department: props.filters.department ?? '',
});

let timer;

// Search as you type (debounced). Results always cover every folder.
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(applyFilters, 300);
});

function applyFilters() {
    router.get(
        route('folders.index'),
        { q: search.q || undefined, department: search.department || undefined },
        { preserveState: true, replace: true },
    );
}

function clearFilters() {
    search.q = '';
    search.department = '';
}

/* ---------------------------------------------------------------- create / rename folder */

const folderModal = reactive({ show: false, target: null }); // target = folder being renamed, null = creating
const folderForm = useForm({ name: '', parent_id: null });

function openCreateFolder() {
    folderForm.reset();
    folderForm.clearErrors();
    folderForm.parent_id = props.folder?.id ?? null;
    folderModal.target = null;
    folderModal.show = true;
}

function openRenameFolder(folder) {
    folderForm.clearErrors();
    folderForm.name = folder.name;
    folderModal.target = folder;
    folderModal.show = true;
}

function closeFolderModal() {
    folderModal.show = false;
}

function submitFolder() {
    const options = { preserveScroll: true, onSuccess: closeFolderModal };

    if (folderModal.target) {
        folderForm.put(route('folders.update', folderModal.target.id), options);
    } else {
        folderForm.post(route('folders.store'), options);
    }
}

/* ---------------------------------------------------------------- delete folder / file */

const deleteDialog = reactive({ show: false, type: null, item: null });
const deleting = ref(false);

const deleteTitle = computed(() => {
    const item = deleteDialog.item;

    if (!item) {
        return '';
    }

    return deleteDialog.type === 'folder' ? `Delete folder "${item.name}"?` : `Delete file "${item.title}"?`;
});

const deleteMessage = computed(() =>
    deleteDialog.type === 'folder'
        ? 'All sub-folders and files inside this folder will be permanently deleted.'
        : 'The file will be permanently deleted.',
);

function askDelete(type, item) {
    deleteDialog.type = type;
    deleteDialog.item = item;
    deleteDialog.show = true;
}

function confirmDelete() {
    const url =
        deleteDialog.type === 'folder'
            ? route('folders.destroy', deleteDialog.item.id)
            : route('documents.destroy', deleteDialog.item.id);

    router.delete(url, {
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
    <Head title="Files" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Files</h2>

                <div v-if="isAdmin && !searching" class="flex gap-2">
                    <PrimaryButton type="button" @click="openCreateFolder">New folder</PrimaryButton>
                    <ButtonLink v-if="folder" :href="route('documents.create', { folder: folder.id })">
                        Upload file
                    </ButtonLink>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb + search & filter -->
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <Breadcrumbs v-if="!searching" :items="breadcrumbs" />
                    <p v-else class="text-sm text-gray-600">Search results from all folders</p>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <TextInput
                            v-model="search.q"
                            type="search"
                            class="block w-full text-sm sm:w-80"
                            placeholder="Search title, file name, department"
                            aria-label="Search files"
                        />
                        <select
                            v-model="search.department"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            aria-label="Filter by department"
                        >
                            <option value="">All departments</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                        <button
                            v-if="searching"
                            type="button"
                            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm hover:bg-gray-50"
                            @click="clearFilters"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Sub-folders -->
                <section v-if="!searching && folders.length">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">Folders</h3>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="item in folders"
                            :key="item.id"
                            class="flex items-center justify-between gap-2 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200"
                        >
                            <Link :href="route('folders.show', item.id)" class="flex min-w-0 items-center gap-3">
                                <svg class="h-8 w-8 shrink-0 text-amber-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M10 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8l-2-2z" />
                                </svg>
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-gray-900">{{ item.name }}</span>
                                    <span class="block text-xs text-gray-500">
                                        {{ plural(item.children_count, 'folder') }} &middot;
                                        {{ plural(item.documents_count, 'file') }}
                                    </span>
                                </span>
                            </Link>

                            <div v-if="isAdmin" class="flex shrink-0 gap-1">
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100"
                                    @click="openRenameFolder(item)"
                                >
                                    Rename
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                                    @click="askDelete('folder', item)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Files of the current folder, or search results -->
                <section
                    v-if="searching || folder"
                    class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200"
                >
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h3 class="font-semibold text-gray-900">{{ searching ? 'Search results' : 'Files' }}</h3>
                        <p class="text-xs text-gray-500">{{ plural(documents.meta.total, 'file') }}</p>
                    </div>

                    <DocumentTable
                        v-if="documents.data.length"
                        :documents="documents.data"
                        :show-folder="searching"
                        show-actions
                        @delete="askDelete('document', $event)"
                    />
                    <p v-else class="p-8 text-center text-sm text-gray-500">
                        {{ searching ? 'No files match your search.' : 'This folder has no files yet.' }}
                    </p>

                    <div
                        v-if="documents.meta.last_page > 1"
                        class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 px-4 py-3"
                    >
                        <p class="text-xs text-gray-500">
                            Showing {{ documents.meta.from }}&ndash;{{ documents.meta.to }} of {{ documents.meta.total }}
                        </p>
                        <Pagination :links="documents.meta.links" />
                    </div>
                </section>

                <!-- Nothing here at all -->
                <p
                    v-if="!searching && !folder && !folders.length"
                    class="rounded-lg bg-white p-8 text-center text-sm text-gray-500 shadow-sm ring-1 ring-gray-200"
                >
                    There are no folders yet.
                </p>
                <p
                    v-else-if="!searching && folder && !folders.length && !documents.data.length"
                    class="text-center text-sm text-gray-500"
                >
                    This folder is empty.
                </p>
            </div>
        </div>

        <NameFormModal
            v-model="folderForm.name"
            :show="folderModal.show"
            :title="folderModal.target ? 'Rename folder' : 'New folder'"
            label="Folder name"
            :submit-text="folderModal.target ? 'Rename' : 'Create'"
            :error="folderForm.errors.name"
            :processing="folderForm.processing"
            @submit="submitFolder"
            @close="closeFolderModal"
        />

        <ConfirmDialog
            :show="deleteDialog.show"
            :title="deleteTitle"
            :message="deleteMessage"
            :processing="deleting"
            @confirm="confirmDelete"
            @cancel="deleteDialog.show = false"
        />
    </AuthenticatedLayout>
</template>
