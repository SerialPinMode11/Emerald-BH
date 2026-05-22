<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import RentalProgress from '@/components/emerald/RentalProgress.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

type Agreement = {
    id: number;
    status: string;
    property: { title: string; city: string; image_url: string | null; price_per_month: number };
    start_date: string | null;
    end_date: string | null;
    signed_by_customer: boolean;
    signed_by_owner: boolean;
};

defineProps<{ agreements: Agreement[] }>();

const nav = [
    { label: 'Explore', href: '/customer/explore', icon: 'search' },
    { label: 'My Rentals', href: '/customer/rentals', icon: 'home_work', active: true },
];
</script>

<template>
    <Head title="My Rentals" />
    <EmeraldLayout :nav="nav">
        <h2 class="mb-2 text-2xl font-semibold">My Rentals</h2>
        <p class="mb-8 text-gray-600">Track your rental requests and active agreements.</p>

        <div v-if="!agreements.length" class="text-center">
            <GlassCard padding="p-12">
                <span class="material-symbols-outlined mb-4 text-5xl text-gray-300">home_work</span>
                <p class="text-gray-600">No rental agreements yet.</p>
                <Link
                    href="/customer/explore"
                    class="emerald-btn-primary mt-4 inline-block rounded-lg px-6 py-2 text-sm font-semibold"
                >
                    Explore Properties
                </Link>
            </GlassCard>
        </div>

        <div v-else class="space-y-6">
            <GlassCard v-for="a in agreements" :key="a.id" padding="p-6">
                <div class="flex flex-col gap-6 md:flex-row">
                    <img
                        :src="a.property.image_url ?? ''"
                        :alt="a.property.title"
                        class="h-40 w-full rounded-lg object-cover md:w-48"
                    />
                    <div class="flex-1">
                        <div class="mb-2 flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-semibold">{{ a.property.title }}</h3>
                                <p class="text-gray-500">
                                    {{ a.property.city }} • ${{ a.property.price_per_month }}/mo
                                </p>
                            </div>
                            <StatusChip :label="a.status.replace('_', ' ')" />
                        </div>
                        <RentalProgress :current="a.status" class="mb-4" />
                        <div class="flex flex-wrap gap-3">
                            <Link
                                v-if="
                                    !a.signed_by_customer &&
                                    ['community_review', 'active'].includes(a.status)
                                "
                                :href="`/customer/agreements/${a.id}/sign`"
                                class="emerald-btn-primary rounded-lg px-4 py-2 text-sm font-semibold"
                            >
                                Sign Agreement
                            </Link>
                            <p
                                v-else-if="a.status === 'requested'"
                                class="text-sm text-amber-700"
                            >
                                Waiting for Super Admin to assign a mediator.
                            </p>
                            <span v-else class="text-sm text-green-700">
                                <span class="material-symbols-outlined align-middle text-base"
                                    >check_circle</span
                                >
                                Signed by you
                            </span>
                        </div>
                    </div>
                </div>
            </GlassCard>
        </div>
    </EmeraldLayout>
</template>
