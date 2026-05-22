import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

type SessionFlash = {
    success?: string;
    error?: string;
    toast?: FlashToast;
};

let lastShownSuccess: string | undefined;
let lastShownError: string | undefined;

function showFlash(flash: SessionFlash | undefined): void {
    if (!flash) {
        return;
    }

    if (flash.toast?.message && flash.toast?.type) {
        toast[flash.toast.type](flash.toast.message);
        return;
    }

    if (flash.success && flash.success !== lastShownSuccess) {
        lastShownSuccess = flash.success;
        toast.success(flash.success);
    }

    if (flash.error && flash.error !== lastShownError) {
        lastShownError = flash.error;
        toast.error(flash.error);
    }
}

function showFlashFromCurrentPage(): void {
    const flash = usePage().props.flash as SessionFlash | undefined;
    showFlash(flash);
}

export function initializeFlashToast(): void {
    router.on('start', () => {
        lastShownSuccess = undefined;
        lastShownError = undefined;
    });

    router.on('flash', (event) => {
        showFlash((event as CustomEvent).detail?.flash as SessionFlash | undefined);
    });

    router.on('success', () => {
        // Run after Inertia swaps page props (session flash from redirect).
        queueMicrotask(() => {
            showFlashFromCurrentPage();
        });
    });

    router.on('error', (event) => {
        const errors = (event as { detail?: { errors?: Record<string, string> } }).detail
            ?.errors;
        const first = errors ? Object.values(errors)[0] : undefined;
        if (first) {
            toast.error(first);
        }
    });
}

/** @deprecated Use initializeFlashToast in app.ts only */
export function useEmeraldFlash(): void {
    showFlashFromCurrentPage();
}
