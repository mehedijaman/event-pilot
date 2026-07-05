<script setup lang="ts">
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Events', href: admin.events.index() },
            { title: 'Edit', href: '' },
        ],
    },
});

const page = usePage();
const eventData = (page.props as any).event as {
    id: number;
    name: string;
    description: string | null;
    event_date: string;
    venue: string;
    indoor_capacity: number;
    outdoor_capacity: number;
    registration_opens_at: string;
    registration_closes_at: string;
    is_active: boolean;
    cover_photo_url: string | null;
    packages: Array<{
        id: number;
        name: string;
        price: number;
        requires_student_verification: boolean;
        description: string | null;
        sort_order: number;
        is_active: boolean;
    }>;
};

function toDatetimeLocal(date: string | null): string {
    if (!date) return '';
    const d = new Date(date);
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const coverPreview = ref(eventData.cover_photo_url ?? null);
const coverFile = ref<File | null>(null);

const form = ref({
    name: eventData.name,
    description: eventData.description ?? '',
    event_date: toDatetimeLocal(eventData.event_date),
    venue: eventData.venue,
    indoor_capacity: eventData.indoor_capacity,
    outdoor_capacity: eventData.outdoor_capacity,
    registration_opens_at: toDatetimeLocal(eventData.registration_opens_at),
    registration_closes_at: toDatetimeLocal(eventData.registration_closes_at),
    is_active: eventData.is_active,
    packages: eventData.packages.map(p => ({
        _key: p.id,
        id: p.id,
        name: p.name,
        price: p.price,
        requires_student_verification: p.requires_student_verification,
        description: p.description ?? '',
        is_active: p.is_active,
    })),
});

let pkgKey = eventData.packages.length > 0
    ? Math.max(...eventData.packages.map(p => p.id)) + 1
    : 1;

const saving = ref(false);
const errors = ref((page.props as any).errors ?? {});

watch(() => (page.props as any).errors, (val) => { errors.value = val ?? {}; }, { deep: true });

function addPackage() {
    form.value.packages.push({
        _key: pkgKey++,
        id: 0,
        name: '',
        price: 0,
        requires_student_verification: false,
        description: '',
        is_active: true,
    });
}

function removePackage(key: number) {
    form.value.packages = form.value.packages.filter(p => p._key !== key);
}

function onCoverChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    coverFile.value = file;
    if (file) {
        coverPreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    saving.value = true;
    const data = new FormData();
    data.append('_method', 'PUT');
    data.append('name', form.value.name);
    data.append('description', form.value.description);
    data.append('event_date', form.value.event_date);
    data.append('venue', form.value.venue);
    data.append('indoor_capacity', String(form.value.indoor_capacity));
    data.append('outdoor_capacity', String(form.value.outdoor_capacity));
    data.append('registration_opens_at', form.value.registration_opens_at);
    data.append('registration_closes_at', form.value.registration_closes_at);
    data.append('is_active', form.value.is_active ? '1' : '0');

    if (coverFile.value) {
        data.append('cover_photo', coverFile.value);
    }

    form.value.packages.forEach((pkg, i) => {
        if (pkg.id) {
            data.append(`packages[${i}][id]`, String(pkg.id));
        }
        data.append(`packages[${i}][name]`, pkg.name);
        data.append(`packages[${i}][price]`, String(pkg.price));
        data.append(`packages[${i}][requires_student_verification]`, pkg.requires_student_verification ? '1' : '0');
        data.append(`packages[${i}][description]`, pkg.description ?? '');
        data.append(`packages[${i}][is_active]`, pkg.is_active ? '1' : '0');
    });

    router.post(admin.events.update(eventData.id), data, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head :title="'Edit: ' + eventData.name" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-semibold">Edit: {{ eventData.name }}</h1>

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
                    <div v-if="coverPreview" class="mb-2">
                        <img :src="coverPreview" alt="Cover preview" class="h-24 w-40 rounded object-cover" />
                    </div>
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
                    <div class="flex items-center gap-2">
                        <input :id="'pkg_active_' + pkg._key" v-model="pkg.is_active" type="checkbox" class="rounded border-sidebar-border/70" />
                        <label class="text-xs font-medium" :for="'pkg_active_' + pkg._key">Active</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pb-8">
                <Link :href="admin.events.index()" class="rounded-md border border-sidebar-border/70 px-4 py-2 text-sm transition hover:bg-muted">Cancel</Link>
                <button type="submit" class="rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-50" :disabled="saving">
                    {{ saving ? 'Saving...' : 'Update Event' }}
                </button>
            </div>
        </form>
    </div>
</template>
