<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    // [{ id, name }, ...] from the root folder down to the current folder
    items: { type: Array, default: () => [] },
    // true = the last item is also a link (used on file pages)
    linkLast: { type: Boolean, default: false },
});
</script>

<template>
    <nav class="flex flex-wrap items-center gap-1.5 text-sm" aria-label="Breadcrumb">
        <Link :href="route('folders.index')" class="font-medium text-indigo-600 hover:underline">
            Home
        </Link>

        <template v-for="(item, index) in items" :key="item.id">
            <span class="text-gray-400" aria-hidden="true">/</span>

            <span
                v-if="index === items.length - 1 && !linkLast"
                class="font-medium text-gray-900"
                aria-current="page"
            >
                {{ item.name }}
            </span>
            <Link v-else :href="route('folders.show', item.id)" class="text-indigo-600 hover:underline">
                {{ item.name }}
            </Link>
        </template>
    </nav>
</template>
