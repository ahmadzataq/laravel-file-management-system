<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import ButtonLink from '@/Components/ButtonLink.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { formatDate, formatSize } from '@/utils/format';

const props = defineProps({
    document: { type: Object, required: true },
    breadcrumbs: { type: Array, default: () => [] },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.isAdmin);

const previewUrl = computed(() => route('documents.preview', props.document.id));
const isImage = computed(() => props.document.mime_type?.startsWith('image/'));

const showDelete = ref(false);
const deleting = ref(false);

function destroy() {
    router.delete(route('documents.destroy', props.document.id), {
        onStart: () => (deleting.value = true),
        onFinish: () => {
            deleting.value = false;
            showDelete.value = false;
        },
    });
}
</script>

<template>
    <Head :title="document.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ document.title }}</h2>

                <div class="flex flex-wrap gap-2">
                    <ButtonLink :href="route('documents.download', document.id)" native>Download</ButtonLink>
                    <template v-if="isAdmin">
                        <ButtonLink :href="route('documents.edit', document.id)" variant="secondary">Edit</ButtonLink>
                        <DangerButton type="button" @click="showDelete = true">Delete</DangerButton>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                <Breadcrumbs :items="breadcrumbs" link-last />

                <!-- File detail -->
                <dl class="grid gap-x-6 gap-y-4 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Folder</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <Link
                                v-if="document.folder"
                                :href="route('folders.show', document.folder.id)"
                                class="text-indigo-600 hover:underline"
                            >
                                {{ document.folder.name }}
                            </Link>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">File name</dt>
                        <dd class="mt-1 break-all text-sm text-gray-900">{{ document.file_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Title</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ document.title }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Department</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ document.department?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Uploaded by</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ document.uploaded_by ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Upload date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(document.created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Size</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatSize(document.size) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ document.mime_type ?? '—' }}</dd>
                    </div>
                </dl>

                <!-- Preview (PDF and images only) -->
                <section
                    v-if="document.previewable"
                    class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200"
                >
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h3 class="font-semibold text-gray-900">Preview</h3>
                    </div>

                    <img
                        v-if="isImage"
                        :src="previewUrl"
                        :alt="document.title"
                        class="mx-auto max-h-[70vh] max-w-full p-4"
                    />
                    <iframe v-else :src="previewUrl" :title="`Preview of ${document.title}`" class="h-[75vh] w-full" />
                </section>
                <p v-else class="text-center text-sm text-gray-500">
                    This file type cannot be previewed in the browser. Use Download to open it.
                </p>
            </div>
        </div>

        <ConfirmDialog
            :show="showDelete"
            :title="`Delete file &quot;${document.title}&quot;?`"
            message="The file will be permanently deleted."
            :processing="deleting"
            @confirm="destroy"
            @cancel="showDelete = false"
        />
    </AuthenticatedLayout>
</template>
