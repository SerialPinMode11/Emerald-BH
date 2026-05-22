<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

const props = defineProps<{
    agreement: {
        id: number;
        status: string;
        community_notes: string | null;
        start_date: string | null;
        end_date: string | null;
        total_rent: number;
        signed_by_customer: boolean;
        signed_by_owner: boolean;
        can_print_contacts: boolean;
        customer: { name: string; email: string };
        land_owner: { name: string; email: string };
        property: {
            title: string;
            city: string;
            address: string;
            image_url: string | null;
        };
    };
}>();

const nav = [{ label: 'Portal', href: '/community/portal', icon: 'dashboard' }];

const activateForm = useForm({ community_notes: '' });
const showPrint = ref(false);

function activate() {
    const notes = activateForm.community_notes.trim();

    if (notes.length < 10) {
        toast.error('Mediator notes must be at least 10 characters.');
        return;
    }

    activateForm.post(`/community/agreements/${props.agreement.id}/activate`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Rental activated. Contact sheet is ready to print.');
        },
        onError: (errors: Record<string, string>) => {
            const message =
                errors.community_notes ||
                'Could not activate rental. Check your notes and try again.';
            toast.error(message);
        },
    });
}

function printContacts() {
    showPrint.value = true;
    setTimeout(() => window.print(), 100);
}
</script>

<template>
    <Head :title="`Agreement — ${agreement.property.title}`" />
    <EmeraldLayout :nav="nav">
        <Link
            href="/community/portal"
            class="mb-6 inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900"
        >
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Back to portal
        </Link>

        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold">{{ agreement.property.title }}</h2>
                <p class="text-gray-600">{{ agreement.property.address }}, {{ agreement.property.city }}</p>
            </div>
            <StatusChip :label="agreement.status.replace('_', ' ')" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <GlassCard padding="p-6">
                <h3 class="mb-4 font-semibold">Rental workflow</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li>
                        <span class="material-symbols-outlined mr-1 align-middle text-base text-green-600"
                            >check_circle</span
                        >
                        Customer requested rental
                    </li>
                    <li>
                        <span
                            class="material-symbols-outlined mr-1 align-middle text-base"
                            :class="agreement.status !== 'requested' ? 'text-green-600' : 'text-gray-300'"
                            >check_circle</span
                        >
                        Super Admin assigned mediator
                    </li>
                    <li>
                        <span
                            class="material-symbols-outlined mr-1 align-middle text-base"
                            :class="
                                agreement.status === 'active' ? 'text-green-600' : 'text-gray-300'
                            "
                            >check_circle</span
                        >
                        Mediator activated agreement
                    </li>
                    <li>
                        <span
                            class="material-symbols-outlined mr-1 align-middle text-base"
                            :class="agreement.signed_by_customer ? 'text-green-600' : 'text-gray-300'"
                            >check_circle</span
                        >
                        Customer signed
                    </li>
                </ul>
            </GlassCard>

            <GlassCard v-if="agreement.status === 'community_review'" padding="p-6">
                <h3 class="mb-3 font-semibold">Activate rental</h3>
                <p class="mb-4 text-sm text-gray-600">
                    Verify terms, then activate so the customer can sign. Contacts become printable
                    after the customer signs.
                </p>
                <textarea
                    v-model="activateForm.community_notes"
                    rows="4"
                    class="mb-1 w-full rounded-lg border px-3 py-2 text-sm"
                    :class="
                        activateForm.errors.community_notes
                            ? 'border-red-500 focus:ring-red-200'
                            : ''
                    "
                    placeholder="Mediator notes (min. 10 characters)..."
                    required
                />
                <p
                    v-if="activateForm.errors.community_notes"
                    class="mb-3 text-sm text-red-600"
                >
                    {{ activateForm.errors.community_notes }}
                </p>
                <p v-else class="mb-3 text-xs text-gray-500">
                    {{ activateForm.community_notes.trim().length }}/10 characters minimum
                </p>
                <button
                    type="button"
                    class="emerald-btn-primary w-full rounded-lg py-2.5 font-semibold"
                    :disabled="activateForm.processing"
                    @click="activate"
                >
                    Approve & activate rental
                </button>
            </GlassCard>

            <GlassCard
                v-if="agreement.can_print_contacts || agreement.status === 'active'"
                id="contact-sheet"
                class="lg:col-span-2"
                padding="p-6"
            >
                <div class="mb-4 flex items-center justify-between print:hidden">
                    <h3 class="font-semibold">Contact sheet</h3>
                    <button
                        type="button"
                        class="emerald-btn-primary inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold"
                        @click="printContacts"
                    >
                        <span class="material-symbols-outlined text-lg">print</span>
                        Print contacts
                    </button>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-xl border p-4">
                        <p class="mb-2 text-xs font-bold uppercase text-gray-500">Customer (Tenant)</p>
                        <p class="text-lg font-semibold">{{ agreement.customer.name }}</p>
                        <p class="text-sm text-gray-600">{{ agreement.customer.email }}</p>
                    </div>
                    <div class="rounded-xl border p-4">
                        <p class="mb-2 text-xs font-bold uppercase text-gray-500">Land Owner</p>
                        <p class="text-lg font-semibold">{{ agreement.land_owner.name }}</p>
                        <p class="text-sm text-gray-600">{{ agreement.land_owner.email }}</p>
                    </div>
                </div>
                <p v-if="!agreement.signed_by_customer" class="mt-4 text-sm text-amber-700 print:hidden">
                    Customer has not signed yet. Print is available after they complete signing.
                </p>
                <p v-else class="mt-4 text-sm text-green-700">
                    Rental active · {{ agreement.start_date }} — {{ agreement.end_date }} · ${{
                        agreement.total_rent
                    }}/mo
                </p>
            </GlassCard>
        </div>
    </EmeraldLayout>
</template>

<style>
@media print {
    header,
    nav,
    .print\\:hidden {
        display: none !important;
    }
}
</style>
