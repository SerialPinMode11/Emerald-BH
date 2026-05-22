<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import GlassCard from '@/components/emerald/GlassCard.vue';
import StatusChip from '@/components/emerald/StatusChip.vue';
import EmeraldLayout from '@/layouts/emerald/EmeraldLayout.vue';

defineProps<{
    stats: { pending: number; in_progress: number; deployed: number; critical: number };
    changeRequests: Array<{
        id: number;
        request_type: string;
        description: string;
        priority: string;
        status: string;
        dev_admin_note: string | null;
        requester: { name: string };
        created_at: string;
    }>;
}>();

const nav = [
    { label: 'Queue', href: '/dev-admin/dashboard', icon: 'terminal', active: true },
];

function updateStatus(id: number, status: string) {
    router.patch(`/dev-admin/change-requests/${id}`, { status });
}

function deploy(id: number) {
    router.put(`/dev-admin/change-requests/${id}/deploy`);
}
</script>

<template>
    <Head title="Dev Admin Console" />
    <EmeraldLayout
        :nav="nav"
        show-sidebar
        sidebar-title="Dev Console"
        sidebar-subtitle="Change Management Only"
    >
        <section class="mb-8">
            <h2 class="text-2xl font-semibold" style="color: var(--emerald-primary)">
                System Configuration
            </h2>
            <p class="text-sm opacity-70">
                Isolated dev workspace — change requests and deployment queue only.
            </p>
        </section>

        <div class="mb-8 grid grid-cols-2 gap-4 md:grid-cols-4">
            <GlassCard padding="p-4">
                <p class="text-xs uppercase opacity-60">Pending</p>
                <p class="text-3xl font-bold">{{ stats.pending }}</p>
            </GlassCard>
            <GlassCard padding="p-4">
                <p class="text-xs uppercase opacity-60">In Progress</p>
                <p class="text-3xl font-bold" style="color: var(--emerald-accent)">{{ stats.in_progress }}</p>
            </GlassCard>
            <GlassCard padding="p-4">
                <p class="text-xs uppercase opacity-60">Deployed</p>
                <p class="text-3xl font-bold text-green-400">{{ stats.deployed }}</p>
            </GlassCard>
            <GlassCard padding="p-4">
                <p class="text-xs uppercase opacity-60">Critical Open</p>
                <p class="text-3xl font-bold text-red-400">{{ stats.critical }}</p>
            </GlassCard>
        </div>

        <GlassCard class="emerald-glass-dark overflow-hidden" padding="p-0">
            <div class="border-b border-white/10 px-4 py-3 font-mono text-sm opacity-70">
                change_requests_queue.log
            </div>
            <div class="divide-y divide-white/5">
                <div
                    v-for="cr in changeRequests"
                    :key="cr.id"
                    class="flex flex-col gap-4 p-4 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex-1 font-mono text-sm">
                        <div class="mb-1 flex flex-wrap gap-2">
                            <StatusChip :label="cr.priority" :variant="cr.priority === 'critical' ? 'error' : 'default'" />
                            <StatusChip :label="cr.status" />
                            <span class="text-xs opacity-50">{{ cr.request_type }}</span>
                        </div>
                        <p class="mb-1 text-base font-sans font-semibold">{{ cr.description }}</p>
                        <p class="text-xs opacity-50">
                            Requested by {{ cr.requester.name }} • {{ cr.created_at }}
                        </p>
                        <p v-if="cr.dev_admin_note" class="mt-2 text-xs text-[var(--emerald-accent)]">
                            Note: {{ cr.dev_admin_note }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-if="cr.status === 'pending'"
                            type="button"
                            class="rounded border border-white/20 px-3 py-1 text-xs hover:bg-white/10"
                            @click="updateStatus(cr.id, 'in_progress')"
                        >
                            Start
                        </button>
                        <button
                            v-if="cr.status !== 'deployed'"
                            type="button"
                            class="emerald-btn-primary rounded px-3 py-1 text-xs font-semibold"
                            @click="deploy(cr.id)"
                        >
                            Deploy
                        </button>
                        <button
                            v-if="cr.status !== 'rejected' && cr.status !== 'deployed'"
                            type="button"
                            class="rounded border border-red-400/50 px-3 py-1 text-xs text-red-400"
                            @click="updateStatus(cr.id, 'rejected')"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </GlassCard>
    </EmeraldLayout>
</template>
