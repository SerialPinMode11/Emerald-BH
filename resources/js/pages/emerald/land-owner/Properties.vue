<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

type PropertyRow = {
    id: number;
    title: string;
    city: string;
    price_per_month: number;
    status: string;
    image_url: string | null;
    requests_count: number;
    rejection_reason: string | null;
    approved_at: string | null;
    created_at: string;
    approver_name: string | null;
    room_length_m: number | null;
    room_width_m: number | null;
    room_height_m: number | null;
    has_ar_model?: boolean;
};

const props = defineProps<{ properties: PropertyRow[] }>();

const page = usePage();
const activeTab = ref(
    (page.url.includes('tab=requests') ? 'requests' : 'add') as 'add' | 'requests',
);

const form = useForm({
    title: '',
    description: '',
    address: '',
    city: '',
    price_per_month: '',
    deposit: '',
    terms_of_rental: '',
    image_url: '',
    room_length_m: '5',
    room_width_m: '4',
    room_height_m: '2.5',
    ar_model: null as File | null,
    ar_model_ios: null as File | null,
});

const arModelLabel = ref('');
const arIosLabel = ref('');

function onArModelFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.ar_model = file;
    arModelLabel.value = file?.name ?? '';
}

function onArIosFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.ar_model_ios = file;
    arIosLabel.value = file?.name ?? '';
}

function submit() {
    form.post('/land-owner/properties', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.room_length_m = '5';
            form.room_width_m = '4';
            form.room_height_m = '2.5';
            form.ar_model = null;
            form.ar_model_ios = null;
            arModelLabel.value = '';
            arIosLabel.value = '';
            activeTab.value = 'requests';
        },
    });
}

const nav = [
    { label: 'Dashboard', href: '/land-owner/dashboard', icon: 'dashboard' },
    { label: 'Properties', href: '/land-owner/properties', icon: 'home_work', active: true },
];

const statusSteps = ['pending', 'approved', 'rented'] as const;

function stepIndex(status: string): number {
    const map: Record<string, number> = {
        pending: 0,
        rejected: 0,
        approved: 1,
        rented: 2,
        inactive: 0,
    };
    return map[status] ?? 0;
}

const pendingCount = computed(() => props.properties.filter((p) => p.status === 'pending').length);
</script>

