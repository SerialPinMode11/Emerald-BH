import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export type RoleTheme = {
    primary: string;
    secondary: string;
    accent: string;
};

export function useRoleTheme() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user as {
        role?: string;
        theme?: RoleTheme;
        name?: string;
        profile_photo?: string | null;
    } | null);

    const theme = computed<RoleTheme>(() => user.value?.theme ?? {
        primary: '#2C7DA0',
        secondary: '#E9F5F9',
        accent: '#F6AE6D',
    });

    const role = computed(() => user.value?.role ?? 'customer');

    return { user, theme, role };
}
