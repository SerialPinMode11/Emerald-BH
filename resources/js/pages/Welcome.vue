<script setup lang="ts">
import '../../css/public-landing.css';

import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import { dashboard, home, login } from '@/routes';

type FeaturedProperty = {
    id: number;
    title: string;
    city: string;
    price_per_month: number | string;
    image_url: string | null;
    owner_name?: string | null;
};

const props = defineProps<{
    featured: FeaturedProperty[];
    stats: {
        listings: number;
        cities: number;
    };
}>();

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const features = [
    {
        icon: 'search',
        title: 'Explore verified homes',
        text: 'Browse approved listings with transparent pricing, deposits, and lease terms.',
    },
    {
        icon: 'handshake',
        title: 'Community mediation',
        text: 'Dedicated mediators review agreements and resolve disputes fairly.',
    },
    {
        icon: 'verified_user',
        title: 'Super Admin oversight',
        text: 'Every property is reviewed before it goes live on the platform.',
    },
    {
        icon: 'home_work',
        title: 'Owner dashboards',
        text: 'Land owners manage listings, agreements, and rental workflows in one place.',
    },
];

const steps = [
    {
        title: 'Create your account',
        text: 'Register as a customer or sign in with your authorized role to access the platform.',
    },
    {
        title: 'Find & request a rental',
        text: 'Explore approved properties, submit a rental request, and track agreement progress.',
    },
    {
        title: 'Sign with confidence',
        text: 'Complete digital agreements with mediator review and admin oversight built in.',
    },
];

function formatPrice(value: number | string): string {
    const amount = Number(value);
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);
}

onMounted(() => {
    document.documentElement.classList.remove('dark');
});

onUnmounted(() => {
    initializeTheme();
});
</script>

