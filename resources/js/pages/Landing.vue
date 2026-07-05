<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard } from '@/routes/admin';
import { login, register } from '@/routes';

const props = defineProps<{
    event: {
        name: string;
        description: string | null;
        event_date: string;
        venue: string | null;
        cover_photo_url: string | null;
        packages: Array<{
            id: number;
            name: string;
            price: number;
            requires_student_verification: boolean;
            description: string | null;
        }>;
    } | null;
    settings: {
        site_name: string | null;
        slogan: string | null;
        logo_url: string | null;
        favicon_url: string | null;
        contact_email: string | null;
        contact_phone: string | null;
        contact_address: string | null;
        social_facebook: string | null;
        social_twitter: string | null;
        social_instagram: string | null;
    } | null;
}>();

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

const formatTime = (date: string) =>
    new Date(date).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    });

const eventDate = computed(() => props.event ? new Date(props.event.event_date) : null);

const dayOfMonth = computed(() => eventDate.value?.getDate() ?? '');
const monthShort = computed(() =>
    eventDate.value?.toLocaleDateString('en-US', { month: 'short' }).toUpperCase() ?? ''
);

const hasContact = computed(() => props.settings && (
    props.settings.contact_email ||
    props.settings.contact_phone ||
    props.settings.contact_address
));

const hasSocial = computed(() => props.settings && (
    props.settings.social_facebook ||
    props.settings.social_twitter ||
    props.settings.social_instagram
));
</script>

