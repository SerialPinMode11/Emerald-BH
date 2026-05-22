<script setup lang="ts">
import '../../../css/auth-login.css';

import { Form, Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { initializeTheme } from '@/composables/useAppearance';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: null,
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

type AuthTab = 'login' | 'register';
type RoleId = 'customer' | 'land_owner' | 'community' | 'super_admin' | 'dev_admin';

const roles: Array<{
    id: RoleId;
    label: string;
    icon: string;
    email: string;
}> = [
    { id: 'customer', label: 'Customer', icon: 'person', email: 'customer@emerald.test' },
    { id: 'land_owner', label: 'Land Owner', icon: 'cottage', email: 'owner@emerald.test' },
    { id: 'community', label: 'Mediator', icon: 'handshake', email: 'community@emerald.test' },
    { id: 'super_admin', label: 'Super Admin', icon: 'verified_user', email: 'admin@emerald.test' },
    { id: 'dev_admin', label: 'Dev Admin', icon: 'terminal', email: 'dev@emerald.test' },
];

const activeTab = ref<AuthTab>('login');
const selectedRole = ref<RoleId>('customer');
const email = ref('');
const showPassword = ref(false);

watch(selectedRole, (role) => {
    const match = roles.find((r) => r.id === role);
    if (match) {
        email.value = match.email;
    }
});

function selectRole(role: RoleId) {
    selectedRole.value = role;
}

function toggleTab(tab: AuthTab) {
    activeTab.value = tab;
}

onMounted(() => {
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('emerald-auth-body');
    document.body.classList.add('emerald-auth-body');
    email.value = roles[0].email;
});

onUnmounted(() => {
    document.documentElement.classList.remove('emerald-auth-body');
    document.body.classList.remove('emerald-auth-body');
    initializeTheme();
});
</script>

<template>
    <Head title="Sign In - Emerald Housing">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="emerald-auth-page emerald-auth-bg flex min-h-dvh w-full flex-1 flex-col">
        <main class="emerald-auth-main">
            <div class="emerald-auth-card">
                <header class="text-center">
                    <h1 class="emerald-auth-title">Emerald Housing</h1>
                    <p class="emerald-auth-subtitle">
                        Secure access to the tenant management ecosystem.
                    </p>
                </header>

                <div class="emerald-auth-toggle">
                    <button
                        type="button"
                        class="emerald-auth-toggle__btn"
                        :class="
                            activeTab === 'login'
                                ? 'emerald-auth-toggle__btn--active'
                                : 'emerald-auth-toggle__btn--idle'
                        "
                        @click="toggleTab('login')"
                    >
                        Login
                    </button>
                    <button
                        type="button"
                        class="emerald-auth-toggle__btn"
                        :class="
                            activeTab === 'register'
                                ? 'emerald-auth-toggle__btn--active'
                                : 'emerald-auth-toggle__btn--idle'
                        "
                        @click="toggleTab('register')"
                    >
                        Register
                    </button>
                </div>

                <div
                    v-if="status"
                    class="mt-5 rounded-xl border border-green-200/80 bg-green-50/90 px-4 py-3 text-center text-sm text-green-800"
                >
                    {{ status }}
                </div>

                <div
                    v-if="activeTab === 'register'"
                    class="mt-6 rounded-2xl border border-dashed border-white/80 bg-white/40 px-5 py-8 text-center"
                >
                    <span
                        class="material-symbols-outlined mb-2 block text-4xl"
                        style="color: var(--auth-purple)"
                        >how_to_reg</span
                    >
                    <p class="font-semibold" style="color: var(--auth-text)">
                        Account registration
                    </p>
                    <p class="mt-2 text-sm" style="color: var(--auth-label)">
                        New accounts are provisioned by a Super Admin. Please sign in
                        with an authorized identity.
                    </p>
                    <button
                        type="button"
                        class="mt-5 text-sm font-semibold hover:underline"
                        style="color: var(--auth-purple)"
                        @click="toggleTab('login')"
                    >
                        Back to Login
                    </button>
                </div>

                <Form
                    v-else
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="mt-6 flex flex-col gap-5"
                >
                    <div>
                        <p class="emerald-auth-roles-label">Select Your Identity</p>
                        <div class="emerald-auth-roles">
                            <button
                                v-for="role in roles"
                                :key="role.id"
                                type="button"
                                class="emerald-auth-role"
                                :class="{
                                    'emerald-auth-role--active':
                                        selectedRole === role.id,
                                }"
                                :title="`Demo: ${role.email}`"
                                @click="selectRole(role.id)"
                            >
                                <span
                                    class="material-symbols-outlined emerald-auth-role__icon"
                                    >{{ role.icon }}</span
                                >
                                <span class="emerald-auth-role__label">{{
                                    role.label
                                }}</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="emerald-auth-field-label"
                            >Email Address</label
                        >
                        <div class="emerald-auth-input-wrap">
                            <span class="material-symbols-outlined emerald-auth-input-icon"
                                >mail</span
                            >
                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="name@example.com"
                                class="emerald-auth-input"
                            />
                        </div>
                        <InputError :message="errors.email" class="mt-1.5 text-sm" />
                    </div>

                    <div>
                        <label for="password" class="emerald-auth-field-label"
                            >Password</label
                        >
                        <div class="emerald-auth-input-wrap">
                            <span class="material-symbols-outlined emerald-auth-input-icon"
                                >lock</span
                            >
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="emerald-auth-input emerald-auth-input--password"
                            />
                            <button
                                type="button"
                                class="emerald-auth-input-toggle"
                                tabindex="-1"
                                :aria-label="
                                    showPassword ? 'Hide password' : 'Show password'
                                "
                                @click="showPassword = !showPassword"
                            >
                                <span class="material-symbols-outlined text-[22px]">{{
                                    showPassword ? 'visibility_off' : 'visibility'
                                }}</span>
                            </button>
                        </div>
                        <InputError :message="errors.password" class="mt-1.5 text-sm" />
                    </div>

                    <div class="emerald-auth-row">
                        <label class="emerald-auth-remember">
                            <input type="checkbox" id="remember" name="remember" />
                            Remember me
                        </label>
                        <a
                            v-if="canResetPassword"
                            :href="request.url()"
                            class="emerald-auth-forgot"
                        >
                            Forgot Password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="emerald-auth-submit"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <span
                            v-if="processing"
                            class="material-symbols-outlined mr-1 inline-block animate-spin align-middle text-lg"
                            >progress_activity</span
                        >
                        Sign In
                    </button>

                    <div class="emerald-auth-divider">
                        <span>Or continue with</span>
                    </div>

                    <div class="emerald-auth-social">
                        <button type="button" class="emerald-auth-social-btn">
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                />
                                <path
                                    fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                />
                                <path
                                    fill="#FBBC05"
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                />
                                <path
                                    fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                />
                            </svg>
                            Google
                        </button>
                        <button type="button" class="emerald-auth-social-btn">
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"
                                />
                            </svg>
                            Apple
                        </button>
                    </div>
                </Form>
            </div>
        </main>

        <footer class="emerald-auth-footer">
            <div class="emerald-auth-footer__inner">
                <p class="emerald-auth-footer__copy">
                    © 2024 Emerald Housing Multi-Tenant Platform
                </p>
                <nav class="emerald-auth-footer__nav">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Settings</a>
                </nav>
                <span class="emerald-auth-footer__badge">
                    Authorized Professional Access Only
                </span>
            </div>
        </footer>
    </div>
</template>
