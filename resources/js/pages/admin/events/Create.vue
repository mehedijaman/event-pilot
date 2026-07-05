<script setup lang="ts">
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Events', href: admin.events.index() },
            { title: 'Create', href: admin.events.create() },
        ],
    },
});

const page = usePage();

const form = ref({
    name: '',
    description: '',
    event_date: '',
    venue: '',
    indoor_capacity: 0,
    outdoor_capacity: 0,
    registration_opens_at: '',
    registration_closes_at: '',
    is_active: true,
    cover_photo: null as File | null,
    packages: [] as Array<{
        _key: number;
        name: string;
        price: number;
        requires_student_verification: boolean;
        description: string;
    }>,
});

let pkgKey = 0;
const saving = ref(false);
const errors = ref((page.props as any).errors ?? {});

watch(() => (page.props as any).errors, (val) => { errors.value = val ?? {}; }, { deep: true });

function addPackage() {
    form.value.packages.push({
        _key: pkgKey++,
        name: '',
        price: 0,
        requires_student_verification: false,
        description: '',
    });
}

function removePackage(key: number) {
    form.value.packages = form.value.packages.filter(p => p._key !== key);
}

function onCoverChange(e: Event) {
    const target = e.target as HTMLInputElement;
    form.value.cover_photo = target.files?.[0] ?? null;
}

function submit() {
    saving.value = true;
    const data = new FormData();
    data.append('name', form.value.name);
    data.append('description', form.value.description);
    data.append('event_date', form.value.event_date);
    data.append('venue', form.value.venue);
    data.append('indoor_capacity', String(form.value.indoor_capacity));
    data.append('outdoor_capacity', String(form.value.outdoor_capacity));
    data.append('registration_opens_at', form.value.registration_opens_at);
    data.append('registration_closes_at', form.value.registration_closes_at);
    data.append('is_active', form.value.is_active ? '1' : '0');

    if (form.value.cover_photo) {
        data.append('cover_photo', form.value.cover_photo);
    }

    form.value.packages.forEach((pkg, i) => {
        data.append(`packages[${i}][name]`, pkg.name);
        data.append(`packages[${i}][price]`, String(pkg.price));
        data.append(`packages[${i}][requires_student_verification]`, pkg.requires_student_verification ? '1' : '0');
        data.append(`packages[${i}][description]`, pkg.description ?? '');
    });

    router.post(admin.events.store(), data, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head title="Create Event" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-semibold">Create Event</h1>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <h2 class="text-lg font-semibold">Event Details</h2>

                <div>
                    <label class="block text-sm font-medium" for="name">Name</label>
                    <input id="name" v-model="form.name" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" required />
                    <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="description">Description</label>
                    <textarea id="description" v-model="form.description" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" rows="3"></textarea>
                    <p v-if="errors.description" class="mt-1 text-xs text-red-600">{{ errors.description }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" for="event_date">Event Date</label>
                        <input id="event_date" v-model="form.event_date" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="datetime-local" required />
                        <p v-if="errors.event_date" class="mt-1 text-xs text-red-600">{{ errors.event_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="venue">Venue</label>
                        <input id="venue" v-model="form.venue" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" required />
                        <p v-if="errors.venue" class="mt-1 text-xs text-red-600">{{ errors.venue }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" for="indoor_capacity">Indoor Capacity</label>
                        <input id="indoor_capacity" v-model.number="form.indoor_capacity" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="number" min="0" required />
                        <p v-if="errors.indoor_capacity" class="mt-1 text-xs text-red-600">{{ errors.indoor_capacity }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="outdoor_capacity">Outdoor Capacity</label>
                        <input id="outdoor_capacity" v-model.number="form.outdoor_capacity" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="number" min="0" required />
                        <p v-if="errors.outdoor_capacity" class="mt-1 text-xs text-red-600">{{ errors.outdoor_capacity }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium" for="registration_opens_at">Registration Opens</label>
                        <input id="registration_opens_at" v-model="form.registration_opens_at" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="datetime-local" required />
                        <p v-if="errors.registration_opens_at" class="mt-1 text-xs text-red-600">{{ errors.registration_opens_at }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium" for="registration_closes_at">Registration Closes</label>
                        <input id="registration_closes_at" v-model="form.registration_closes_at" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-2 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="datetime-local" required />
                        <p v-if="errors.registration_closes_at" class="mt-1 text-xs text-red-600">{{ errors.registration_closes_at }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-sidebar-border/70" />
                    <label class="text-sm font-medium" for="is_active">Active</label>
                </div>
                <div>
                    <label class="block text-sm font-medium" for="cover_photo">Cover Photo</label>
                    <input id="cover_photo" class="mt-1 block w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:opacity-90" type="file" accept="image/jpeg,image/png,image/webp" @change="onCoverChange" />
                    <p v-if="errors.cover_photo" class="mt-1 text-xs text-red-600">{{ errors.cover_photo }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Packages</h2>
                    <button type="button" class="rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground transition hover:opacity-90" @click="addPackage">
                        Add Package
                    </button>
                </div>

                <div v-if="form.packages.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                    No packages yet. Click "Add Package" to create one.
                </div>

                <div v-for="(pkg, i) in form.packages" :key="pkg._key" class="rounded-lg border border-sidebar-border/70 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Package {{ i + 1 }}</span>
                        <button type="button" class="text-xs text-red-600 hover:underline" @click="removePackage(pkg._key)">Remove</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium" :for="'pkg_name_' + pkg._key">Name</label>
                            <input :id="'pkg_name_' + pkg._key" v-model="pkg.name" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-1.5 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="text" required />
                            <p v-if="errors['packages.' + i + '.name']" class="mt-1 text-xs text-red-600">{{ errors['packages.' + i + '.name'] }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium" :for="'pkg_price_' + pkg._key">Price</label>
                            <input :id="'pkg_price_' + pkg._key" v-model.number="pkg.price" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-1.5 text-sm outline-none focus:border-primary dark:border-sidebar-border" type="number" min="0" step="0.01" required />
                            <p v-if="errors['packages.' + i + '.price']" class="mt-1 text-xs text-red-600">{{ errors['packages.' + i + '.price'] }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium" :for="'pkg_desc_' + pkg._key">Description</label>
                        <textarea :id="'pkg_desc_' + pkg._key" v-model="pkg.description" class="mt-1 block w-full rounded-md border border-sidebar-border/70 bg-transparent px-3 py-1.5 text-sm outline-none focus:border-primary dark:border-sidebar-border" rows="2"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input :id="'pkg_student_' + pkg._key" v-model="pkg.requires_student_verification" type="checkbox" class="rounded border-sidebar-border/70" />
                        <label class="text-xs font-medium" :for="'pkg_student_' + pkg._key">Requires Student Verification</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pb-8">
                <Link :href="admin.events.index()" class="rounded-md border border-sidebar-border/70 px-4 py-2 text-sm transition hover:bg-muted">Cancel</Link>
                <button type="submit" class="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50" :disabled="saving">
                    {{ saving ? 'Creating...' : 'Create Event' }}
                </button>
            </div>
        </form>
    </div>
</template>
