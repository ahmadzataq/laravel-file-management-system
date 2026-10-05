<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: 'Delete' },
    processing: { type: Boolean, default: false },
});

defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Modal :show="show" max-width="md" @close="$emit('cancel')">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ message }}</p>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="$emit('cancel')">Cancel</SecondaryButton>
                <DangerButton
                    type="button"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="$emit('confirm')"
                >
                    {{ confirmText }}
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>
