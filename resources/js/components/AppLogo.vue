<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const settings = (page.props as any).settings as {
    site_name: string | null;
    logo_url: string | null;
} | null;
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="settings?.logo_url"
            :src="settings.logo_url"
            :alt="settings.site_name ?? 'Logo'"
            class="size-5 object-contain"
        />
        <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">
            {{ settings?.site_name ?? 'Admin Panel' }}
        </span>
    </div>
</template>
