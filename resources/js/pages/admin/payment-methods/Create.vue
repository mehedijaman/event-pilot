<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import admin from '@/routes/admin';

const form = ref({
    name: '',
    slug: '',
    account_type: '',
    account_number: '',
    instructions: '',
    is_active: true,
});
const saving = ref(false);
const errors = ref<Record<string, string>>({});

function generateSlug() {
    form.value.slug = form.value.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

function submit() {
    saving.value = true;
    errors.value = {};
    router.post(admin.paymentMethods.store(), form.value, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
        onError: (e) => { errors.value = e; },
    });
}
</script>

<template>
    <Head title="Add Payment Method" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <Heading
            title="Add Payment Method"
            description="Create a new payment method for registration"
        />

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">Details</h2>
                <div>
                    <label class="block text-sm font-medium" for="name">Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                        type="text"
                        @blur="generateSlug"
                    />
                    <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="slug">Slug</label>
                    <input
                        id="slug"
                        v-model="form.slug"
                        class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                        type="text"
                    />
                    <p v-if="errors.slug" class="mt-1 text-xs text-red-600">{{ errors.slug }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="account_type">Account Type</label>
                    <select
                        id="account_type"
                        v-model="form.account_type"
                        class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                    >
                        <option value="">None</option>
                        <option value="mobile">Mobile</option>
                        <option value="bank">Bank</option>
                    </select>
                    <p v-if="errors.account_type" class="mt-1 text-xs text-red-600">{{ errors.account_type }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="account_number">Account Number</label>
                    <input
                        id="account_number"
                        v-model="form.account_number"
                        class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                        type="text"
                        placeholder="e.g. 01XXXXXXXXX"
                    />
                    <p v-if="errors.account_number" class="mt-1 text-xs text-red-600">{{ errors.account_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="instructions">Instructions</label>
                    <textarea
                        id="instructions"
                        v-model="form.instructions"
                        class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                        rows="3"
                        placeholder="Optional payment instructions"
                    ></textarea>
                    <p v-if="errors.instructions" class="mt-1 text-xs text-red-600">{{ errors.instructions }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-sidebar-border/70"
                    />
                    <label class="text-sm font-medium" for="is_active">Active</label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pb-8">
                <Link
                    :href="admin.paymentMethods.index()"
                    class="rounded-md border border-sidebar-border/70 px-4 py-2 text-sm font-medium transition hover:bg-sidebar-accent"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50"
                    :disabled="saving"
                >
                    {{ saving ? 'Creating...' : 'Create Payment Method' }}
                </button>
            </div>
        </form>
    </div>
</template>
