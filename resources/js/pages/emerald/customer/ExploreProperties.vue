<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

type Property = {
    id: number;
    title: string;
    city: string;
    price_per_month: number;
    image_url: string | null;
    status: string;
};

const props = defineProps<{
    properties: Property[];
    featured: Property[];
    cities: string[];
    filters: { search: string; city: string };
}>();

const search = ref(props.filters.search);
const city = ref(props.filters.city);

function applyFilters() {
    router.get('/customer/explore', { search: search.value, city: city.value }, { preserveState: true });
}

const nav = [
    { label: 'Explore', href: '/customer/explore', icon: 'search', active: true },
    { label: 'My Rentals', href: '/customer/rentals', icon: 'home_work' },
];
</script>

<template>
    <Head title="Explore Properties" />
    <EmeraldLayout :nav="nav">
        <section class="mb-8">
            <GlassCard padding="p-4 md:p-6">
                <div class="flex flex-col gap-4 md:flex-row">
                    <div class="relative flex-1">
                        <span
                            class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                            >search</span
                        >
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by property name, neighborhood..."
                            class="w-full rounded-lg border border-gray-200 bg-white py-3 pl-12 pr-4 outline-none focus:ring-2 focus:ring-[var(--emerald-primary)]"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <select
                        v-model="city"
                        class="min-w-[140px] rounded-lg border border-gray-200 bg-white px-4 py-3"
                        @change="applyFilters"
                    >
                        <option value="">All Cities</option>
                        <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <button
                        type="button"
                        class="emerald-btn-primary rounded-lg px-6 py-3 text-sm font-semibold transition hover:opacity-90"
                        @click="applyFilters"
                    >
                        Filter
                    </button>
                </div>
            </GlassCard>
        </section>

        <div v-if="featured.length" class="mb-8">
            <h2 class="mb-4 text-2xl font-semibold">Featured Collections</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
                <div
                    v-if="featured[0]"
                    class="group relative h-[400px] overflow-hidden rounded-xl shadow-lg md:col-span-8"
                >
                    <img
                        :src="featured[0].image_url ?? ''"
                        :alt="featured[0].title"
                        class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"
                    />
                    <div class="absolute bottom-0 left-0 w-full p-6">
                        <GlassCard>
                            <h3 class="text-2xl font-semibold">{{ featured[0].title }}</h3>
                            <p class="mb-4 text-gray-600">
                                {{ featured[0].city }} • ${{ featured[0].price_per_month }}/month
                            </p>
                            <Link
                                :href="`/customer/properties/${featured[0].id}`"
                                class="emerald-btn-primary rounded-lg px-4 py-2 text-sm font-semibold"
                            >
                                View & Request
                            </Link>
                        </GlassCard>
                    </div>
                </div>
                <div class="flex flex-col gap-6 md:col-span-4">
                    <GlassCard
                        v-for="p in featured.slice(1, 3)"
                        :key="p.id"
                        class="overflow-hidden"
                    >
                        <img
                            :src="p.image_url ?? ''"
                            :alt="p.title"
                            class="h-32 w-full object-cover"
                        />
                        <div class="p-4">
                            <h4 class="font-semibold">{{ p.title }}</h4>
                            <p class="mb-3 text-sm text-gray-500">
                                {{ p.city }} • ${{ p.price_per_month }}/mo
                            </p>
                            <Link
                                :href="`/customer/properties/${p.id}`"
                                class="emerald-btn-outline block w-full rounded-lg py-2 text-center text-sm font-semibold hover:bg-[var(--emerald-secondary)]"
                            >
                                View & AR Preview
                            </Link>
                        </div>
                    </GlassCard>
                </div>
            </div>
        </div>

        <section>
            <h2 class="mb-4 text-2xl font-semibold">Recently Added</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <GlassCard
                    v-for="p in properties"
                    :key="p.id"
                    class="overflow-hidden"
                >
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <img
                            :src="p.image_url ?? ''"
                            :alt="p.title"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        />
                        <StatusChip
                            class="absolute left-3 top-3"
                            label="Available"
                            variant="success"
                        />
                    </div>
                    <div class="p-4">
                        <h5 class="mb-1 truncate font-semibold">{{ p.title }}</h5>
                        <p class="mb-3 text-sm text-gray-500">
                            {{ p.city }} • ${{ p.price_per_month }}/mo
                        </p>
                        <Link
                            :href="`/customer/properties/${p.id}`"
                            class="block w-full rounded-lg py-2 text-center text-sm font-semibold transition emerald-text-primary"
                            style="background: color-mix(in srgb, var(--emerald-primary) 12%, white)"
                        >
                            View Details
                        </Link>
                    </div>
                </GlassCard>
            </div>
        </section>
    </EmeraldLayout>
</template>
