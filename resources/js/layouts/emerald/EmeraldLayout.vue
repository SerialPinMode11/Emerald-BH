<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useRoleTheme } from '@/composables/useRoleTheme';
import { Toaster } from '@/components/ui/sonner';
import { logout } from '@/routes';
import type { User } from '@/types';

type NavItem = {
    label: string;
    href: string;
    icon: string;
    active?: boolean;
};

const props = defineProps<{
    title?: string;
    nav?: NavItem[];
    showSidebar?: boolean;
    sidebarTitle?: string;
    sidebarSubtitle?: string;
}>();

const { user, theme, role } = useRoleTheme();
const page = usePage();

const authUser = computed(() => page.props.auth?.user as User | null);

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

function handleLogout() {
    router.flushAll();
}

</script>

<template>
    <div
        class="emerald-app"
        :data-role="role"
        :style="{
            '--emerald-primary': theme.primary,
            '--emerald-secondary': theme.secondary,
            '--emerald-accent': theme.accent,
        }"
    >
        <header
            class="sticky top-0 z-40 w-full border-b border-white/30 bg-white/60 shadow-sm backdrop-blur-xl"
        >
            <div
                class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-3 md:px-16"
            >
                <div class="flex items-center gap-3">
                    <span
                        v-if="role === 'super_admin'"
                        class="material-symbols-outlined emerald-text-primary text-2xl"
                        >home_work</span
                    >
                    <span class="text-xl font-bold emerald-text-primary md:text-2xl"
                        >Emerald Housing</span
                    >
                </div>
                <nav v-if="nav?.length" class="hidden items-center gap-6 md:flex">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="text-sm font-medium transition-colors"
                        :class="
                            item.active
                                ? 'font-bold emerald-text-primary'
                                : 'text-gray-600 hover:text-gray-900'
                        "
                    >
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-full p-2 transition hover:bg-white/30"
                        aria-label="Notifications"
                    >
                        <span class="material-symbols-outlined emerald-text-primary"
                            >notifications</span
                        >
                    </button>
                    <DropdownMenu v-if="authUser">
                        <DropdownMenuTrigger
                            class="rounded-full outline-none ring-offset-2 focus-visible:ring-2 focus-visible:ring-[var(--emerald-primary)]"
                            aria-label="Account menu"
                        >
                            <div
                                class="h-10 w-10 overflow-hidden rounded-full border-2"
                                :style="{ borderColor: `${theme.primary}33` }"
                            >
                                <img
                                    v-if="user?.profile_photo"
                                    :src="user.profile_photo"
                                    alt="Profile"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-sm font-bold text-white"
                                    :style="{ backgroundColor: theme.primary }"
                                >
                                    {{ user?.name?.charAt(0) ?? 'E' }}
                                </div>
                            </div>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuLabel class="font-normal">
                                <p class="truncate text-sm font-semibold text-gray-900">
                                    {{ authUser.name }}
                                </p>
                                <p class="truncate text-xs text-gray-500">
                                    {{ authUser.email }}
                                </p>
                                <p
                                    v-if="authUser.role_label"
                                    class="mt-1 truncate text-xs font-medium emerald-text-primary"
                                >
                                    {{ authUser.role_label }}
                                </p>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem :as-child="true">
                                <Link
                                    :href="logout()"
                                    method="post"
                                    as="button"
                                    class="flex w-full cursor-pointer items-center gap-2"
                                    data-test="logout-button"
                                    @click="handleLogout"
                                >
                                    <span class="material-symbols-outlined text-lg"
                                        >logout</span
                                    >
                                    Log out
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>

        <aside
            v-if="showSidebar"
            class="fixed bottom-0 left-0 top-16 z-30 hidden w-72 flex flex-col border-r border-white/30 bg-white/80 py-6 backdrop-blur-2xl md:flex"
        >
            <div class="mb-8 px-6">
                <h2 class="text-xl font-semibold emerald-text-primary">
                    {{ sidebarTitle ?? 'Console' }}
                </h2>
                <p class="text-sm text-gray-500">{{ sidebarSubtitle }}</p>
            </div>
            <nav class="flex flex-1 flex-col gap-1 px-2">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm transition"
                    :class="
                        item.active
                            ? 'font-bold emerald-bg-secondary emerald-text-primary'
                            : 'text-gray-600 hover:bg-gray-100'
                    "
                >
                    <span class="material-symbols-outlined text-xl">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>
            </nav>
            <div v-if="authUser" class="mt-auto border-t border-white/40 px-4 pt-4">
                <Link
                    :href="logout()"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-lg px-2 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900"
                    data-test="sidebar-logout-button"
                    @click="handleLogout"
                >
                    <span class="material-symbols-outlined text-xl">logout</span>
                    Log out
                </Link>
            </div>
        </aside>

        <main
            :class="[
                'mx-auto w-full max-w-7xl px-4 py-8',
                showSidebar ? 'md:pl-[calc(18rem+4rem)] md:pr-16' : 'md:px-16',
            ]"
        >
            <div
                v-if="flash?.success"
                class="emerald-glass mb-6 rounded-xl border-l-4 px-4 py-3 text-sm"
                :style="{ borderColor: theme.accent }"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash?.error"
                class="mb-6 rounded-xl border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{ flash.error }}
            </div>
            <slot />
        </main>

        <nav
            v-if="nav?.length && !showSidebar"
            class="fixed bottom-0 z-50 w-full rounded-t-xl border-t border-white/30 bg-white/60 backdrop-blur-xl md:hidden"
        >
            <div class="flex h-16 items-center justify-around px-4">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="flex flex-col items-center text-xs"
                    :class="item.active ? 'emerald-text-primary font-bold' : 'text-gray-500'"
                >
                    <span class="material-symbols-outlined">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>
            </div>
        </nav>
        <Toaster rich-colors theme="light" position="top-right" />
    </div>
</template>
