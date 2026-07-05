<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import admin from '@/routes/admin';

interface PaymentMethod {
    id: number;
    name: string;
    slug: string;
    account_type: string | null;
    account_number: string | null;
    instructions: string | null;
    is_active: boolean;
}

const props = defineProps<{
    paymentMethods: PaymentMethod[];
}>();

const page = usePage();
const success = ref((page.props as any).flash?.success ?? '');

watch(() => (page.props as any).flash?.success, (val) => { success.value = val ?? ''; });

function destroy(id: number) {
    if (confirm('Are you sure you want to delete this payment method?')) {
        router.delete(admin.paymentMethods.destroy(id));
    }
}
</script>

<template>
    <Head title="Payment Methods" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Payment Methods"
                description="Manage payment methods available for registration"
            />
            <Button as-child>
                <Link :href="admin.paymentMethods.create()">
                    Add Payment Method
                </Link>
            </Button>
        </div>

        <div v-if="success" class="rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-400/10 dark:text-green-400">{{ success }}</div>

        <div class="rounded-xl border border-sidebar-border/70">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-sidebar-border/70 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Account Type</th>
                        <th class="px-4 py-3 font-medium">Account Number</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="method in paymentMethods" :key="method.id" class="border-b border-sidebar-border/70 last:border-0">
                        <td class="px-4 py-3">{{ method.name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ method.slug }}</td>
                        <td class="px-4 py-3">{{ method.account_type ?? '—' }}</td>
                        <td class="px-4 py-3">{{ method.account_number ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                :class="method.is_active ? 'bg-green-50 text-green-700 dark:bg-green-400/10 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                            >
                                {{ method.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Link
                                    :href="admin.paymentMethods.edit(method.id)"
                                    class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="text-sm text-red-600 hover:underline dark:text-red-400"
                                    @click="destroy(method.id)"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="paymentMethods.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                            No payment methods found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
