<script setup>
import { ref } from 'vue';
import { formatSize } from '@/utils/format';

defineProps({
    modelValue: { default: null }, // the selected File, or null
    accept: { type: String, default: '' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const dragging = ref(false);

function pick(files) {
    if (files && files.length > 0) {
        emit('update:modelValue', files[0]);
    }
}

function onDrop(event) {
    dragging.value = false;
    pick(event.dataTransfer?.files);
}
</script>

<template>
    <div>
        <!-- A <label> around the hidden input: click opens the file dialog, drag & drop is handled below. -->
        <label
            class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-6 py-10 text-center transition focus-within:ring-2 focus-within:ring-indigo-500"
            :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 bg-white hover:border-indigo-400'"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"
                />
            </svg>

            <span v-if="modelValue" class="mt-2 text-sm font-medium text-gray-900">
                {{ modelValue.name }} ({{ formatSize(modelValue.size) }})
            </span>
            <span v-else class="mt-2 text-sm font-medium text-gray-700">
                Drag &amp; drop a file here, or click to browse
            </span>
            <span v-if="modelValue" class="mt-1 text-xs text-gray-500">Drop or choose another file to replace it</span>

            <input type="file" class="sr-only" :accept="accept" @change="pick($event.target.files)" />
        </label>

        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
