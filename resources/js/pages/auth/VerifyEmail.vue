<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { logout } from '@/routes';

defineOptions({
    layout: {
        title: 'Email verification',
        description: 'Email verification is not required.',
    },
});
</script>

<template>
    <Head title="Email verification" />

    <div class="text-center text-sm text-muted-foreground">
        Email verification is not required for this application.
    </div>

    <div class="mt-6 text-center">
        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            Log out
        </TextLink>
    </div>
</template>
