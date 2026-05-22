<script setup lang="ts">
import { onMounted, ref } from 'vue';

const props = defineProps<{
    src: string;
    iosSrc?: string | null;
    title: string;
    poster?: string | null;
}>();

const ready = ref(false);
const loadError = ref(false);

const MODEL_VIEWER_CDN =
    'https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js';

onMounted(async () => {
    try {
        if (!customElements.get('model-viewer')) {
            await import(/* @vite-ignore */ MODEL_VIEWER_CDN);
        }
        ready.value = true;
    } catch {
        loadError.value = true;
    }
});
</script>

<template>
    <div class="ar-model-panel">
        <div class="ar-model-panel__header">
            <span class="material-symbols-outlined text-[var(--emerald-primary)]">view_in_ar</span>
            <div>
                <p class="font-semibold">3D / AR room model</p>
                <p class="text-xs text-gray-500">
                    Powered by
                    <a
                        href="https://modelviewer.dev/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="underline"
                        >Google &lt;model-viewer&gt;</a
                    >
                    — view in AR on mobile
                </p>
            </div>
        </div>

        <p v-if="loadError" class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
            Could not load the 3D viewer. Open this page in Chrome or Safari on a phone and tap
            the AR button.
        </p>
        <model-viewer
            v-else-if="ready"
            :src="src"
            :ios-src="iosSrc ?? undefined"
            :poster="poster ?? undefined"
            :alt="`${title} — 3D rental space`"
            ar
            ar-modes="webxr scene-viewer quick-look"
            camera-controls
            auto-rotate
            shadow-intensity="1"
            exposure="1"
            class="ar-model-panel__viewer"
        />
        <div
            v-else
            class="ar-model-panel__viewer flex items-center justify-center text-sm text-gray-500"
        >
            Loading 3D viewer…
        </div>
        <p class="mt-2 text-center text-xs text-gray-500">
            Drag to rotate · Pinch/scroll to zoom · On phone, tap
            <span class="material-symbols-outlined align-middle text-sm">view_in_ar</span> for AR
        </p>
    </div>
</template>

<style scoped>
.ar-model-panel {
    border-radius: 1rem;
    border: 1px solid rgba(81, 58, 143, 0.12);
    background: linear-gradient(160deg, #f8f2fa 0%, #fff 100%);
    padding: 1rem;
}

.ar-model-panel__header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.ar-model-panel__viewer {
    width: 100%;
    height: 360px;
    border-radius: 0.75rem;
    background: #e8e8ec;
}
</style>
