<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="app-shell">
            <!-- Navbar -->
            <nav class="app-nav">
                <div class="app-nav-inner">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="app-brand">
                        <div class="app-brand-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </div>
                        PrintCheck AI
                    </Link>

                    <!-- Desktop nav links -->
                    <div class="app-nav-links">
                        <Link :href="route('dashboard')" :class="['app-nav-link', route().current('dashboard') ? 'app-nav-link--active' : '']">
                            Dashboard
                        </Link>
                    </div>

                    <!-- Right side: teams + user -->
                    <div class="app-nav-right">
                        <!-- Teams Dropdown -->
                        <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                            <template #trigger>
                                <button type="button" class="app-dropdown-trigger">
                                    {{ $page.props.auth.user.current_team.name }}
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="app-dropdown-content">
                                    <div class="app-dropdown-section-label">Gestionar equipo</div>
                                    <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">Ajustes del equipo</DropdownLink>
                                    <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">Crear nuevo equipo</DropdownLink>
                                    <template v-if="$page.props.auth.user.all_teams.length > 1">
                                        <div class="app-dropdown-divider" />
                                        <div class="app-dropdown-section-label">Cambiar equipo</div>
                                        <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                            <form @submit.prevent="switchToTeam(team)">
                                                <DropdownLink as="button">
                                                    <div style="display:flex;align-items:center;gap:8px">
                                                        <svg v-if="team.id == $page.props.auth.user.current_team_id" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ team.name }}
                                                    </div>
                                                </DropdownLink>
                                            </form>
                                        </template>
                                    </template>
                                </div>
                            </template>
                        </Dropdown>

                        <!-- User Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button v-if="$page.props.jetstream.managesProfilePhotos" class="app-avatar-btn">
                                    <img class="app-avatar-img" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                </button>
                                <button v-else type="button" class="app-dropdown-trigger">
                                    {{ $page.props.auth.user.name }}
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="app-dropdown-content">
                                    <div class="app-dropdown-section-label">Mi cuenta</div>
                                    <DropdownLink :href="route('profile.show')">Perfil</DropdownLink>
                                    <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">Tokens API</DropdownLink>
                                    <div class="app-dropdown-divider" />
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button">Cerrar sesión</DropdownLink>
                                    </form>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Hamburger -->
                    <button class="app-hamburger" @click="showingNavigationDropdown = !showingNavigationDropdown">
                        <svg width="22" height="22" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Mobile menu -->
                <div v-show="showingNavigationDropdown" class="app-mobile-menu">
                    <div class="app-mobile-links">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Dashboard</ResponsiveNavLink>
                    </div>
                    <div class="app-mobile-user">
                        <div class="app-mobile-user-info">
                            <img v-if="$page.props.jetstream.managesProfilePhotos" class="app-avatar-img" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            <div>
                                <div class="app-mobile-name">{{ $page.props.auth.user.name }}</div>
                                <div class="app-mobile-email">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>
                        <div class="app-mobile-actions">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">Perfil</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">Tokens API</ResponsiveNavLink>
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">Cerrar sesión</ResponsiveNavLink>
                            </form>
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="app-dropdown-divider" />
                                <div class="app-dropdown-section-label" style="padding:8px 16px">Gestionar equipo</div>
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')">Ajustes del equipo</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">Crear nuevo equipo</ResponsiveNavLink>
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="app-dropdown-divider" />
                                    <div class="app-dropdown-section-label" style="padding:8px 16px">Cambiar equipo</div>
                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">{{ team.name }}</ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="app-page-header">
                <div class="app-container">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="app-main">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.app-shell {
    min-height: 100vh;
    background: #f8fafc;
    font-family: 'Inter', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
}

/* ── Navbar ──────────────────────────────── */
.app-nav {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e2e8f0;
}

.app-nav-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
    height: 64px;
    display: flex;
    align-items: center;
    gap: 32px;
}

.app-brand {
    display: flex;
    align-items: center;
    gap: 9px;
    font-weight: 800;
    font-size: 17px;
    color: #0f172a;
    text-decoration: none;
    flex-shrink: 0;
}

.app-brand-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #dcfce7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    flex-shrink: 0;
}

.app-nav-links {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
}

.app-nav-link {
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    text-decoration: none;
    transition: background .15s, color .15s;
}

.app-nav-link:hover {
    background: #f1f5f9;
}

.app-nav-link--active {
    background: #dcfce7;
    color: #15803d;
    font-weight: 600;
}

.app-nav-right {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

@media (max-width: 640px) {
    .app-nav-links,
    .app-nav-right {
        display: none;
    }
}

/* ── Dropdown trigger ─────────────────────── */
.app-dropdown-trigger {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    background: #fff;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    font-family: 'Inter', system-ui, sans-serif;
}

.app-dropdown-trigger:hover {
    border-color: #cbd5e1;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
}

.app-dropdown-content {
    padding: 6px;
}

.app-dropdown-section-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #94a3b8;
    padding: 6px 10px 4px;
}

.app-dropdown-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 6px 0;
}

/* ── Avatar ──────────────────────────────── */
.app-avatar-btn {
    background: none;
    border: 2px solid transparent;
    border-radius: 50%;
    padding: 0;
    cursor: pointer;
    transition: border-color .15s;
}

.app-avatar-btn:hover {
    border-color: #16a34a;
}

.app-avatar-img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

/* ── Hamburger ───────────────────────────── */
.app-hamburger {
    display: none;
    margin-left: auto;
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    transition: background .15s;
}

.app-hamburger:hover {
    background: #f1f5f9;
}

@media (max-width: 640px) {
    .app-hamburger {
        display: flex;
    }
}

/* ── Mobile menu ─────────────────────────── */
.app-mobile-menu {
    border-top: 1px solid #e2e8f0;
    padding-bottom: 8px;
}

.app-mobile-links {
    padding: 8px 0;
}

.app-mobile-user {
    border-top: 1px solid #e2e8f0;
    padding-top: 12px;
}

.app-mobile-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 16px 12px;
}

.app-mobile-name {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}

.app-mobile-email {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.app-mobile-actions {
    padding: 0;
}

/* ── Page header ─────────────────────────── */
.app-page-header {
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
}

.app-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 20px 24px;
}

/* ── Main content ────────────────────────── */
.app-main {
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 24px;
}
</style>
