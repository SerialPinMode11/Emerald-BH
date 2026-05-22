<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

const props = defineProps<{
    transaction: {
        id: number;
        amount: number;
        type: string;
        status: string;
        resolution: string | null;
    };
    agreement: {
        property: {
            title: string;
            city: string;
            land_owner?: { name: string; email: string };
            landOwner?: { name: string; email: string };
        };
        customer: { name: string; email: string };
    };
}>();

const form = useForm({
    resolution: '',
    status: 'completed' as 'completed' | 'failed',
});

function submit() {
    form.post(`/community/disputes/${props.transaction.id}/resolve`);
}
</script>

<template>
    <Head title="Dispute Resolution" />
    <EmeraldLayout>
        <Link href="/community/portal" class="mb-4 inline-flex items-center gap-1 text-sm emerald-text-primary">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Back to Portal
        </Link>

        <h2 class="mb-2 text-2xl font-semibold">Dispute Resolution</h2>
        <p class="mb-8 text-gray-600">Investigate and propose a fair resolution.</p>

        <div class="grid gap-6 lg:grid-cols-2">
            <GlassCard padding="p-6">
                <h3 class="mb-4 font-semibold">Transaction Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Property</dt>
                        <dd class="font-medium">{{ agreement.property.title }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Amount</dt>
                        <dd class="font-medium">${{ transaction.amount }} ({{ transaction.type }})</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Customer</dt>
                        <dd>{{ agreement.customer.name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-bold text-red-600">{{ transaction.status }}</dd>
                    </div>
                </dl>
            </GlassCard>

            <GlassCard padding="p-6">
                <form @submit.prevent="submit">
                    <label class="mb-2 block text-sm font-medium">Resolution Notes</label>
                    <textarea
                        v-model="form.resolution"
                        rows="5"
                        class="mb-4 w-full rounded-lg border px-3 py-2"
                        placeholder="Describe your proposed resolution..."
                        required
                    />
                    <label class="mb-2 block text-sm font-medium">Outcome</label>
                    <select v-model="form.status" class="mb-4 w-full rounded-lg border px-3 py-2">
                        <option value="completed">Resolved — Completed</option>
                        <option value="failed">Resolved — Failed / Refund</option>
                    </select>
                    <button
                        type="submit"
                        class="emerald-btn-primary w-full rounded-lg py-3 font-semibold"
                        :disabled="form.processing"
                    >
                        Submit Resolution
                    </button>
                </form>
            </GlassCard>
        </div>
    </EmeraldLayout>
</template>
