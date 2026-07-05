<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    registration: {
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
        created_at: string;
        event: {
            name: string;
            event_date: string;
            venue: string | null;
        };
        package: {
            name: string;
            price: number;
        };
    };
}>();

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Pending Verification',
        verified: 'Verified',
        rejected: 'Rejected',
    };
    return labels[status] ?? status;
}

function statusColor(status: string): string {
    const colors: Record<string, string> = {
        pending: 'text-yellow-600 bg-yellow-50 border-yellow-200 dark:text-yellow-400 dark:bg-yellow-900/20 dark:border-yellow-800',
        verified: 'text-green-600 bg-green-50 border-green-200 dark:text-green-400 dark:bg-green-900/20 dark:border-green-800',
        rejected: 'text-red-600 bg-red-50 border-red-200 dark:text-red-400 dark:bg-red-900/20 dark:border-red-800',
    };
    return colors[status] ?? 'text-gray-600 bg-gray-50 border-gray-200';
}
</script>

<template>
    <Head :title="`Ticket - ${registration.ticket_code.slice(0, 8)}`" />

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <div class="flex w-full max-w-lg items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:p-10 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                <div class="mb-6 text-center">
                    <div class="mb-2 text-3xl">🎫</div>
                    <h1 class="text-xl font-semibold">Registration Submitted!</h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Your ticket has been issued. Payment is being verified.
                    </p>
                </div>

                <div class="mb-6 space-y-3 rounded-md border border-[#e3e3e0] p-4 dark:border-[#3E3E3A]">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Status</span>
                        <span
                            class="rounded-sm border px-2 py-0.5 text-xs font-medium"
                            :class="statusColor(registration.payment_status)"
                        >
                            {{ statusLabel(registration.payment_status) }}
                        </span>
                    </div>

                    <div v-if="registration.rejection_reason" class="rounded-md bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                        <strong>Reason:</strong> {{ registration.rejection_reason }}
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Ticket Code</span>
                        <span class="font-mono text-sm font-medium">{{ registration.ticket_code }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Event</span>
                        <span class="text-sm font-medium">{{ registration.event.name }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Date</span>
                        <span class="text-sm">{{ new Date(registration.event.event_date).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) }}</span>
                    </div>

                    <div v-if="registration.event.venue" class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Venue</span>
                        <span class="text-sm">{{ registration.event.venue }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Package</span>
                        <span class="text-sm font-medium">{{ registration.package.name }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Seat</span>
                        <span class="text-sm capitalize">{{ registration.seat_position }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Amount Paid</span>
                        <span class="text-sm font-medium">৳{{ registration.amount }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Payment Method</span>
                        <span class="text-sm capitalize">{{ registration.payment_method }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Transaction ID</span>
                        <span class="font-mono text-sm">{{ registration.transaction_id }}</span>
                    </div>
                </div>

                <div class="rounded-md border border-[#e3e3e0] p-4 text-sm text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                    <p class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">What happens next?</p>
                    <ul class="mt-2 space-y-1">
                        <li>1. An admin will verify your payment manually.</li>
                        <li>2. You'll receive a confirmation email once verified.</li>
                        <li>3. Show this page (or your email) at the entrance for scanning.</li>
                    </ul>
                </div>

                <div class="mt-6 flex flex-col gap-3 text-center">
                    <Link
                        :href="home()"
                        class="text-sm text-[#f53003] underline underline-offset-4 hover:text-[#FF4433] dark:text-[#FF4433]"
                    >
                        Back to Home
                    </Link>
                </div>
            </main>
        </div>
    </div>
</template>
