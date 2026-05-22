<script setup lang="ts">
import { computed, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        lengthM?: number | null;
        widthM?: number | null;
        heightM?: number | null;
        title?: string;
    }>(),
    {
        lengthM: 5,
        widthM: 4,
        heightM: 2.5,
        title: 'Rental space',
    },
);

const rotateX = ref(-18);
const rotateY = ref(32);
const isDragging = ref(false);
let lastX = 0;
let lastY = 0;

const length = computed(() => Number(props.lengthM) || 5);
const width = computed(() => Number(props.widthM) || 4);
const height = computed(() => Number(props.heightM) || 2.5);
const scale = 36;

const floorStyle = computed(() => ({
    width: `${width.value * scale}px`,
    height: `${length.value * scale}px`,
}));

const wallHeight = computed(() => `${height.value * scale}px`);

function onPointerDown(e: PointerEvent) {
    isDragging.value = true;
    lastX = e.clientX;
    lastY = e.clientY;
    (e.target as HTMLElement).setPointerCapture?.(e.pointerId);
}

function onPointerMove(e: PointerEvent) {
    if (!isDragging.value) {
        return;
    }
    rotateY.value += (e.clientX - lastX) * 0.4;
    rotateX.value -= (e.clientY - lastY) * 0.3;
    lastX = e.clientX;
    lastY = e.clientY;
}

function onPointerUp() {
    isDragging.value = false;
}
</script>

<template>
    <div class="ar-viewer">
        <div class="ar-viewer__header">
            <span class="material-symbols-outlined text-[var(--emerald-primary)]">view_in_ar</span>
            <div>
                <p class="font-semibold">{{ title }}</p>
                <p class="text-xs text-gray-500">
                    Drag to rotate · {{ length }}m × {{ width }}m × {{ height }}m
                </p>
            </div>
        </div>
        <div
            class="ar-viewer__stage"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointerleave="onPointerUp"
        >
            <div
                class="ar-viewer__room"
                :style="{
                    transform: `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`,
                }"
            >
                <div class="ar-viewer__floor" :style="floorStyle">
                    <span class="ar-viewer__dim ar-viewer__dim--w">{{ width }}m</span>
                    <span class="ar-viewer__dim ar-viewer__dim--l">{{ length }}m</span>
                </div>
                <div class="ar-viewer__wall ar-viewer__wall--front" :style="{ height: wallHeight }" />
                <div class="ar-viewer__wall ar-viewer__wall--side" :style="{ height: wallHeight }" />
                <span class="ar-viewer__dim ar-viewer__dim--h">{{ height }}m</span>
            </div>
        </div>
        <p class="ar-viewer__hint text-center text-xs text-gray-500">
            Approximate floor plan preview — measure on-site before signing.
        </p>
    </div>
</template>

<style scoped>
.ar-viewer {
    border-radius: 1rem;
    border: 1px solid rgba(81, 58, 143, 0.12);
    background: linear-gradient(160deg, #f8f2fa 0%, #ece6ee 100%);
    padding: 1rem;
}

.ar-viewer__header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.ar-viewer__stage {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    cursor: grab;
    touch-action: none;
    perspective: 900px;
}

.ar-viewer__stage:active {
    cursor: grabbing;
}

.ar-viewer__room {
    position: relative;
    transform-style: preserve-3d;
    transition: transform 0.05s linear;
}

.ar-viewer__floor {
    position: relative;
    background: color-mix(in srgb, var(--emerald-primary) 25%, white);
    border: 2px solid var(--emerald-primary);
    transform: rotateX(90deg) translateZ(0);
    transform-origin: center bottom;
    box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.08);
}

.ar-viewer__wall {
    position: absolute;
    background: color-mix(in srgb, var(--emerald-primary) 12%, rgba(255, 255, 255, 0.85));
    border: 1px solid color-mix(in srgb, var(--emerald-primary) 40%, transparent);
    opacity: 0.85;
}

.ar-viewer__wall--front {
    width: 100%;
    bottom: 0;
    left: 0;
    transform-origin: bottom center;
    transform: rotateX(-90deg);
}

.ar-viewer__wall--side {
    width: 2px;
    bottom: 0;
    right: 0;
    height: 100%;
    transform: rotateY(-90deg) translateX(100%);
    transform-origin: right bottom;
}

.ar-viewer__dim {
    position: absolute;
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--emerald-primary);
    background: rgba(255, 255, 255, 0.9);
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}

.ar-viewer__dim--w {
    top: -1.25rem;
    left: 50%;
    transform: translateX(-50%);
}

.ar-viewer__dim--l {
    right: -2.5rem;
    top: 50%;
    transform: translateY(-50%);
}

.ar-viewer__dim--h {
    left: -2.25rem;
    top: 30%;
}
</style>
