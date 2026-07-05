<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: admin.events.index(),
            },
        ],
    },
});

const props = defineProps<{
    events: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            event_date: string;
            venue: string;
            is_active: boolean;
            indoor_capacity: number;
            outdoor_capacity: number;
            registration_opens_at: string;
            registration_closes_at: string;
            packages_count: number;
            registrations_count: number;
            created_at: string;
            cover_photo_url: string | null;
        }>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

function destroy(event: { id: number; name: string }) {
    if (!confirm(`Delete "${event.name}"? This cannot be undone.`)) return;
    router.delete(admin.events.destroy(event.id), {
        preserveScroll: true,
    });
}

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Events" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Events</h1>
            <Link :href="admin.events.create()" class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90">
                Create Event
            </Link>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <table class="min-w-full divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                <thead>
                    <tr class="bg-muted/50">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Cover</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Venue</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Packages</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Registrations</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="e in events.data" :key="e.id" class="hover:bg-muted/30 transition">
                        <td class="whitespace-nowrap px-4 py-3">
                            <img v-if="e.cover_photo_url" :src="e.cover_photo_url" alt="Cover" class="h-10 w-16 rounded object-cover" />
                            <div v-else class="h-10 w-16 rounded bg-muted flex items-center justify-center text-xs text-muted-foreground">—</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <div class="text-sm font-medium">{{ e.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ e.slug }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">{{ formatDate(e.event_date) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted-foreground">{{ e.venue }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">{{ e.packages_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm">{{ e.registrations_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                                  :class="e.is_active ? 'text-green-700 bg-green-50 ring-green-600/20 dark:text-green-400 dark:bg-green-400/10 dark:ring-green-400/20'
                                                      : 'text-gray-700 bg-gray-50 ring-gray-600/20 dark:text-gray-400 dark:bg-gray-400/10 dark:ring-gray-400/20'">
                                {{ e.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <div class="flex gap-1">
                                <Link :href="admin.events.edit(e.id)" class="rounded bg-primary px-2 py-1 text-xs text-primary-foreground transition hover:opacity-80">
                                    Edit
                                </Link>
                                <button type="button" class="rounded bg-red-600 px-2 py-1 text-xs text-white transition hover:bg-red-700 disabled:opacity-50"
                                        :disabled="e.registrations_count > 0"
                                        :title="e.registrations_count > 0 ? 'Cannot delete: has registrations' : ''"
                                        @click="destroy(e)">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="events.data.length === 0">
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                            No events found.
                            <Link :href="admin.events.create()" class="text-primary hover:underline">Create one</Link>.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="events.last_page > 1" class="flex items-center justify-between">
            <p class="text-sm text-muted-foreground">Showing {{ events.from }}–{{ events.to }} of {{ events.total }}</p>
            <div class="flex gap-1">
                <Link v-for="link in events.links" :key="link.label" :href="link.url ?? '#'"
                      class="rounded-md px-3 py-1.5 text-sm transition"
                      :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                      v-html="link.label"
                      preserve-state preserve-scroll />
            </div>
        </div>
    </div>
</template>
