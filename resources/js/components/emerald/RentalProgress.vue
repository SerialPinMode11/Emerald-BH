<script setup lang="ts">
const steps = [
    { key: 'listed', label: 'Listed' },
    { key: 'approved', label: 'Approved' },
    { key: 'rented', label: 'Rented' },
    { key: 'completed', label: 'Completed' },
];

defineProps<{
    current: string;
}>();

function stepIndex(status: string): number {
    const map: Record<string, number> = {
        pending: 0,
        approved: 1,
        rented: 2,
        active: 2,
        requested: 1,
        community_review: 2,
        completed: 3,
        terminated: 3,
        rejected: 0,
    };
    return map[status] ?? 0;
}
</script>

<template>
    <div class="flex items-center gap-2">
        <template v-for="(step, i) in steps" :key="step.key">
            <div class="flex flex-col items-center gap-1">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold"
                    :class="
                        i <= stepIndex(current)
                            ? 'emerald-btn-primary text-white'
                            : 'bg-gray-200 text-gray-500'
                    "
                >
                    {{ i + 1 }}
                </div>
                <span class="text-[10px] font-medium text-gray-600">{{ step.label }}</span>
            </div>
            <div
                v-if="i < steps.length - 1"
                class="mb-4 h-0.5 flex-1"
                :class="i < stepIndex(current) ? 'bg-[var(--emerald-primary)]' : 'bg-gray-200'"
            />
        </template>
    </div>
</template>
