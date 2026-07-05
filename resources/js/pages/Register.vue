<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    event: {
        id: number;
        name: string;
        description: string | null;
        event_date: string;
        venue: string | null;
        indoor_capacity: number | null;
        outdoor_capacity: number | null;
        packages: Array<{
            id: number;
            name: string;
            price: number;
            requires_student_verification: boolean;
            description: string | null;
        }>;
    };
    paymentMethods: Array<{
        id: number;
        name: string;
        slug: string;
        account_type: string | null;
        account_number: string | null;
        instructions: string | null;
    }>;
}>();

const step = ref(1);
const totalSteps = 4;

const form = useForm({
    package_id: 0,
    seat_position: '',
    name: '',
    email: '',
    phone: '',
    student_id_number: '',
    institution_name: '',
    payment_method: '',
    transaction_id: '',
    amount: '',
});

const selectedPackage = computed(() =>
    props.event.packages.find(p => p.id === form.package_id)
);

const requiresStudentId = computed(() =>
    selectedPackage.value?.requires_student_verification ?? false
);

function selectPackage(id: number) {
    form.package_id = id;
    form.clearErrors('package_id');
    step.value = 2;
}

function selectSeat(position: string) {
    form.seat_position = position;
    form.clearErrors('seat_position');
    step.value = 3;
}

function goToForm() {
    if (!form.payment_method && props.paymentMethods.length > 0) {
        form.payment_method = props.paymentMethods[0].slug;
    }
    step.value = 4;
}

function goBack() {
    if (step.value > 1) step.value--;
}

function submit() {
    form.post('/register', {
        onSuccess: () => {
            // Redirect handled by server
        },
    });
}
</script>

