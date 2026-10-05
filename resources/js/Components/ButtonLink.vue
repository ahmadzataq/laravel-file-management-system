<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
    variant: { type: String, default: 'primary' }, // 'primary' | 'secondary'
    native: { type: Boolean, default: false }, // true = plain <a> (for file downloads)
});

const base =
    'inline-flex items-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';

const variants = {
    primary: 'border-transparent bg-gray-800 text-white hover:bg-gray-700',
    secondary: 'border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50',
};

const classes = computed(() => `${base} ${variants[props.variant]}`);
</script>

<template>
    <component :is="native ? 'a' : Link" :href="href" :class="classes">
        <slot />
    </component>
</template>
