<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as SonnerPrimitive, type ToasterProps } from 'vue-sonner';
import 'vue-sonner/style.css';

const props = withDefaults(
    defineProps<{
        theme?: ToasterProps['theme'];
        position?: ToasterProps['position'];
        richColors?: boolean;
    }>(),
    {
        position: 'bottom-right',
    },
);

const { appearance } = useAppearance();
</script>

<template>
    <SonnerPrimitive
        :theme="props.theme ?? appearance"
        :rich-colors="props.richColors"
        class="toaster group"
        :position="props.position"
        :style="{
            '--normal-bg': 'var(--popover)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border': 'var(--border)',
        }"
    />
</template>