<template>
    <Head title="Emerald Housing — Multi-Tenant Rental Platform">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="emerald-public min-h-dvh">
        <section class="emerald-public-hero">
            <header class="emerald-public-nav">
                <Link :href="home()" class="emerald-public-logo">Emerald Housing</Link>
                <div class="emerald-public-nav__actions">
                    <Link
                        v-if="isAuthenticated"
                        :href="dashboard()"
                        class="emerald-public-btn-ghost"
                    >
                        Dashboard
                    </Link>
                    <Link v-else :href="login()" class="emerald-public-btn-ghost">
                        Log in
                    </Link>
                    <Link
                        :href="isAuthenticated ? dashboard() : login()"
                        class="emerald-public-btn-primary"
                    >
                        {{ isAuthenticated ? 'My dashboard' : 'Get started' }}
                    </Link>
                </div>
            </header>

            <div class="emerald-public-hero__content emerald-public-container px-5 pb-16 md:px-10">
                <h1 class="emerald-public-hero__title">
                    Fair, transparent rentals for everyone
                </h1>
                <p class="emerald-public-hero__subtitle">
                    Emerald Housing connects tenants, land owners, community mediators, and
                    administrators in one secure multi-tenant platform — from listing approval
                    to signed agreements.
                </p>
                <div class="emerald-public-hero__cta">
                    <Link
                        :href="isAuthenticated ? dashboard() : login()"
                        class="emerald-public-btn-primary emerald-public-btn-primary--lg"
                    >
                        {{ isAuthenticated ? 'Go to dashboard' : 'Sign in to explore' }}
                        <span class="material-symbols-outlined text-xl">arrow_forward</span>
                    </Link>
                    <a href="#listings" class="emerald-public-btn-outline">
                        View listings
                    </a>
                </div>
                <div class="emerald-public-stats">
                    <div class="emerald-public-stat">
                        <div class="emerald-public-stat__value">{{ stats.listings }}</div>
                        <div class="emerald-public-stat__label">Approved listings</div>
                    </div>
                    <div class="emerald-public-stat">
                        <div class="emerald-public-stat__value">{{ stats.cities }}</div>
                        <div class="emerald-public-stat__label">Cities available</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="emerald-public-section emerald-public-section--muted">
            <div class="emerald-public-container">
                <h2 class="emerald-public-section__title">Built for every role</h2>
                <p class="emerald-public-section__subtitle">
                    A single ecosystem with role-based access — customers discover homes,
                    owners manage properties, mediators ensure fairness, and admins keep
                    operations secure.
                </p>
                <div class="emerald-public-features">
                    <article
                        v-for="feature in features"
                        :key="feature.title"
                        class="emerald-public-feature"
                    >
                        <div class="emerald-public-feature__icon">
                            <span class="material-symbols-outlined">{{ feature.icon }}</span>
                        </div>
                        <h3 class="emerald-public-feature__title">{{ feature.title }}</h3>
                        <p class="emerald-public-feature__text">{{ feature.text }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="listings" class="emerald-public-section">
            <div class="emerald-public-container">
                <h2 class="emerald-public-section__title">Featured properties</h2>
                <p class="emerald-public-section__subtitle">
                    Verified listings ready to explore. Sign in as a customer to request a
                    rental and start your agreement.
                </p>

                <div v-if="featured.length" class="emerald-public-listings">
                    <article
                        v-for="property in featured"
                        :key="property.id"
                        class="emerald-public-card"
                    >
                        <img
                            v-if="property.image_url"
                            :src="property.image_url"
                            :alt="property.title"
                            class="emerald-public-card__image"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="emerald-public-card__image flex items-center justify-center"
                        >
                            <span
                                class="material-symbols-outlined text-5xl"
                                style="color: var(--public-purple)"
                                >apartment</span
                            >
                        </div>
                        <div class="emerald-public-card__body">
                            <h3 class="emerald-public-card__title">{{ property.title }}</h3>
                            <p class="emerald-public-card__meta">
                                {{ property.city }}
                                <span v-if="property.owner_name">
                                    · {{ property.owner_name }}</span
                                >
                            </p>
                            <p class="emerald-public-card__price">
                                {{ formatPrice(property.price_per_month) }}
                                <span class="text-sm font-medium text-[var(--public-muted)]"
                                    >/ month</span
                                >
                            </p>
                        </div>
                    </article>
                </div>
                <p v-else class="emerald-public-empty">
                    No approved listings yet. Run
                    <code class="rounded bg-white/80 px-1.5 py-0.5 text-sm">php artisan
                        migrate --seed</code
                    >
                    to load demo properties.
                </p>
            </div>
        </section>

        <section class="emerald-public-section emerald-public-section--muted">
            <div class="emerald-public-container">
                <h2 class="emerald-public-section__title">How it works</h2>
                <p class="emerald-public-section__subtitle">
                    From discovery to signed lease — every step is tracked and overseen.
                </p>
                <div class="emerald-public-steps">
                    <article
                        v-for="step in steps"
                        :key="step.title"
                        class="emerald-public-step"
                    >
                        <h3 class="emerald-public-step__title">{{ step.title }}</h3>
                        <p class="emerald-public-step__text">{{ step.text }}</p>
                    </article>
                </div>

                <div class="emerald-public-cta-band">
                    <h3 class="emerald-public-cta-band__title">Ready to get started?</h3>
                    <p class="emerald-public-cta-band__text">
                        Customers can register for an account on the sign-in page. Staff roles
                        are provisioned by a Super Admin.
                    </p>
                    <div class="emerald-public-cta-band__actions">
                        <Link
                            :href="login()"
                            class="emerald-public-btn-outline"
                        >
                            Sign in
                        </Link>
                        <Link
                            v-if="isAuthenticated"
                            :href="dashboard()"
                            class="emerald-public-btn-primary emerald-public-btn-primary--lg"
                        >
                            Open dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <footer class="emerald-public-footer">
            <div class="emerald-public-footer__inner">
                <span class="emerald-public-footer__brand">Emerald Housing</span>
                <nav class="emerald-public-footer__links">
                    <Link :href="login()">Sign in</Link>
                    <a href="#listings">Listings</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </nav>
                <span>© {{ new Date().getFullYear() }} Emerald Housing</span>
            </div>
        </footer>
    </div>
</template>
