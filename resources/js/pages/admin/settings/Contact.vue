<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';

const page = usePage();
const s = (page.props as any).settings as {
    contact_email: string | null;
    contact_phone: string | null;
    contact_address: string | null;
    social_facebook: string | null;
    social_twitter: string | null;
    social_instagram: string | null;
} | null;

const form = ref({
    contact_email: s?.contact_email ?? '',
    contact_phone: s?.contact_phone ?? '',
    contact_address: s?.contact_address ?? '',
    social_facebook: s?.social_facebook ?? '',
    social_twitter: s?.social_twitter ?? '',
    social_instagram: s?.social_instagram ?? '',
});
const saving = ref(false);
const success = ref((page.props as any).flash?.success ?? '');
const errors = ref((page.props as any).errors ?? {});

watch(() => (page.props as any).flash?.success, (val) => { success.value = val ?? ''; });
watch(() => (page.props as any).errors, (val) => { errors.value = val ?? {}; }, { deep: true });

function submit() {
    saving.value = true;
    router.put(admin.settings.contact(), form.value, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head title="Contact Settings" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div v-if="success" class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-400/10 dark:text-green-400">{{ success }}</div>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">Contact Details</h2>
                <div>
                    <label class="block text-sm font-medium" for="contact_email">Email</label>
                    <input id="contact_email" v-model="form.contact_email" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="email" />
                    <p v-if="errors.contact_email" class="mt-1 text-xs text-red-600">{{ errors.contact_email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="contact_phone">Phone</label>
                    <input id="contact_phone" v-model="form.contact_phone" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" />
                    <p v-if="errors.contact_phone" class="mt-1 text-xs text-red-600">{{ errors.contact_phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="contact_address">Address</label>
                    <textarea id="contact_address" v-model="form.contact_address" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" rows="2"></textarea>
                    <p v-if="errors.contact_address" class="mt-1 text-xs text-red-600">{{ errors.contact_address }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">Social Links</h2>
                <div>
                    <label class="block text-sm font-medium" for="social_facebook">Facebook URL</label>
                    <input id="social_facebook" v-model="form.social_facebook" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="url" placeholder="https://facebook.com/..." />
                    <p v-if="errors.social_facebook" class="mt-1 text-xs text-red-600">{{ errors.social_facebook }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="social_twitter">Twitter URL</label>
                    <input id="social_twitter" v-model="form.social_twitter" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="url" placeholder="https://twitter.com/..." />
                    <p v-if="errors.social_twitter" class="mt-1 text-xs text-red-600">{{ errors.social_twitter }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="social_instagram">Instagram URL</label>
                    <input id="social_instagram" v-model="form.social_instagram" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="url" placeholder="https://instagram.com/..." />
                    <p v-if="errors.social_instagram" class="mt-1 text-xs text-red-600">{{ errors.social_instagram }}</p>
                </div>
            </div>

            <div class="flex justify-end pb-8">
                <button type="submit" class="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50" :disabled="saving">{{ saving ? 'Saving...' : 'Save Settings' }}</button>
            </div>
        </form>
    </div>
</template>
