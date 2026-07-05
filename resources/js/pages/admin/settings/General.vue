<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';

const page = usePage();
const s = (page.props as any).settings as {
    site_name: string | null;
    slogan: string | null;
    logo_url: string | null;
    favicon_url: string | null;
} | null;

const form = ref({
    site_name: s?.site_name ?? '',
    slogan: s?.slogan ?? '',
});
const logoFile = ref<File | null>(null);
const faviconFile = ref<File | null>(null);
const logoPreview = ref(s?.logo_url ?? null);
const faviconPreview = ref(s?.favicon_url ?? null);
const saving = ref(false);
const success = ref((page.props as any).flash?.success ?? '');
const errors = ref((page.props as any).errors ?? {});

watch(() => (page.props as any).flash?.success, (val) => { success.value = val ?? ''; });
watch(() => (page.props as any).errors, (val) => { errors.value = val ?? {}; }, { deep: true });

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        logoFile.value = file;
        logoPreview.value = URL.createObjectURL(file);
    }
}

function removeLogo() {
    logoFile.value = null;
    logoPreview.value = null;
}

function onFaviconChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        faviconFile.value = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
}

function removeFavicon() {
    faviconFile.value = null;
    faviconPreview.value = null;
}

function submit() {
    saving.value = true;
    const data = new FormData();
    data.append('_method', 'PUT');
    data.append('site_name', form.value.site_name);
    data.append('slogan', form.value.slogan);
    if (logoFile.value) data.append('logo', logoFile.value);
    if (faviconFile.value) data.append('favicon', faviconFile.value);

    router.post(admin.settings.general(), data, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head title="General Settings" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div v-if="success" class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-400/10 dark:text-green-400">{{ success }}</div>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">General</h2>
                <div>
                    <label class="block text-sm font-medium" for="site_name">Site Name</label>
                    <input id="site_name" v-model="form.site_name" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" />
                    <p v-if="errors.site_name" class="mt-1 text-xs text-red-600">{{ errors.site_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="slogan">Slogan</label>
                    <textarea id="slogan" v-model="form.slogan" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" rows="2"></textarea>
                    <p v-if="errors.slogan" class="mt-1 text-xs text-red-600">{{ errors.slogan }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">Branding</h2>
                <div>
                    <label class="block text-sm font-medium">Logo</label>
                    <div v-if="logoPreview" class="mt-2 mb-2 flex items-center gap-3">
                        <img :src="logoPreview" class="h-12 rounded object-contain" alt="Logo preview" />
                        <button type="button" class="text-xs text-red-600 hover:underline" @click="removeLogo">Remove</button>
                    </div>
                    <input class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" @change="onLogoChange" />
                    <p v-if="errors.logo" class="mt-1 text-xs text-red-600">{{ errors.logo }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Favicon</label>
                    <div v-if="faviconPreview" class="mt-2 mb-2 flex items-center gap-3">
                        <img :src="faviconPreview" class="h-8 w-8 rounded object-contain" alt="Favicon preview" />
                        <button type="button" class="text-xs text-red-600 hover:underline" @click="removeFavicon">Remove</button>
                    </div>
                    <input class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="file" accept="image/png,image/x-icon,image/svg+xml" @change="onFaviconChange" />
                    <p v-if="errors.favicon" class="mt-1 text-xs text-red-600">{{ errors.favicon }}</p>
                </div>
            </div>

            <div class="flex justify-end pb-8">
                <button type="submit" class="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50" :disabled="saving">{{ saving ? 'Saving...' : 'Save Settings' }}</button>
            </div>
        </form>
    </div>
</template>
