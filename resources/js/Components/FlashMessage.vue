<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// { type: 'success' | 'error', text: string } or null
const message = ref(null);
let timer;

// "flash" is shared by HandleInertiaRequests and is replaced on every visit.
watch(
    () => page.props.flash,
    (flash) => {
        clearTimeout(timer);

        const type = flash?.error ? 'error' : flash?.success ? 'success' : null;
        message.value = type ? { type, text: flash[type] } : null;

        if (type) {
            timer = setTimeout(() => (message.value = null), 6000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" aria-live="polite">
        <div
            v-if="message"
            class="flex items-start justify-between gap-4 rounded-md border px-4 py-3 text-sm"
            :class="
                message.type === 'error'
                    ? 'border-red-200 bg-red-50 text-red-800'
                    : 'border-green-200 bg-green-50 text-green-800'
            "
            role="status"
        >
            <span>{{ message.text }}</span>
            <button
                type="button"
                class="shrink-0 font-semibold leading-none"
                aria-label="Dismiss message"
                @click="message = null"
            >
                &times;
            </button>
        </div>
    </div>
</template>