<template>
    <Head :title="settings?.site_name ?? 'Home'">
        <link v-if="settings?.favicon_url" rel="icon" :href="settings.favicon_url" />
    </Head>

    <div class="min-h-screen bg-white dark:bg-[#0a0a0a]">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-neutral-100 bg-white/90 backdrop-blur-md dark:border-neutral-800 dark:bg-[#0a0a0a]/90">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5">
                    <img v-if="settings?.logo_url" :src="settings.logo_url" :alt="settings.site_name ?? 'Logo'" class="h-7 w-auto" />
                    <span class="text-base font-semibold tracking-tight text-neutral-900 dark:text-white">
                        {{ settings?.site_name ?? 'Event Ticket' }}
                    </span>
                </Link>
                <nav class="flex items-center gap-5">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="text-sm font-medium text-neutral-500 transition hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="text-sm font-medium text-neutral-500 transition hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                        >
                            Log in
                        </Link>
                    </template>
                    <Link
                        v-if="event"
                        :href="register()"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100"
                    >
                        Register
                    </Link>
                </nav>
            </div>
        </header>

        <template v-if="event">
            <!-- Hero with Cover Photo -->
            <section v-if="event.cover_photo_url" class="relative">
                <div class="aspect-[21/9] w-full overflow-hidden lg:aspect-[3/1]">
                    <img :src="event.cover_photo_url" :alt="event.name" class="h-full w-full object-cover" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />
                <div class="absolute inset-0 flex items-end">
                    <div class="mx-auto w-full max-w-6xl px-6 pb-12 lg:px-8 lg:pb-16">
                        <h1 class="max-w-3xl text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                            {{ event.name }}
                        </h1>
                        <p v-if="settings?.slogan" class="mt-3 max-w-xl text-base text-white/80 lg:text-lg">
                            {{ settings.slogan }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Hero without Cover Photo -->
            <section v-else class="px-6 pt-16 pb-12 lg:px-8 lg:pt-24 lg:pb-16">
                <div class="mx-auto max-w-6xl">
                    <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-neutral-900 dark:text-white sm:text-5xl lg:text-6xl">
                        {{ event.name }}
                    </h1>
                    <p v-if="settings?.slogan" class="mt-4 max-w-xl text-lg text-neutral-500 dark:text-neutral-400">
                        {{ settings.slogan }}
                    </p>
                </div>
            </section>

            <!-- Event Info Bar -->
            <section class="border-y border-neutral-100 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900/50">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-10 gap-y-4 px-6 py-5 lg:px-8">
                    <!-- Date -->
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-white shadow-sm dark:bg-neutral-800">
                            <span class="text-xs font-bold leading-none text-neutral-500 dark:text-neutral-400">{{ monthShort }}</span>
                            <span class="text-lg font-bold leading-tight text-neutral-900 dark:text-white">{{ dayOfMonth }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ formatDate(event.event_date) }}</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ formatTime(event.event_date) }}</p>
                        </div>
                    </div>

                    <div class="hidden h-8 w-px bg-neutral-200 dark:bg-neutral-700 sm:block" />

                    <!-- Venue -->
                    <div v-if="event.venue" class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-neutral-800">
                            <svg class="h-5 w-5 text-neutral-500 dark:text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">Venue</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ event.venue }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About -->
            <section v-if="event.description" class="px-6 py-16 lg:px-8 lg:py-20">
                <div class="mx-auto max-w-6xl">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">About this event</p>
                        <p class="mt-4 text-base leading-relaxed text-neutral-600 dark:text-neutral-300 lg:text-lg">
                            {{ event.description }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Packages -->
            <section class="px-6 pb-16 lg:px-8 lg:pb-24">
                <div class="mx-auto max-w-6xl">
                    <div class="mb-10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-neutral-400 dark:text-neutral-500">Packages</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-neutral-900 dark:text-white sm:text-3xl">
                            Choose your package
                        </h2>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="pkg in event.packages"
                            :key="pkg.id"
                            class="group relative flex flex-col rounded-2xl border border-neutral-200 bg-white p-6 transition-all hover:border-neutral-300 hover:shadow-lg dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-neutral-700"
                        >
                            <div class="mb-4 flex-1">
                                <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ pkg.name }}</h3>
                                <p v-if="pkg.description" class="mt-2 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                                    {{ pkg.description }}
                                </p>
                            </div>

                            <div class="mt-auto pt-4">
                                <div class="flex items-end justify-between">
                                    <div>
                                        <p class="text-xs text-neutral-400 dark:text-neutral-500">Starting from</p>
                                        <p class="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
                                            ৳{{ pkg.price }}
                                        </p>
                                    </div>
                                    <Link
                                        :href="register()"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-900 transition hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:hover:border-neutral-600 dark:hover:bg-neutral-700"
                                    >
                                        Register
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </Link>
                                </div>
                                <div v-if="pkg.requires_student_verification" class="mt-3">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                                        </svg>
                                        Student ID required
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="mt-12 flex flex-col items-center gap-4 rounded-2xl bg-neutral-50 p-8 text-center dark:bg-neutral-900 sm:flex-row sm:text-left lg:px-12">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-white">Ready to attend?</h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Secure your spot before registration closes.</p>
                        </div>
                        <Link
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-xl bg-neutral-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100"
                        >
                            Register Now
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </section>
        </template>

        <!-- No Event -->
        <template v-else>
            <section class="px-6 py-24 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    <div class="max-w-2xl">
                        <h1 class="text-4xl font-bold tracking-tight text-neutral-900 dark:text-white sm:text-5xl">
                            {{ settings?.site_name ?? 'Event Ticket' }}
                        </h1>
                        <p v-if="settings?.slogan" class="mt-4 text-lg text-neutral-500 dark:text-neutral-400">
                            {{ settings.slogan }}
                        </p>
                        <div class="mt-10 rounded-2xl border border-neutral-200 bg-neutral-50 p-8 dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-neutral-200 dark:bg-neutral-800">
                                    <svg class="h-6 w-6 text-neutral-500 dark:text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-neutral-900 dark:text-white">No active events</p>
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Check back soon for upcoming events.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <!-- Footer -->
        <footer class="border-t border-neutral-100 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900/50">
            <div class="mx-auto max-w-6xl px-6 py-12 lg:px-8">
                <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Brand -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2">
                            <img v-if="settings?.logo_url" :src="settings.logo_url" :alt="settings.site_name ?? 'Logo'" class="h-6 w-auto" />
                            <span class="font-semibold text-neutral-900 dark:text-white">
                                {{ settings?.site_name ?? 'Event Ticket' }}
                            </span>
                        </div>
                        <p v-if="settings?.slogan" class="mt-3 max-w-xs text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                            {{ settings.slogan }}
                        </p>
                        <!-- Social -->
                        <div v-if="hasSocial" class="mt-5 flex gap-2">
                            <a
                                v-if="settings?.social_facebook"
                                :href="settings.social_facebook"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-neutral-400 shadow-sm transition hover:text-neutral-900 dark:bg-neutral-800 dark:hover:text-white"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a
                                v-if="settings?.social_twitter"
                                :href="settings.social_twitter"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-neutral-400 shadow-sm transition hover:text-neutral-900 dark:bg-neutral-800 dark:hover:text-white"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            <a
                                v-if="settings?.social_instagram"
                                :href="settings.social_instagram"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-neutral-400 shadow-sm transition hover:text-neutral-900 dark:bg-neutral-800 dark:hover:text-white"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div v-if="hasContact">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Contact</h3>
                        <ul class="mt-4 space-y-3 text-sm text-neutral-500 dark:text-neutral-400">
                            <li v-if="settings?.contact_email" class="flex items-center gap-2.5">
                                <svg class="h-4 w-4 shrink-0 text-neutral-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                <a :href="`mailto:${settings.contact_email}`" class="transition hover:text-neutral-900 dark:hover:text-white">{{ settings.contact_email }}</a>
                            </li>
                            <li v-if="settings?.contact_phone" class="flex items-center gap-2.5">
                                <svg class="h-4 w-4 shrink-0 text-neutral-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                <a :href="`tel:${settings.contact_phone}`" class="transition hover:text-neutral-900 dark:hover:text-white">{{ settings.contact_phone }}</a>
                            </li>
                            <li v-if="settings?.contact_address" class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-neutral-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span>{{ settings.contact_address }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Quick Links</h3>
                        <ul class="mt-4 space-y-3 text-sm text-neutral-500 dark:text-neutral-400">
                            <li>
                                <Link href="/" class="transition hover:text-neutral-900 dark:hover:text-white">Home</Link>
                            </li>
                            <li v-if="event">
                                <Link :href="register()" class="transition hover:text-neutral-900 dark:hover:text-white">Register</Link>
                            </li>
                            <li>
                                <Link :href="login()" class="transition hover:text-neutral-900 dark:hover:text-white">Admin Login</Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 border-t border-neutral-200 pt-6 dark:border-neutral-800">
                    <p class="text-center text-xs text-neutral-400 dark:text-neutral-500">
                        &copy; {{ new Date().getFullYear() }} {{ settings?.site_name ?? 'Event Ticket' }}. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
