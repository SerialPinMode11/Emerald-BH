<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import PropertyArModelViewer from '@/components/emerald/PropertyArModelViewer.vue';
import PropertyArViewer from '@/components/emerald/PropertyArViewer.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

const props = defineProps<{
    property: {
        id: number;
        title: string;
        description: string;
        address: string;
        city: string;
        price_per_month: number;
        deposit: number;
        terms_of_rental: string | null;
        image_url: string | null;
        owner_name: string | null;
        room_length_m: number | null;
        room_width_m: number | null;
        room_height_m: number | null;
        ar_model_url: string | null;
        ar_model_ios_url: string | null;
    };
    hasActiveRequest: boolean;
}>();

const nav = [
    { label: 'Explore', href: '/customer/explore', icon: 'search', active: true },
    { label: 'My Rentals', href: '/customer/rentals', icon: 'home_work' },
];

function requestRent() {
    router.post(`/customer/properties/${props.property.id}/request`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="property.title" />
    <EmeraldLayout :nav="nav">
        <Link
            href="/customer/explore"
            class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900"
        >
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Back to Explore
        </Link>

        <div class="grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <img
                    :src="property.image_url ?? ''"
                    :alt="property.title"
                    class="mb-6 h-80 w-full rounded-xl object-cover shadow-lg"
                />
                <h1 class="mb-2 text-3xl font-bold">{{ property.title }}</h1>
                <p class="mb-4 text-gray-600">
                    {{ property.address }}, {{ property.city }}
                    <span v-if="property.owner_name"> · {{ property.owner_name }}</span>
                </p>
                <StatusChip label="Available" variant="success" class="mb-4" />
                <p class="leading-relaxed text-gray-700">{{ property.description }}</p>
                <GlassCard v-if="property.terms_of_rental" class="mt-6" padding="p-4">
                    <h3 class="mb-2 font-semibold">Terms of rental</h3>
                    <p class="text-sm text-gray-600">{{ property.terms_of_rental }}</p>
                </GlassCard>
            </div>

            <div class="space-y-6 lg:col-span-5">
                <GlassCard padding="p-6">
                    <p class="text-3xl font-bold emerald-text-primary">
                        ${{ property.price_per_month }}
                        <span class="text-base font-medium text-gray-500">/ month</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500">Deposit: ${{ property.deposit }}</p>
                    <button
                        v-if="!hasActiveRequest"
                        type="button"
                        class="emerald-btn-primary mt-6 w-full rounded-lg py-3 font-semibold"
                        @click="requestRent"
                    >
                        Request to Rent
                    </button>
                    <p v-else class="mt-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800">
                        You already have an active request for this property.
                        <Link href="/customer/rentals" class="font-semibold underline"
                            >View My Rentals</Link
                        >
                    </p>
                </GlassCard>

                <PropertyArModelViewer
                    v-if="property.ar_model_url"
                    :src="property.ar_model_url"
                    :ios-src="property.ar_model_ios_url"
                    :poster="property.image_url"
                    :title="property.title"
                />
                <PropertyArViewer
                    v-else
                    :title="property.title"
                    :length-m="property.room_length_m"
                    :width-m="property.room_width_m"
                    :height-m="property.room_height_m"
                />
                <p
                    v-if="property.ar_model_url"
                    class="text-center text-xs text-gray-500"
                >
                    Dimensions: {{ property.room_length_m }}m × {{ property.room_width_m }}m ×
                    {{ property.room_height_m }}m
                </p>
            </div>
        </div>
    </EmeraldLayout>
</template>
