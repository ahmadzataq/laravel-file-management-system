<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { fileExtension, formatDate, formatSize } from '@/utils/format';

defineProps({
    documents: { type: Array, default: () => [] },
    showFolder: { type: Boolean, default: false }, // add a "Folder" column (search results, dashboard)
    showActions: { type: Boolean, default: false }, // Download for everyone, Edit/Delete for administrators
});

defineEmits(['delete']);

const page = usePage();
const isAdmin = computed(() => page.props.auth.isAdmin);
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-600">
                <tr>
                    <th scope="col" class="px-4 py-3">Title</th>
                    <th v-if="showFolder" scope="col" class="px-4 py-3">Folder</th>
                    <th scope="col" class="px-4 py-3">Department</th>
                    <th scope="col" class="px-4 py-3">Uploaded by</th>
                    <th scope="col" class="px-4 py-3">Upload date</th>
                    <th v-if="showActions" scope="col" class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <Link :href="route('documents.show', doc.id)" class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded bg-indigo-50 text-[10px] font-bold text-indigo-700"
                            >
                                {{ fileExtension(doc.file_name) }}
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-gray-900">{{ doc.title }}</span>
                                <span class="block truncate text-xs text-gray-500">
                                    {{ doc.file_name }} &middot; {{ formatSize(doc.size) }}
                                </span>
                            </span>
                        </Link>
                    </td>

                    <td v-if="showFolder" class="whitespace-nowrap px-4 py-3 text-gray-700">
                        <Link
                            v-if="doc.folder"
                            :href="route('folders.show', doc.folder.id)"
                            class="text-indigo-600 hover:underline"
                        >
                            {{ doc.folder.name }}
                        </Link>
                    </td>

                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ doc.department?.name ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ doc.uploaded_by ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ formatDate(doc.created_at) }}</td>

                    <td v-if="showActions" class="whitespace-nowrap px-4 py-3 text-right">
                        <a
                            :href="route('documents.download', doc.id)"
                            class="rounded px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100"
                        >
                            Download
                        </a>
                        <template v-if="isAdmin">
                            <Link
                                :href="route('documents.edit', doc.id)"
                                class="rounded px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                                @click="$emit('delete', doc)"
                            >
                                Delete
                            </button>
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
