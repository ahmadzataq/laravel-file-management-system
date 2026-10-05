<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: Number, required: true },
});

const messages = {
    403: { title: 'Access denied', text: 'You do not have permission to do this.' },
    404: { title: 'Not found', text: 'The page or file you are looking for does not exist.' },
    500: { title: 'Server error', text: 'Something went wrong on our side. Please try again later.' },
    503: { title: 'Service unavailable', text: 'The application is in maintenance. Please come back soon.' },
};

const content = computed(
    () => messages[props.status] ?? { title: 'Error', text: 'Something went wrong.' },
);
</script>

<template>
    <Head :title="content.title" />

    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-sm ring-1 ring-gray-200">
            <p class="text-5xl font-semibold text-indigo-600">{{ status }}</p>
            <h1 class="mt-4 text-xl font-semibold text-gray-900">{{ content.title }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ content.text }}</p>

            <Link
                :href="route('dashboard')"
                class="mt-6 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
            >
                Back to dashboard
            </Link>
        </div>
    </div>
</template>
