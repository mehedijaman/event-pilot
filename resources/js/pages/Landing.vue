<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

const hasContact = props.settings && (
    props.settings.contact_email ||
    props.settings.contact_phone ||
    props.settings.contact_address
);

const hasSocial = props.settings && (
    props.settings.social_facebook ||
    props.settings.social_twitter ||
    props.settings.social_instagram
);
</script>

<template>
    <Head :title="settings?.site_name ?? 'Home'">
        <link v-if="settings?.favicon_url" rel="icon" :href="settings.favicon_url" />
    </Head>

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-[#e3e3e0] bg-[#FDFDFC]/80 backdrop-blur-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/80">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <img v-if="settings?.logo_url" :src="settings.logo_url" :alt="settings.site_name ?? 'Logo'" class="h-8 w-auto" />
                    <span class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        {{ settings?.site_name ?? 'Event Ticket' }}
                    </span>
                </div>
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="text-sm text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="text-sm text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        >
                            Log in
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="px-6 py-16 lg:py-24">
            <div class="mx-auto max-w-5xl">
                <template v-if="event">
                    <div v-if="event.cover_photo_url" class="mb-10 overflow-hidden rounded-2xl">
                        <img :src="event.cover_photo_url" :alt="event.name" class="h-64 w-full object-cover sm:h-80 lg:h-96" />
                    </div>

                    <div class="max-w-3xl">
                        <h1 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] sm:text-5xl">
                            {{ event.name }}
                        </h1>
                        <p v-if="settings?.slogan" class="mt-3 text-lg text-[#706f6c] dark:text-[#A1A09A]">
                            {{ settings.slogan }}
                        </p>
                        <p v-if="event.description" class="mt-6 text-base leading-relaxed text-[#706f6c] dark:text-[#A1A09A]">
                            {{ event.description }}
                        </p>
                    </div>

                    <!-- Event Details -->
                    <div class="mt-10 flex flex-wrap gap-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span>{{ formatDate(event.event_date) }}</span>
                        </div>
                        <div v-if="event.venue" class="flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>{{ event.venue }}</span>
                        </div>
                    </div>

                    <!-- Packages -->
                    <div class="mt-14">
                        <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Packages</h2>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="pkg in event.packages"
                                :key="pkg.id"
                                class="rounded-xl border border-[#e3e3e0] p-6 transition hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                            >
                                <h3 class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ pkg.name }}</h3>
                                <p v-if="pkg.description" class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ pkg.description }}
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">৳{{ pkg.price }}</span>
                                    <span
                                        v-if="pkg.requires_student_verification"
                                        class="rounded-full bg-[#f5f5f4] px-3 py-1 text-xs font-medium text-[#706f6c] dark:bg-[#27272a] dark:text-[#A1A09A]"
                                    >
                                        Student ID Required
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="mt-10">
                        <Link
                            :href="register()"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#1b1b18] px-6 py-3 text-sm font-medium text-white transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            Register Now
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                    </div>
                </template>

                <!-- No Event -->
                <template v-else>
                    <h1 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">
                        {{ settings?.site_name ?? 'Event Ticket' }}
                    </h1>
                    <p v-if="settings?.slogan" class="mt-3 text-lg text-[#706f6c] dark:text-[#A1A09A]">
                        {{ settings.slogan }}
                    </p>
                    <div class="mt-10 rounded-xl border border-[#e3e3e0] p-8 dark:border-[#3E3E3A]">
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">
                            There are no active events at the moment. Please check back later.
                        </p>
                    </div>
                </template>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-[#e3e3e0] bg-[#fafaf9] dark:border-[#3E3E3A] dark:bg-[#111110]">
            <div class="mx-auto max-w-5xl px-6 py-12">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Brand -->
                    <div>
                        <div class="flex items-center gap-2">
                            <img v-if="settings?.logo_url" :src="settings.logo_url" :alt="settings.site_name ?? 'Logo'" class="h-6 w-auto" />
                            <span class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                {{ settings?.site_name ?? 'Event Ticket' }}
                            </span>
                        </div>
                        <p v-if="settings?.slogan" class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            {{ settings.slogan }}
                        </p>
                    </div>

                    <!-- Contact -->
                    <div v-if="hasContact">
                        <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Contact</h3>
                        <ul class="mt-3 space-y-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            <li v-if="settings?.contact_email" class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                <a :href="`mailto:${settings.contact_email}`" class="hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">{{ settings.contact_email }}</a>
                            </li>
                            <li v-if="settings?.contact_phone" class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                <a :href="`tel:${settings.contact_phone}`" class="hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">{{ settings.contact_phone }}</a>
                            </li>
                            <li v-if="settings?.contact_address" class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span>{{ settings.contact_address }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Social -->
                    <div v-if="hasSocial">
                        <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Follow Us</h3>
                        <div class="mt-3 flex gap-3">
                            <a
                                v-if="settings?.social_facebook"
                                :href="settings.social_facebook"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#e3e3e0] text-[#706f6c] transition hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
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
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#e3e3e0] text-[#706f6c] transition hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
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
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#e3e3e0] text-[#706f6c] transition hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-10 border-t border-[#e3e3e0] pt-6 dark:border-[#3E3E3A]">
                    <p class="text-center text-xs text-[#a1a09a] dark:text-[#706f6c]">
                        &copy; {{ new Date().getFullYear() }} {{ settings?.site_name ?? 'Event Ticket' }}. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
