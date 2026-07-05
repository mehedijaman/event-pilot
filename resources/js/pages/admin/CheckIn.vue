<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Check-In',
                href: admin.checkIn.index(),
            },
        ],
    },
});

const props = defineProps<{
    lookup: {
        id: number;
        ticket_code: string;
        name: string;
        email: string;
        seat_position: string;
        payment_status: string;
        checked_in_at: string | null;
        quantity: number;
        event: { name: string };
        package: { name: string; price: number };
    } | null;
}>();

const ticketCode = ref('');
const processing = ref(false);

function search() {
    if (!ticketCode.value.trim()) return;
    router.get(admin.checkIn.index(), { ticket_code: ticketCode.value }, {
        preserveState: true,
    });
}

function doCheckIn() {
    if (!props.lookup) return;
    processing.value = true;
    router.post(admin.checkIn.store(), { ticket_code: ticketCode.value }, {
        preserveScroll: true,
        onSuccess: () => {
            ticketCode.value = '';
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
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
    return colors[status] ?? '';
}

function canCheckIn(): boolean {
    if (!props.lookup) return false;
    if (props.lookup.payment_status !== 'verified') return false;
    if (props.lookup.checked_in_at) return false;
    return true;
}

function checkInStatus(): string {
    if (!props.lookup) return '';
    if (props.lookup.checked_in_at) return 'Already checked in';
    if (props.lookup.payment_status === 'pending') return 'Payment pending';
    if (props.lookup.payment_status === 'rejected') return 'Registration rejected';
    return 'Ready for check-in';
}
</script>

<template>
    <Head title="Check-In" />

    <div class="flex h-full flex-1 flex-col items-center gap-6 overflow-x-auto rounded-xl p-4 pt-12">
        <div class="w-full max-w-md text-center">
            <h1 class="text-xl font-semibold">Check-In</h1>
            <p class="mt-1 text-sm text-muted-foreground">Enter the ticket code to check in an attendee.</p>
        </div>

        <form class="w-full max-w-md" @submit.prevent="search">
            <div class="flex gap-2">
                <input
                    v-model="ticketCode"
                    type="text"
                    placeholder="Scan or type ticket code..."
                    class="flex-1 rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border"
                />
                <button
                    type="submit"
                    :disabled="!ticketCode.trim()"
                    class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground transition hover:bg-primary/90 disabled:opacity-50"
                >
                    Search
                </button>
            </div>
        </form>

        <div v-if="lookup" class="w-full max-w-md rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
            <div class="mb-4 text-center">
                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset" :class="statusColor(lookup.payment_status)">
                    {{ statusLabel(lookup.payment_status) }}
                </span>
                <p class="mt-2 text-sm text-muted-foreground">{{ checkInStatus() }}</p>
            </div>

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Name</dt>
                    <dd class="font-medium">{{ lookup.name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Email</dt>
                    <dd>{{ lookup.email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Event</dt>
                    <dd>{{ lookup.event.name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Package</dt>
                    <dd>{{ lookup.package.name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Quantity</dt>
                    <dd>{{ lookup.quantity }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-muted-foreground">Seat</dt>
                    <dd class="capitalize">{{ lookup.seat_position }}</dd>
                </div>
                <div v-if="lookup.checked_in_at" class="flex justify-between">
                    <dt class="text-muted-foreground">Checked In</dt>
                    <dd class="text-green-600 dark:text-green-400">{{ new Date(lookup.checked_in_at).toLocaleString() }}</dd>
                </div>
            </dl>

            <button
                v-if="canCheckIn()"
                type="button"
                :disabled="processing"
                class="mt-6 w-full rounded-md bg-green-600 px-4 py-2 text-sm text-white transition hover:bg-green-700 disabled:opacity-50"
                @click="doCheckIn"
            >
                {{ processing ? 'Checking In...' : 'Confirm Check-In' }}
            </button>
        </div>

        <div v-if="lookup === null && ticketCode && $page.props.flash?.error" class="w-full max-w-md rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ $page.props.flash.error }}
        </div>
    </div>
</template>
