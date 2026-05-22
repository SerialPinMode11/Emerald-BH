<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import RentalProgress from '@/components/emerald/RentalProgress.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

const props = defineProps<{
    agreement: {
        id: number;
        status: string;
        property: { title: string; city: string; image_url: string | null; terms_of_rental: string };
        owner: { name: string };
        start_date: string | null;
        end_date: string | null;
        total_rent: number;
        signed_by_customer: boolean;
        signed_by_owner: boolean;
    };
}>();

const form = useForm({});

function sign() {
    form.post(`/customer/agreements/${props.agreement.id}/sign`);
}
</script>

<template>
    <Head title="Sign Rental Agreement" />
    <EmeraldLayout>
        <h2 class="mb-2 text-2xl font-semibold">Sign Rental Agreement</h2>
        <p class="mb-8 text-gray-600">Review terms and provide your digital signature.</p>

        <div class="grid gap-8 lg:grid-cols-2">
            <GlassCard padding="p-6">
                <img
                    :src="agreement.property.image_url ?? ''"
                    :alt="agreement.property.title"
                    class="mb-4 h-48 w-full rounded-lg object-cover"
                />
                <h3 class="text-xl font-semibold">{{ agreement.property.title }}</h3>
                <p class="text-gray-500">{{ agreement.property.city }}</p>
                <p class="mt-4 text-sm text-gray-600">
                    Land Owner: <strong>{{ agreement.owner.name }}</strong>
                </p>
                <p v-if="agreement.start_date" class="mt-2 text-sm">
                    Term: {{ agreement.start_date }} – {{ agreement.end_date }}
                </p>
                <p class="mt-4 text-lg font-bold emerald-text-primary">
                    ${{ agreement.total_rent }} / month
                </p>
            </GlassCard>

            <GlassCard padding="p-6">
                <RentalProgress :current="agreement.status" class="mb-6" />
                <h4 class="mb-2 font-semibold">Terms of Rental</h4>
                <p class="mb-6 text-sm text-gray-600">
                    {{ agreement.property.terms_of_rental ?? 'Standard lease terms apply.' }}
                </p>
                <div class="mb-6 space-y-2 text-sm">
                    <p>
                        Owner signed:
                        <strong>{{ agreement.signed_by_owner ? 'Yes' : 'Pending' }}</strong>
                    </p>
                    <p>
                        Your signature:
                        <strong>{{ agreement.signed_by_customer ? 'Yes' : 'Pending' }}</strong>
                    </p>
                </div>
                <button
                    v-if="!agreement.signed_by_customer"
                    type="button"
                    class="emerald-btn-primary w-full rounded-lg py-3 font-semibold disabled:opacity-50"
                    :disabled="form.processing"
                    @click="sign"
                >
                    Sign Digitally
                </button>
                <p v-else class="text-center font-semibold text-green-700">Agreement signed.</p>
            </GlassCard>
        </div>
    </EmeraldLayout>
</template>
