<script setup lang="ts">
defineProps<{
    padding?: string;
}>();
</script>

<template>
    <div :class="['emerald-glass rounded-xl', padding ?? 'p-4']">
        <slot />
    </div>
</template>
