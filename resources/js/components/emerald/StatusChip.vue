<script setup lang="ts">
defineProps<{
    label: string;
    variant?: 'default' | 'success' | 'warning' | 'error';
}>();
</script>

<template>
    <span
        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide"
        :class="{
            'emerald-chip': variant === 'default' || !variant,
            'bg-green-100 text-green-800': variant === 'success',
            'bg-amber-100 text-amber-800': variant === 'warning',
            'bg-red-100 text-red-800': variant === 'error',
        }"
    >
        {{ label }}
    </span>
</template>
