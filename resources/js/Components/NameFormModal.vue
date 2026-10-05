<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// A modal with a single "name" field. Used for folders and departments.
const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    label: { type: String, default: 'Name' },
    submitText: { type: String, default: 'Save' },
    modelValue: { type: String, default: '' },
    error: { type: String, default: '' },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'submit', 'close']);

const name = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const input = ref(null);

watch(
    () => props.show,
    (show) => {
        if (show) {
            nextTick(() => input.value?.focus());
        }
    },
);
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <form class="p-6" @submit.prevent="emit('submit')">
            <h2 class="text-lg font-semibold text-gray-900">{{ title }}</h2>

            <div class="mt-4">
                <InputLabel for="name" :value="label" />
                <TextInput
                    id="name"
                    ref="input"
                    v-model="name"
                    type="text"
                    class="mt-1 block w-full"
                    maxlength="255"
                    autocomplete="off"
                    required
                />
                <InputError class="mt-2" :message="error" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="emit('close')">Cancel</SecondaryButton>
                <PrimaryButton :class="{ 'opacity-25': processing }" :disabled="processing">
                    {{ submitText }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