<template>
    <Head title="My Properties" />
    <EmeraldLayout :nav="nav">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold">My Properties</h2>
            <p class="text-gray-600">Submit listings for Super Admin approval and track progress.</p>
        </div>

        <div class="mb-6 flex gap-2 rounded-full bg-white/60 p-1 backdrop-blur">
            <button
                type="button"
                class="flex-1 rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="
                    activeTab === 'add'
                        ? 'emerald-btn-primary text-white shadow'
                        : 'text-gray-600 hover:bg-white/80'
                "
                @click="activeTab = 'add'"
            >
                Add Property
            </button>
            <button
                type="button"
                class="flex-1 rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="
                    activeTab === 'requests'
                        ? 'emerald-btn-primary text-white shadow'
                        : 'text-gray-600 hover:bg-white/80'
                "
                @click="activeTab = 'requests'"
            >
                My Requests
                <span
                    v-if="pendingCount"
                    class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-white/30 px-1 text-xs"
                    >{{ pendingCount }}</span
                >
            </button>
        </div>

        <GlassCard v-if="activeTab === 'add'" class="mb-8" padding="p-6">
            <h3 class="mb-4 font-semibold">New listing</h3>
            <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Title</label>
                    <input v-model="form.title" class="w-full rounded-lg border px-3 py-2" required />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">City</label>
                    <input v-model="form.city" class="w-full rounded-lg border px-3 py-2" required />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Address</label>
                    <input v-model="form.address" class="w-full rounded-lg border px-3 py-2" required />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Description</label>
                    <textarea
                        v-model="form.description"
                        class="w-full rounded-lg border px-3 py-2"
                        rows="3"
                        required
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600"
                        >Price / month ($)</label
                    >
                    <input
                        v-model="form.price_per_month"
                        type="number"
                        step="0.01"
                        class="w-full rounded-lg border px-3 py-2"
                        required
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">Deposit ($)</label>
                    <input
                        v-model="form.deposit"
                        type="number"
                        step="0.01"
                        class="w-full rounded-lg border px-3 py-2"
                    />
                </div>
                <div class="md:col-span-2 rounded-xl border border-dashed border-[var(--emerald-primary)]/30 bg-[var(--emerald-secondary)]/40 p-4">
                    <div class="mb-3 flex items-start gap-2">
                        <span class="material-symbols-outlined emerald-text-primary">view_in_ar</span>
                        <div>
                            <p class="text-sm font-semibold">Augmented reality room upload</p>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600">
                                Upload a 3D scan of the rental space. Customers view it with
                                <strong>Google &lt;model-viewer&gt;</strong> (WebXR / Scene Viewer on
                                Android, AR Quick Look on iOS with USDZ). Scan apps such as
                                Polycam, RoomScan, or Scaniverse export <strong>.glb</strong>
                                files.
                            </p>
                        </div>
                    </div>
                    <p class="mb-2 text-xs font-medium text-gray-600">
                        Room dimensions (meters) — fallback box preview if no 3D file
                    </p>
                    <div class="mb-4 grid grid-cols-3 gap-3">
                        <input
                            v-model="form.room_length_m"
                            type="number"
                            step="0.1"
                            min="1"
                            placeholder="Length"
                            class="rounded-lg border px-3 py-2"
                        />
                        <input
                            v-model="form.room_width_m"
                            type="number"
                            step="0.1"
                            min="1"
                            placeholder="Width"
                            class="rounded-lg border px-3 py-2"
                        />
                        <input
                            v-model="form.room_height_m"
                            type="number"
                            step="0.1"
                            min="1"
                            placeholder="Height"
                            class="rounded-lg border px-3 py-2"
                        />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                3D room model (GLB or glTF)
                            </label>
                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 py-6 transition hover:border-[var(--emerald-primary)]"
                            >
                                <span class="material-symbols-outlined mb-1 text-3xl text-gray-400"
                                    >upload_file</span
                                >
                                <span class="text-center text-xs text-gray-600">
                                    {{ arModelLabel || 'Choose .glb or .gltf (max 50 MB)' }}
                                </span>
                                <input
                                    type="file"
                                    accept=".glb,.gltf,model/gltf-binary,model/gltf+json"
                                    class="hidden"
                                    @change="onArModelFile"
                                />
                            </label>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">
                                iOS AR model (USDZ, optional)
                            </label>
                            <label
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 py-6 transition hover:border-[var(--emerald-primary)]"
                            >
                                <span class="material-symbols-outlined mb-1 text-3xl text-gray-400"
                                    >phone_iphone</span
                                >
                                <span class="text-center text-xs text-gray-600">
                                    {{ arIosLabel || 'Choose .usdz for iPhone AR Quick Look' }}
                                </span>
                                <input
                                    type="file"
                                    accept=".usdz,model/vnd.usdz+zip"
                                    class="hidden"
                                    @change="onArIosFile"
                                />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Image URL</label>
                    <input v-model="form.image_url" class="w-full rounded-lg border px-3 py-2" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-medium text-gray-600"
                        >Terms of rental</label
                    >
                    <textarea
                        v-model="form.terms_of_rental"
                        class="w-full rounded-lg border px-3 py-2"
                        rows="2"
                    />
                </div>
                <button
                    type="submit"
                    class="emerald-btn-primary rounded-lg px-6 py-2.5 font-semibold md:col-span-2"
                    :disabled="form.processing"
                >
                    Submit for Approval
                </button>
            </form>
        </GlassCard>

        <div v-if="activeTab === 'requests'" class="space-y-4">
            <GlassCard v-if="!properties.length" padding="p-8" class="text-center text-gray-500">
                No property requests yet. Use the Add Property tab to submit your first listing.
            </GlassCard>
            <GlassCard v-for="p in properties" :key="p.id" padding="p-5">
                <div class="flex flex-col gap-4 md:flex-row">
                    <img
                        :src="p.image_url ?? ''"
                        :alt="p.title"
                        class="h-28 w-full rounded-lg object-cover md:w-36"
                    />
                    <div class="flex-1">
                        <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold">{{ p.title }}</h3>
                                <p class="text-sm text-gray-500">
                                    {{ p.city }} · Submitted {{ p.created_at }}
                                </p>
                            </div>
                            <StatusChip :label="p.status" />
                        </div>
                        <div class="mb-3 flex items-center gap-2">
                            <template v-for="(step, i) in statusSteps" :key="step">
                                <div
                                    class="h-2 flex-1 rounded-full"
                                    :class="
                                        i <= stepIndex(p.status)
                                            ? 'bg-[var(--emerald-primary)]'
                                            : 'bg-gray-200'
                                    "
                                />
                            </template>
                        </div>
                        <p class="text-xs text-gray-500">
                            <span v-if="p.status === 'pending'">Awaiting Super Admin review</span>
                            <span v-else-if="p.status === 'approved'">
                                Approved{{ p.approver_name ? ` by ${p.approver_name}` : '' }}
                                {{ p.approved_at ? ` on ${p.approved_at}` : '' }} — visible to
                                customers
                            </span>
                            <span v-else-if="p.status === 'rejected'" class="text-red-700">
                                Rejected: {{ p.rejection_reason }}
                            </span>
                            <span v-else-if="p.status === 'rented'">Active rental in progress</span>
                        </p>
                        <p v-if="p.status === 'approved'" class="mt-2 text-xs text-gray-500">
                            {{ p.requests_count }} customer rental request(s)
                        </p>
                        <p
                            v-if="p.has_ar_model"
                            class="mt-2 inline-flex items-center gap-1 text-xs font-medium emerald-text-primary"
                        >
                            <span class="material-symbols-outlined text-sm">view_in_ar</span>
                            3D AR model attached
                        </p>
                    </div>
                </div>
            </GlassCard>
        </div>

        <div v-if="activeTab === 'add'" class="mt-8">
            <h3 class="mb-4 text-lg font-semibold">Your listings</h3>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <template v-for="p in properties" :key="p.id">
                    <Link
                        v-if="p.status === 'rented'"
                        :href="`/land-owner/properties/${p.id}/rental`"
                        class="block transition hover:opacity-95"
                    >
                        <GlassCard class="overflow-hidden ring-2 ring-transparent hover:ring-[var(--emerald-primary)]">
                            <img
                                :src="p.image_url ?? ''"
                                :alt="p.title"
                                class="h-40 w-full object-cover"
                            />
                            <div class="p-4">
                                <div class="mb-2 flex items-start justify-between">
                                    <h3 class="font-semibold">{{ p.title }}</h3>
                                    <StatusChip :label="p.status" />
                                </div>
                                <p class="text-sm text-gray-500">
                                    {{ p.city }} · ${{ p.price_per_month }}/mo
                                </p>
                                <p
                                    class="mt-2 inline-flex items-center gap-1 text-xs font-medium emerald-text-primary"
                                >
                                    <span class="material-symbols-outlined text-sm">receipt_long</span>
                                    View tenant &amp; payments
                                </p>
                            </div>
                        </GlassCard>
                    </Link>
                    <GlassCard v-else class="overflow-hidden">
                        <img
                            :src="p.image_url ?? ''"
                            :alt="p.title"
                            class="h-40 w-full object-cover"
                        />
                        <div class="p-4">
                            <div class="mb-2 flex items-start justify-between">
                                <h3 class="font-semibold">{{ p.title }}</h3>
                                <StatusChip :label="p.status" />
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ p.city }} · ${{ p.price_per_month }}/mo
                            </p>
                        </div>
                    </GlassCard>
                </template>
            </div>
        </div>
    </EmeraldLayout>
</template>