<template>
    <Head title="Register" />

    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <div class="flex w-full max-w-lg items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:p-10 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                <div class="mb-6">
                    <h1 class="text-xl font-semibold">{{ event.name }}</h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Step {{ step }} of {{ totalSteps }}
                    </p>
                    <div class="mt-2 flex gap-1">
                        <div
                            v-for="s in totalSteps"
                            :key="s"
                            class="h-1 flex-1 rounded-full"
                            :class="s <= step ? 'bg-[#1b1b18] dark:bg-[#EDEDEC]' : 'bg-[#e3e3e0] dark:bg-[#3E3E3A]'"
                        />
                    </div>
                </div>

                <!-- Step 1: Package Selection -->
                <div v-if="step === 1" class="space-y-3">
                    <h2 class="text-lg font-medium">Choose a Package</h2>
                    <div v-if="form.errors.package_id" class="text-sm text-red-600">{{ form.errors.package_id }}</div>
                    <button
                        v-for="pkg in event.packages"
                        :key="pkg.id"
                        type="button"
                        class="w-full rounded-md border border-[#e3e3e0] p-4 text-left transition hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        :class="{ 'border-[#1b1b18] dark:border-[#EDEDEC]': form.package_id === pkg.id }"
                        @click="selectPackage(pkg.id)"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">{{ pkg.name }}</h3>
                                <p v-if="pkg.description" class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ pkg.description }}
                                </p>
                            </div>
                            <span class="text-lg font-semibold">৳{{ pkg.price }}</span>
                        </div>
                    </button>
                </div>

                <!-- Step 2: Seat Position -->
                <div v-if="step === 2" class="space-y-3">
                    <h2 class="text-lg font-medium">Select Seat Position</h2>
                    <div v-if="form.errors.seat_position" class="text-sm text-red-600">{{ form.errors.seat_position }}</div>
                    <button
                        type="button"
                        class="w-full rounded-md border border-[#e3e3e0] p-4 text-left transition hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        :class="{ 'border-[#1b1b18] dark:border-[#EDEDEC]': form.seat_position === 'indoor' }"
                        @click="selectSeat('indoor')"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">Indoor</h3>
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Air-conditioned indoor seating</p>
                            </div>
                            <span v-if="event.indoor_capacity" class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                {{ event.indoor_capacity }} seats
                            </span>
                        </div>
                    </button>
                    <button
                        type="button"
                        class="w-full rounded-md border border-[#e3e3e0] p-4 text-left transition hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        :class="{ 'border-[#1b1b18] dark:border-[#EDEDEC]': form.seat_position === 'outdoor' }"
                        @click="selectSeat('outdoor')"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">Outdoor</h3>
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Open-air outdoor seating</p>
                            </div>
                            <span v-if="event.outdoor_capacity" class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                {{ event.outdoor_capacity }} seats
                            </span>
                        </div>
                    </button>
                    <button
                        type="button"
                        class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]"
                        @click="goBack"
                    >
                        Back to packages
                    </button>
                </div>

                <!-- Step 3: Payment Instructions -->
                <div v-if="step === 3" class="space-y-3">
                    <h2 class="text-lg font-medium">Payment Instructions</h2>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Please send the exact amount to one of the following accounts, then enter the details in the next step.
                    </p>
                    <div
                        v-for="method in paymentMethods"
                        :key="method.id"
                        class="rounded-md border border-[#e3e3e0] p-4 dark:border-[#3E3E3A]"
                    >
                        <h3 class="font-medium">{{ method.name }}</h3>
                        <p v-if="method.account_number" class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            {{ method.account_type === 'bank' ? 'Account' : 'Send Money to' }}:
                            <strong class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ method.account_number }}</strong>
                        </p>
                        <p v-if="method.instructions" class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            {{ method.instructions }}
                        </p>
                    </div>
                    <div class="pt-2">
                        <p class="mb-3 text-sm font-medium">
                            Amount to pay: <strong>৳{{ selectedPackage?.price ?? 0 }}</strong>
                        </p>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-sm border border-[#706f6c] px-4 py-1.5 text-sm transition hover:border-[#1b1b18] dark:hover:border-[#EDEDEC]"
                                @click="goBack"
                            >
                                Back
                            </button>
                            <button
                                type="button"
                                class="rounded-sm border border-black bg-[#1b1b18] px-4 py-1.5 text-sm text-white transition hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                                @click="goToForm"
                            >
                                I've Paid - Enter Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Details Form -->
                <div v-if="step === 4">
                    <form @submit.prevent="submit" class="space-y-4">
                        <h2 class="text-lg font-medium">Your Details</h2>

                        <div>
                            <label class="block text-sm font-medium">Full Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Phone (Bangladesh)</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="01XXXXXXXXX"
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                :class="{ 'border-red-500': form.errors.phone }"
                            />
                            <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
                        </div>

                        <template v-if="requiresStudentId">
                            <div>
                                <label class="block text-sm font-medium">Student ID Number</label>
                                <input
                                    v-model="form.student_id_number"
                                    type="text"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                    :class="{ 'border-red-500': form.errors.student_id_number }"
                                />
                                <div v-if="form.errors.student_id_number" class="mt-1 text-sm text-red-600">{{ form.errors.student_id_number }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Institution Name</label>
                                <input
                                    v-model="form.institution_name"
                                    type="text"
                                    class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                />
                            </div>
                        </template>

                        <div>
                            <label class="block text-sm font-medium">Payment Method</label>
                            <select
                                v-model="form.payment_method"
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                :class="{ 'border-red-500': form.errors.payment_method }"
                            >
                                <option value="" disabled>Select method</option>
                                <option v-for="m in paymentMethods" :key="m.id" :value="m.slug">
                                    {{ m.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600">{{ form.errors.payment_method }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Transaction ID</label>
                            <input
                                v-model="form.transaction_id"
                                type="text"
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                :class="{ 'border-red-500': form.errors.transaction_id }"
                            />
                            <div v-if="form.errors.transaction_id" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Amount (৳)</label>
                            <input
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                class="mt-1 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-2 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                                :class="{ 'border-red-500': form.errors.amount }"
                            />
                            <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</div>
                        </div>

                        <div v-if="form.errors.package_id" class="text-sm text-red-600">{{ form.errors.package_id }}</div>
                        <div v-if="form.errors.seat_position" class="text-sm text-red-600">{{ form.errors.seat_position }}</div>

                        <div class="flex gap-2 pt-2">
                            <button
                                type="button"
                                class="rounded-sm border border-[#706f6c] px-4 py-1.5 text-sm transition hover:border-[#1b1b18] dark:hover:border-[#EDEDEC]"
                                @click="step = 3"
                            >
                                Back
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-sm border border-black bg-[#1b1b18] px-6 py-1.5 text-sm text-white transition hover:bg-black disabled:opacity-50 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                            >
                                {{ form.processing ? 'Submitting...' : 'Submit Registration' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="form.hasErrors && step < 4" class="mt-4 text-sm text-red-600">
                    Please review and complete all previous steps.
                </div>
            </main>
        </div>
    </div>
</template>
