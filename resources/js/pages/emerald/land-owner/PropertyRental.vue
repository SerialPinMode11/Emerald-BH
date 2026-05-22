<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

type PaymentRow = {
    id: number | null;
    period: string;
    billing_month: string;
    amount: number;
    status: string;
    status_label: string;
    paid_at: string | null;
    is_overdue: boolean;
    can_mark_paid: boolean;
};

const props = defineProps<{
    property: {
        id: number;
        title: string;
        city: string;
        price_per_month: number;
        image_url: string | null;
    };
    agreement: {
        id: number;
        start_date: string;
        start_date_iso: string;
        end_date: string;
        monthly_rent: number;
        deposit_paid: boolean;
        status: string;
    };
    tenant: { name: string; email: string };
    payment_schedule: PaymentRow[];
    payment_summary: {
        total_due: number;
        total_paid: number;
        months_paid: number;
        months_total: number;
        months_overdue: number;
    };
}>();

const nav = [
    { label: 'Dashboard', href: '/land-owner/dashboard', icon: 'dashboard' },
    { label: 'Properties', href: '/land-owner/properties', icon: 'home_work' },
];

function markPaid(row: PaymentRow) {
    if (!row.id || !row.can_mark_paid) {
        return;
    }

    router.post(
        `/land-owner/properties/${props.property.id}/payments/${row.id}/record`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success(`Marked ${row.period} as received.`),
            onError: () => toast.error('Could not record payment. Try again.'),
        },
    );
}

function rowStatusClass(row: PaymentRow): string {
    if (row.status === 'completed' || row.status === 'paid') {
        return 'bg-emerald-50 text-emerald-800';
    }
    if (row.is_overdue) {
        return 'bg-red-50 text-red-800';
    }
    return 'bg-amber-50 text-amber-800';
}
</script>

<template>
    <Head :title="`Rental — ${property.title}`" />
    <EmeraldLayout :nav="nav">
        <Link
            href="/land-owner/properties"
            class="mb-6 inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900"
        >
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Back to properties
        </Link>

        <div class="mb-6 flex flex-col gap-6 lg:flex-row">
            <img
                v-if="property.image_url"
                :src="property.image_url"
                :alt="property.title"
                class="h-48 w-full rounded-xl object-cover lg:h-auto lg:w-72"
            />
            <div class="flex-1">
                <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold">{{ property.title }}</h2>
                        <p class="text-gray-600">{{ property.city }}</p>
                    </div>
                    <StatusChip label="rented" />
                </div>
                <p class="text-sm text-gray-500">
                    Agreed rent:
                    <span class="font-semibold text-gray-900"
                        >${{ Number(agreement.monthly_rent).toFixed(2) }}/month</span
                    >
                    since {{ agreement.start_date }}
                </p>
            </div>
        </div>

        <div class="mb-8 grid gap-6 md:grid-cols-2">
            <GlassCard padding="p-6">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold">
                    <span class="material-symbols-outlined emerald-text-primary">person</span>
                    Tenant
                </h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-900">{{ tenant.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900">{{ tenant.email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Rent started</dt>
                        <dd class="font-medium text-gray-900">{{ agreement.start_date }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Lease ends</dt>
                        <dd class="font-medium text-gray-900">{{ agreement.end_date }}</dd>
                    </div>
                </dl>
            </GlassCard>

            <GlassCard padding="p-6">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold">
                    <span class="material-symbols-outlined emerald-text-primary">payments</span>
                    Payment summary
                </h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Collected</dt>
                        <dd class="text-xl font-semibold emerald-text-primary">
                            ${{ Number(payment_summary.total_paid).toFixed(2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total scheduled</dt>
                        <dd class="text-xl font-semibold text-gray-900">
                            ${{ Number(payment_summary.total_due).toFixed(2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Months paid</dt>
                        <dd class="font-medium">
                            {{ payment_summary.months_paid }} / {{ payment_summary.months_total }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Overdue</dt>
                        <dd
                            class="font-medium"
                            :class="payment_summary.months_overdue ? 'text-red-600' : 'text-gray-900'"
                        >
                            {{ payment_summary.months_overdue }}
                        </dd>
                    </div>
                </dl>
            </GlassCard>
        </div>

        <GlassCard padding="p-0" class="overflow-hidden">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold">Monthly payment tracker</h3>
                <p class="text-sm text-gray-500">
                    Each month from {{ agreement.start_date }} at the agreed
                    ${{ Number(agreement.monthly_rent).toFixed(2) }} rate. Mark received when the tenant pays.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Billing period</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Paid on</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="row in payment_schedule" :key="row.billing_month">
                            <td class="px-6 py-4 font-medium">{{ row.period }}</td>
                            <td class="px-6 py-4">${{ row.amount.toFixed(2) }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                                    :class="rowStatusClass(row)"
                                >
                                    {{ row.status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ row.paid_at ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    v-if="row.can_mark_paid"
                                    type="button"
                                    class="emerald-btn-primary rounded-lg px-3 py-1.5 text-xs font-semibold"
                                    @click="markPaid(row)"
                                >
                                    Mark received
                                </button>
                                <span v-else class="text-xs text-gray-400">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </GlassCard>
    </EmeraldLayout>
</template>
