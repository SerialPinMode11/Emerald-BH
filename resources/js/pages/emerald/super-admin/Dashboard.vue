<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import GlassCard from '@/components/emerald/GlassCard.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

const props = defineProps<{
    stats: {
        pending_approvals: number;
        active_disputes: number;
        new_users: number;
        unassigned_agreements: number;
    };
    unassignedAgreements: Array<{
        id: number;
        property: { title: string };
        customer: { name: string; email?: string };
    }>;
    mediators: Array<{ id: number; name: string }>;
}>();

const nav = [
    { label: 'Overview', href: '/super-admin/dashboard', icon: 'dashboard', active: true },
    { label: 'Approvals', href: '/super-admin/approvals', icon: 'fact_check' },
];

const changeForm = ref({
    request_type: 'feature',
    description: '',
    priority: 'medium',
});

function assignMediator(agreementId: number, communityId: number) {
    if (!communityId) {
        return;
    }
    router.patch(
        `/super-admin/agreements/${agreementId}/assign`,
        { community_id: communityId },
        { preserveScroll: true },
    );
}

function submitChangeRequest() {
    router.post('/super-admin/change-requests', changeForm.value, { preserveScroll: true });
}
</script>

<template>
    <Head title="Super Admin Console" />
    <EmeraldLayout
        :nav="nav"
        show-sidebar
        sidebar-title="Admin Console"
        sidebar-subtitle="Super Admin Access"
    >
        <section class="mb-8">
            <h2 class="text-3xl font-bold">System Overview</h2>
            <p class="text-gray-600">
                Welcome back, Administrator. Here's the current state of the Emerald ecosystem.
            </p>
        </section>

        <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">Pending Approvals</p>
                <p class="text-4xl font-bold emerald-text-primary">{{ stats.pending_approvals }}</p>
            </GlassCard>
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">Active Disputes</p>
                <p class="text-4xl font-bold">{{ stats.active_disputes }}</p>
            </GlassCard>
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">New Users (24h)</p>
                <p class="text-4xl font-bold">{{ stats.new_users }}</p>
            </GlassCard>
            <GlassCard padding="p-6">
                <p class="text-sm uppercase text-gray-500">Unassigned Rentals</p>
                <p class="text-4xl font-bold">{{ stats.unassigned_agreements }}</p>
            </GlassCard>
        </div>

        <div class="grid gap-8 lg:grid-cols-12">
            <section class="lg:col-span-7">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold">Customer rental requests</h3>
                    <a
                        href="/super-admin/approvals"
                        class="text-sm font-semibold emerald-text-primary hover:underline"
                    >
                        Property approvals →
                    </a>
                </div>
                <GlassCard v-if="!unassignedAgreements.length" padding="p-6" class="text-gray-500">
                    No unassigned rental requests.
                </GlassCard>
                <GlassCard
                    v-for="a in unassignedAgreements"
                    :key="a.id"
                    class="mb-3"
                    padding="p-4"
                >
                    <p class="font-semibold">{{ a.property.title }}</p>
                    <p class="mb-2 text-sm text-gray-500">
                        Tenant: {{ a.customer.name }}
                        <span v-if="a.customer.email"> ({{ a.customer.email }})</span>
                    </p>
                    <label class="mb-1 block text-xs font-medium text-gray-500"
                        >Assign community mediator</label
                    >
                    <select
                        class="w-full rounded-lg border px-3 py-2 text-sm"
                        @change="
                            assignMediator(
                                a.id,
                                Number(($event.target as HTMLSelectElement).value),
                            )
                        "
                    >
                        <option value="">Select mediator...</option>
                        <option v-for="m in mediators" :key="m.id" :value="m.id">
                            {{ m.name }}
                        </option>
                    </select>
                </GlassCard>
            </section>

            <section class="lg:col-span-5">
                <GlassCard padding="p-4">
                    <h3 class="mb-3 font-semibold">New Change Request</h3>
                    <form class="space-y-2" @submit.prevent="submitChangeRequest">
                        <select
                            v-model="changeForm.request_type"
                            class="w-full rounded border px-2 py-1 text-sm"
                        >
                            <option value="feature">Feature</option>
                            <option value="bugfix">Bugfix</option>
                            <option value="config_change">Config</option>
                            <option value="ui_update">UI Update</option>
                        </select>
                        <textarea
                            v-model="changeForm.description"
                            rows="3"
                            class="w-full rounded border px-2 py-1 text-sm"
                            placeholder="Describe the change..."
                            required
                        />
                        <select
                            v-model="changeForm.priority"
                            class="w-full rounded border px-2 py-1 text-sm"
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                        <button
                            type="submit"
                            class="emerald-btn-primary w-full rounded py-2 text-sm font-semibold"
                        >
                            Submit to Dev Admin
                        </button>
                    </form>
                </GlassCard>
            </section>
        </div>
    </EmeraldLayout>
</template>
