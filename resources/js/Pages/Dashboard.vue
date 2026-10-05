<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DocumentTable from '@/Components/DocumentTable.vue';

defineProps({
    stats: { type: Object, required: true }, // { folders, documents, departments }
    latestDocuments: { type: Array, default: () => [] },
});

const cards = [
    { key: 'folders', label: 'Total folders' },
    { key: 'documents', label: 'Total files' },
    { key: 'departments', label: 'Total departments' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="card in cards"
                        :key="card.key"
                        class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-200"
                    >
                        <p class="text-sm text-gray-500">{{ card.label }}</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ stats[card.key] }}</p>
                    </div>
                </div>

                <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                    <div class="border-b border-gray-200 px-4 py-4">
                        <h3 class="font-semibold text-gray-900">Latest files</h3>
                        <p class="text-sm text-gray-500">The 10 most recently uploaded files.</p>
                    </div>

                    <DocumentTable v-if="latestDocuments.length" :documents="latestDocuments" show-folder />
                    <p v-else class="p-8 text-center text-sm text-gray-500">
                        No files have been uploaded yet.
                    </p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
