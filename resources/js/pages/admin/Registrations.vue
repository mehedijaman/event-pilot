<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Registrations',
                href: admin.registrations.index(),
            },
        ],
    },
});

const props = defineProps<{
    registrations: {
        data: Array<{
            id: number;
            ticket_code: string;
            name: string;
            email: string;
            phone: string;
            seat_position: string;
            payment_method: string;
            transaction_id: string;
            amount: number;
            payment_status: string;
            rejection_reason: string | null;
            verified_at: string | null;
            checked_in_at: string | null;
            created_at: string;
            package: { name: string };
            event: { name: string };
            verifier: { name: string } | null;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filter: string;
}>();

const rejectModal = ref<{ open: boolean; registrationId: number | null }>({ open: false, registrationId: null });
const rejectionReason = ref('');

const tabs = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Verified', value: 'verified' },
    { label: 'Rejected', value: 'rejected' },
];

function switchTab(tab: string) {
    router.get(admin.registrations.index(), tab === 'all' ? {} : { status: tab }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function verify(id: number) {
    if (!confirm('Verify this registration?')) return;
    router.post(admin.registrations.verify(id), {}, {
        preserveScroll: true,
    });
}

function openReject(id: number) {
    rejectModal.value = { open: true, registrationId: id };
    rejectionReason.value = '';
}

function confirmReject() {
    if (!rejectionReason.value.trim()) return;
    router.post(admin.registrations.reject(rejectModal.value.registrationId!), {
        rejection_reason: rejectionReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            rejectModal.value = { open: false, registrationId: null };
            rejectionReason.value = '';
        },
    });
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = { pending: 'Pending', verified: 'Verified', rejected: 'Rejected' };
    return labels[status] ?? status;
}

function statusColor(status: string): string {
    const colors: Record<string, string> = {
        pending: 'text-yellow-700 bg-yellow-50 ring-yellow-600/20 dark:text-yellow-400 dark:bg-yellow-400/10 dark:ring-yellow-400/20',
        verified: 'text-green-700 bg-green-50 ring-green-600/20 dark:text-green-400 dark:bg-green-400/10 dark:ring-green-400/20',
        rejected: 'text-red-700 bg-red-50 ring-red-600/10 dark:text-red-400 dark:bg-red-400/10 dark:ring-red-400/20',
    };
    return colors[status] ?? 'text-gray-700 bg-gray-50 ring-gray-600/20';
}

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Registrations" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-semibold">Registrations</h1>

        <div class="flex gap-1 rounded-lg bg-muted p-1 self-start">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium transition"
                :class="filter === tab.value ? 'bg-white dark:bg-sidebar-accent shadow-sm' : 'hover:text-foreground/60'"
                @click="switchTab(tab.value)"
            >
                {{ tab.label }}
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <table class="min-w-full divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                <thead>
                    <tr class="bg-muted/50">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Package</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Seat</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Payment</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="r in registrations.data" :key="r.id" class="hover:bg-muted/30 transition">
                        <td class="whitespace-nowrap px-4 py-3">
                            <div class="text-sm font-medium">{{ r.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ r.ticket_code.slice(0, 8) }}...</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <div>{{ r.email }}</div>
                            <div class="text-xs text-muted-foreground">{{ r.phone }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">{{ r.package.name }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm capitalize">{{ r.seat_position }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">
                            <div class="capitalize">{{ r.payment_method }}</div>
                            <div class="text-xs text-muted-foreground font-mono">{{ r.transaction_id }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">৳{{ r.amount }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset" :class="statusColor(r.payment_status)">
                                {{ statusLabel(r.payment_status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted-foreground">{{ formatDate(r.created_at) }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <div v-if="r.payment_status === 'pending'" class="flex gap-1">
                                <button type="button" class="rounded bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700 transition" @click="verify(r.id)">
                                    Verify
                                </button>
                                <button type="button" class="rounded bg-red-600 px-2 py-1 text-xs text-white hover:bg-red-700 transition" @click="openReject(r.id)">
                                    Reject
                                </button>
                            </div>
                            <div v-else-if="r.verified_at" class="text-xs text-muted-foreground">
                                by {{ r.verifier?.name ?? '—' }}
                            </div>
                        </td>
                    </tr>
                    <tr v-if="registrations.data.length === 0">
                        <td colspan="9" class="px-4 py-12 text-center text-sm text-muted-foreground">
                            No registrations found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="registrations.last_page > 1" class="flex items-center justify-between">
            <p class="text-sm text-muted-foreground">
                Showing {{ registrations.from }}–{{ registrations.to }} of {{ registrations.total }}
            </p>
            <div class="flex gap-1">
                <Link
                    v-for="link in registrations.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-md px-3 py-1.5 text-sm transition"
                    :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                    v-html="link.label"
                    preserve-state
                    preserve-scroll
                />
            </div>
        </div>
    </div>

    <div v-if="rejectModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="rejectModal.open = false">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-sidebar-accent">
            <h2 class="text-lg font-semibold">Reject Registration</h2>
            <p class="mt-1 text-sm text-muted-foreground">Provide a reason for rejection.</p>
            <textarea
                v-model="rejectionReason"
                rows="3"
                class="mt-4 w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                placeholder="Enter rejection reason..."
            />
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="rounded-md border border-sidebar-border/70 px-4 py-1.5 text-sm transition hover:bg-muted" @click="rejectModal.open = false">
                    Cancel
                </button>
                <button type="button" class="rounded-md bg-red-600 px-4 py-1.5 text-sm text-white transition hover:bg-red-700 disabled:opacity-50" :disabled="!rejectionReason.trim()" @click="confirmReject">
                    Confirm Reject
                </button>
            </div>
        </div>
    </div>
</template>
