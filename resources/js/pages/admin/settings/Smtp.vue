<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';

const page = usePage();
const s = (page.props as any).settings as {
    smtp_host: string | null;
    smtp_port: number | null;
    smtp_username: string | null;
    smtp_encryption: string | null;
    smtp_from_address: string | null;
    smtp_from_name: string | null;
} | null;

const form = ref({
    smtp_host: s?.smtp_host ?? '',
    smtp_port: s?.smtp_port ?? null,
    smtp_username: s?.smtp_username ?? '',
    smtp_password: '',
    smtp_encryption: s?.smtp_encryption ?? '',
    smtp_from_address: s?.smtp_from_address ?? '',
    smtp_from_name: s?.smtp_from_name ?? '',
});
const smtpPasswordVisible = ref(false);
const saving = ref(false);
const success = ref((page.props as any).flash?.success ?? '');
const errors = ref((page.props as any).errors ?? {});

watch(() => (page.props as any).flash?.success, (val) => { success.value = val ?? ''; });
watch(() => (page.props as any).errors, (val) => { errors.value = val ?? {}; }, { deep: true });

function submit() {
    saving.value = true;
    const data: Record<string, any> = { ...form.value };
    if (!data.smtp_password) delete data.smtp_password;

    router.put(admin.settings.smtp(), data, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head title="SMTP Settings" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div v-if="success" class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-400/10 dark:text-green-400">{{ success }}</div>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">SMTP Configuration</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" for="smtp_host">Host</label>
                        <input id="smtp_host" v-model="form.smtp_host" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" />
                        <p v-if="errors.smtp_host" class="mt-1 text-xs text-red-600">{{ errors.smtp_host }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="smtp_port">Port</label>
                        <input id="smtp_port" v-model.number="form.smtp_port" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="number" />
                        <p v-if="errors.smtp_port" class="mt-1 text-xs text-red-600">{{ errors.smtp_port }}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="smtp_username">Username</label>
                    <input id="smtp_username" v-model="form.smtp_username" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" />
                    <p v-if="errors.smtp_username" class="mt-1 text-xs text-red-600">{{ errors.smtp_username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="smtp_password">Password</label>
                    <div class="flex gap-2">
                        <input v-if="!smtpPasswordVisible" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border flex-1" type="password" value="••••••••" disabled />
                        <input v-else id="smtp_password" v-model="form.smtp_password" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border flex-1" type="password" />
                    </div>
                    <button type="button" class="mt-1 text-xs text-muted-foreground hover:underline" @click="smtpPasswordVisible = !smtpPasswordVisible">{{ smtpPasswordVisible ? 'Cancel' : 'Change password' }}</button>
                    <p v-if="errors.smtp_password" class="mt-1 text-xs text-red-600">{{ errors.smtp_password }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="smtp_encryption">Encryption</label>
                    <select id="smtp_encryption" v-model="form.smtp_encryption" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border">
                        <option value="">None</option>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                    <p v-if="errors.smtp_encryption" class="mt-1 text-xs text-red-600">{{ errors.smtp_encryption }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" for="smtp_from_address">From Address</label>
                        <input id="smtp_from_address" v-model="form.smtp_from_address" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="email" />
                        <p v-if="errors.smtp_from_address" class="mt-1 text-xs text-red-600">{{ errors.smtp_from_address }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="smtp_from_name">From Name</label>
                        <input id="smtp_from_name" v-model="form.smtp_from_name" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" />
                        <p v-if="errors.smtp_from_name" class="mt-1 text-xs text-red-600">{{ errors.smtp_from_name }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pb-8">
                <button type="submit" class="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50" :disabled="saving">{{ saving ? 'Saving...' : 'Save Settings' }}</button>
            </div>
        </form>
    </div>
</template>
