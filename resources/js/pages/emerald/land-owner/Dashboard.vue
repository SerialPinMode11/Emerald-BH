<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

defineProps<{
    stats: {
        total_properties: number;
        pending_approval: number;
        active_rentals: number;
        rental_requests: number;
    };
    recentRequests: Array<{
        id: number;
        status: string;
        customer: { name: string };
        property: { title: string };
    }>;
    properties: Array<{
        id: number;
        title: string;
        city: string;
        price_per_month: number;
        status: string;
        image_url: string | null;
    }>;
}>();

const nav = [
    { label: 'Dashboard', href: '/land-owner/dashboard', icon: 'dashboard', active: true },
    { label: 'Properties', href: '/land-owner/properties', icon: 'home_work' },
];
</script>

<template>
    <Head title="Land Owner Dashboard" />
    <EmeraldLayout :nav="nav">
        <h2 class="mb-2 text-3xl font-bold">Owner Dashboard</h2>
        <p class="mb-8 text-gray-600">Manage your properties and rental requests.</p>

        <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
            <GlassCard v-for="(val, key) in stats" :key="key" padding="p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">
                    {{ String(key).replace(/_/g, ' ') }}
                </p>
                <p class="text-3xl font-bold emerald-text-primary">{{ val }}</p>
            </GlassCard>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            <section>
                <h3 class="mb-4 text-xl font-semibold">Recent Rental Requests</h3>
                <GlassCard v-if="!recentRequests.length" padding="p-6">
                    <p class="text-gray-500">No pending requests.</p>
                </GlassCard>
                <div v-else class="space-y-3">
                    <GlassCard
                        v-for="r in recentRequests"
                        :key="r.id"
                        class="flex items-center justify-between"
                    >
                        <div>
                            <p class="font-semibold">{{ r.property.title }}</p>
                            <p class="text-sm text-gray-500">Tenant: {{ r.customer.name }}</p>
                        </div>
                        <StatusChip :label="r.status" />
                    </GlassCard>
                </div>
            </section>

            <section>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold">Your Properties</h3>
                    <Link href="/land-owner/properties" class="text-sm font-semibold emerald-text-primary"
                        >View all</Link
                    >
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <GlassCard v-for="p in properties" :key="p.id" class="overflow-hidden">
                        <img
                            :src="p.image_url ?? ''"
                            :alt="p.title"
                            class="h-28 w-full object-cover"
                        />
                        <div class="p-3">
                            <p class="truncate font-semibold">{{ p.title }}</p>
                            <p class="text-sm text-gray-500">${{ p.price_per_month }}/mo</p>
                            <StatusChip class="mt-2" :label="p.status" />
                        </div>
                    </GlassCard>
                </div>
            </section>
        </div>
    </EmeraldLayout>
</template>
