<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

type Property = {
    id: number;
    title: string;
    description: string;
    address: string;
    city: string;
    price_per_month: number;
    deposit: number;
    terms_of_rental: string | null;
    status: string;
    created_at: string;
    rejection_reason?: string | null;
    approver_name?: string | null;
    approved_at?: string | null;
    room_length_m: number | null;
    room_width_m: number | null;
    room_height_m: number | null;
    land_owner: { name: string; email: string };
    media: Array<{ url: string }>;
};

defineProps<{
    pendingProperties: Property[];
    reviewedProperties: Property[];
}>();

const nav = [
    { label: 'Overview', href: '/super-admin/dashboard', icon: 'dashboard' },
    { label: 'Approvals', href: '/super-admin/approvals', icon: 'fact_check', active: true },
];

const rejectTarget = ref<Property | null>(null);
const rejectForm = useForm({ rejection_reason: '' });

function approveProperty(id: number) {
    router.put(`/super-admin/properties/${id}/approve`, {}, { preserveScroll: true });
}

function openReject(p: Property) {
    rejectTarget.value = p;
    rejectForm.reset();
}

function submitReject() {
    if (!rejectTarget.value) {
        return;
    }
    rejectForm.put(`/super-admin/properties/${rejectTarget.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            rejectTarget.value = null;
        },
    });
}
</script>

<template>
    <Head title="Property Approvals" />
    <EmeraldLayout :nav="nav" show-sidebar sidebar-title="Admin Console" sidebar-subtitle="Super Admin">
        <section class="mb-8">
            <h2 class="text-3xl font-bold">Property Approvals</h2>
            <p class="text-gray-600">
                Review land owner submissions. Approved listings appear on the customer Explore
                page.
            </p>
        </section>

        <h3 class="mb-4 text-xl font-semibold">Pending review</h3>
        <div v-if="!pendingProperties.length" class="mb-10">
            <GlassCard padding="p-8" class="text-center text-gray-500">
                No pending property requests.
            </GlassCard>
        </div>
        <div v-else class="mb-10 space-y-4">
            <GlassCard
                v-for="p in pendingProperties"
                :key="p.id"
                class="flex flex-col gap-6 lg:flex-row"
                padding="p-5"
            >
                <img
                    :src="p.media[0]?.url ?? ''"
                    :alt="p.title"
                    class="h-40 w-full rounded-lg object-cover lg:w-48"
                />
                <div class="flex-1">
                    <h4 class="text-lg font-semibold">{{ p.title }}</h4>
                    <p class="text-sm text-gray-500">
                        Owner: <strong>{{ p.land_owner.name }}</strong> ({{ p.land_owner.email }})
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ p.address }}, {{ p.city }} · Submitted {{ p.created_at }}
                    </p>
                    <p class="mt-2 text-sm">{{ p.description }}</p>
                    <p class="mt-2 font-bold emerald-text-primary">
                        ${{ p.price_per_month }} / month · Deposit ${{ p.deposit }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        AR dimensions: {{ p.room_length_m }}m × {{ p.room_width_m }}m ×
                        {{ p.room_height_m }}m
                    </p>
                </div>
                <div class="flex flex-col justify-center gap-2 sm:flex-row lg:flex-col">
                    <button
                        type="button"
                        class="rounded-full border border-red-500 px-6 py-2 font-bold text-red-500 hover:bg-red-50"
                        @click="openReject(p)"
                    >
                        Reject
                    </button>
                    <button
                        type="button"
                        class="emerald-btn-primary rounded-full px-6 py-2 font-bold"
                        @click="approveProperty(p.id)"
                    >
                        Approve
                    </button>
                </div>
            </GlassCard>
        </div>

        <h3 class="mb-4 text-xl font-semibold">Recently reviewed</h3>
        <div class="space-y-3">
            <GlassCard
                v-for="p in reviewedProperties"
                :key="p.id"
                class="flex items-center justify-between gap-4"
                padding="p-4"
            >
                <div>
                    <p class="font-semibold">{{ p.title }}</p>
                    <p class="text-sm text-gray-500">{{ p.land_owner.name }} · {{ p.approved_at }}</p>
                    <p v-if="p.rejection_reason" class="mt-1 text-sm text-red-700">
                        Reason: {{ p.rejection_reason }}
                    </p>
                </div>
                <StatusChip :label="p.status" />
            </GlassCard>
        </div>

        <div
            v-if="rejectTarget"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="rejectTarget = null"
        >
            <GlassCard class="w-full max-w-md" padding="p-6">
                <h3 class="mb-2 text-lg font-bold">Reject listing</h3>
                <p class="mb-4 text-sm text-gray-600">
                    Explain why <strong>{{ rejectTarget.title }}</strong> cannot be approved. The
                    land owner will see this message.
                </p>
                <textarea
                    v-model="rejectForm.rejection_reason"
                    rows="4"
                    class="mb-4 w-full rounded-lg border px-3 py-2 text-sm"
                    placeholder="Minimum 10 characters..."
                    required
                />
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100"
                        @click="rejectTarget = null"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                        :disabled="rejectForm.processing"
                        @click="submitReject"
                    >
                        Confirm rejection
                    </button>
                </div>
            </GlassCard>
        </div>
    </EmeraldLayout>
</template>
