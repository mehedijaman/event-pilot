<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import { login, register } from '@/routes';

defineProps<{
    event: {
        name: string;
        description: string | null;
        event_date: string;
        venue: string | null;
        packages: Array<{
            id: number;
            name: string;
            price: number;
            requires_student_verification: boolean;
            description: string | null;
        }>;
    } | null;
}>();
</script>

<template>
    <Head title="Home" />

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                </template>
            </nav>
        </header>

        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex w-full max-w-[335px] flex-col overflow-hidden rounded-lg lg:max-w-4xl">
                <div
                    class="rounded-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <template v-if="event">
                        <h1 class="mb-2 text-2xl font-semibold">{{ event.name }}</h1>
                        <p v-if="event.description" class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">
                            {{ event.description }}
                        </p>

                        <div class="mb-6 space-y-2">
                            <div v-if="event.venue" class="flex items-center gap-2">
                                <span class="font-medium">Venue:</span>
                                <span>{{ event.venue }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Date:</span>
                                <span>{{ new Date(event.event_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                            </div>
                        </div>

                        <div class="mb-6 space-y-3">
                            <h2 class="text-lg font-medium">Packages</h2>
                            <div
                                v-for="pkg in event.packages"
                                :key="pkg.id"
                                class="rounded-md border border-[#e3e3e0] p-4 dark:border-[#3E3E3A]"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium">{{ pkg.name }}</h3>
                                        <p v-if="pkg.description" class="text-[#706f6c] dark:text-[#A1A09A]">
                                            {{ pkg.description }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-semibold">৳{{ pkg.price }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Link
                            :href="register()"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Register Now
                        </Link>
                    </template>
                    <template v-else>
                        <h1 class="mb-2 text-2xl font-semibold">No active events</h1>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">
                            There are no active events at the moment. Please check back later.
                        </p>
                    </template>
                </div>
            </main>
        </div>
    </div>
</template>
