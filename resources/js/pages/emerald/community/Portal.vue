<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

defineProps<{
    stats: {
        assigned_agreements: number;
        active_disputes: number;
        in_review: number;
        active_rentals: number;
    };
    agreements: Array<{
        id: number;
        status: string;
        customer: { name: string; email: string };
        property: { title: string; city: string };
        community_notes: string | null;
        can_print_contacts: boolean;
        signed_by_customer: boolean;
    }>;
    disputes: Array<{
        id: number;
        amount: number;
        type: string;
        rental_agreement: { property: { title: string }; customer: { name: string } };
    }>;
}>();

const nav = [
    { label: 'Portal', href: '/community/portal', icon: 'dashboard', active: true },
];
</script>

<template>
    <Head title="Community Mediator Portal" />
    <EmeraldLayout :nav="nav">
        <section class="mb-8">
            <h2 class="text-2xl font-semibold">Community Mediator Portal</h2>
            <p class="text-gray-600">Resolving housing disputes with transparency and fairness.</p>
        </section>

        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">Assigned Agreements</p>
                <p class="text-4xl font-bold emerald-text-primary">{{ stats.assigned_agreements }}</p>
            </GlassCard>
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">Active Disputes</p>
                <p class="text-4xl font-bold" style="color: var(--emerald-accent)">
                    {{ stats.active_disputes }}
                </p>
            </GlassCard>
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">In Review</p>
                <p class="text-4xl font-bold">{{ stats.in_review }}</p>
            </GlassCard>
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">Active Rentals</p>
                <p class="text-4xl font-bold text-green-700">{{ stats.active_rentals }}</p>
            </GlassCard>
        </div>

        <div class="grid gap-8 lg:grid-cols-12">
            <section class="space-y-4 lg:col-span-7">
                <h3 class="text-xl font-semibold">Dispute Investigation Queue</h3>
                <GlassCard v-if="!disputes.length" padding="p-6">
                    <p class="text-gray-500">No active disputes.</p>
                </GlassCard>
                <GlassCard
                    v-for="d in disputes"
                    :key="d.id"
                    class="flex items-center justify-between"
                >
                    <div>
                        <p class="font-semibold">{{ d.rental_agreement.property.title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ d.type }} • ${{ d.amount }} •
                            {{ d.rental_agreement.customer.name }}
                        </p>
                    </div>
                    <Link
                        :href="`/community/disputes/${d.id}`"
                        class="emerald-btn-primary rounded-full px-4 py-2 text-sm font-semibold"
                    >
                        Investigate
                    </Link>
                </GlassCard>
            </section>

            <section class="space-y-4 lg:col-span-5">
                <h3 class="text-xl font-semibold">Assigned Agreements</h3>
                <GlassCard v-for="a in agreements" :key="a.id" padding="p-4">
                    <div class="mb-2 flex justify-between gap-2">
                        <p class="font-semibold">{{ a.property.title }}</p>
                        <StatusChip :label="a.status.replace('_', ' ')" />
                    </div>
                    <p class="text-sm text-gray-500">Tenant: {{ a.customer.name }}</p>
                    <p v-if="a.community_notes" class="mt-2 text-xs text-gray-600">
                        {{ a.community_notes }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Link
                            :href="`/community/agreements/${a.id}`"
                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold emerald-text-primary hover:bg-[var(--emerald-secondary)]"
                        >
                            Manage
                        </Link>
                        <Link
                            v-if="a.can_print_contacts"
                            :href="`/community/agreements/${a.id}`"
                            class="rounded-lg emerald-btn-primary px-3 py-1.5 text-xs font-semibold text-white"
                        >
                            Print contacts
                        </Link>
                    </div>
                </GlassCard>
            </section>
        </div>
    </EmeraldLayout>
</template>
