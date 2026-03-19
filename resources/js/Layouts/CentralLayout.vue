<template>
    <div class="central-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-icon">🏢</span>
                    <span class="logo-text">PMS Central</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">MAIN</div>
                <Link
                    :href="route('central.dashboard')"
                    class="nav-item"
                    :class="{ active: $page.component === 'Central/Dashboard' }"
                >
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Dashboard</span>
                </Link>

                <Link
                    :href="route('central.tenants.index')"
                    class="nav-item"
                    :class="{
                        active: $page.component.startsWith('Central/Tenants'),
                    }"
                >
                    <span class="nav-icon">🏢</span>
                    <span class="nav-label">Tenants</span>
                </Link>

                <Link
                    :href="route('central.register')"
                    class="nav-item"
                    :class="{
                        active: $page.component === 'Central/Tenants/Create',
                    }"
                >
                    <span class="nav-icon">➕</span>
                    <span class="nav-label">New Tenant</span>
                </Link>

                <div class="nav-section">REPORTS</div>
                <Link
                    :href="route('central.dashboard')"
                    class="nav-item"
                    :class="{
                        active: $page.component === 'Central/Reports/Overview',
                    }"
                >
                    <span class="nav-icon">📈</span>
                    <span class="nav-label">Overview</span>
                </Link>

                <Link
                    :href="route('central.dashboard')"
                    class="nav-item"
                    :class="{
                        active: $page.component === 'Central/Reports/Analytics',
                    }"
                >
                    <span class="nav-icon">📉</span>
                    <span class="nav-label">Analytics</span>
                </Link>

                <div class="nav-section">SYSTEM</div>
                <Link
                    :href="route('central.profile')"
                    class="nav-item"
                    :class="{ active: $page.component === 'Central/Profile' }"
                >
                    <span class="nav-icon">👤</span>
                    <span class="nav-label">Profile</span>
                </Link>

                <Link
                    :href="route('central.dashboard')"
                    class="nav-item"
                    :class="{ active: $page.component === 'Central/Settings' }"
                >
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-label">Settings</span>
                </Link>
            </nav>

            <div class="sidebar-footer">
                <div class="user-card" v-if="$page.props.auth?.user">
                    <div class="avatar">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="user-role">Super Admin</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle">
                        <span class="menu-icon">☰</span>
                    </button>
                    <div class="search-bar">
                        <span class="search-icon">🔍</span>
                        <input
                            type="text"
                            placeholder="Search tenants, users..."
                        />
                    </div>
                </div>

                <div class="topbar-right">
                    <button class="icon-button notification-btn">
                        <span class="icon">🔔</span>
                        <span class="badge">3</span>
                    </button>

                    <div class="divider"></div>

                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('central.logout')"
                        method="post"
                        as="button"
                        class="btn-logout"
                    >
                        <span class="icon">🚪</span> Logout
                    </Link>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <div class="content-container">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
</script>

<style scoped>
/* Reset and Base Styles */
* {
    box-sizing: border-box;
}

.central-layout {
    display: flex;
    min-height: 100vh;
    background-color: #f3f4f6;
    font-family:
        "Inter",
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        Roboto,
        Helvetica,
        Arial,
        sans-serif;
}

/* Sidebar Styling */
.sidebar {
    width: 260px;
    background-color: #ffffff;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 40;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02);
}

.sidebar-header {
    height: 72px;
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    font-size: 1.5rem;
}

.logo-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    letter-spacing: -0.025em;
}

.sidebar-nav {
    flex: 1;
    padding: 1.5rem 1rem;
    overflow-y: auto;
}

.nav-section {
    font-size: 0.75rem;
    font-weight: 600;
    color: #9ca3af;
    letter-spacing: 0.05em;
    padding: 0 0.75rem;
    margin-bottom: 0.5rem;
    margin-top: 1.5rem;
}

.nav-section:first-child {
    margin-top: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #4b5563;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 0.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-item:hover {
    background-color: #f9fafb;
    color: #111827;
}

.nav-item.active {
    background-color: #eef2ff;
    color: #4f46e5;
}

.nav-icon {
    font-size: 1.25rem;
    margin-right: 0.75rem;
    opacity: 0.7;
    transition: transform 0.2s ease;
}

.nav-item:hover .nav-icon {
    transform: scale(1.1);
}

.nav-item.active .nav-icon {
    opacity: 1;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
}

.user-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    background-color: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.user-info {
    flex: 1;
    overflow: hidden;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.user-role {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Main Content Wrapper */
.main-wrapper {
    flex: 1;
    margin-left: 260px; /* Same as sidebar width */
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Topbar Styling */
.topbar {
    height: 72px;
    background-color: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    position: sticky;
    top: 0;
    z-index: 30;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #4b5563;
    cursor: pointer;
}

.search-bar {
    display: flex;
    align-items: center;
    background-color: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    width: 300px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.search-bar:focus-within {
    background-color: #ffffff;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.search-icon {
    color: #9ca3af;
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.search-bar input {
    background: transparent;
    border: none;
    outline: none;
    width: 100%;
    color: #111827;
    font-size: 0.875rem;
}

.search-bar input::placeholder {
    color: #9ca3af;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-button {
    background: none;
    border: none;
    position: relative;
    cursor: pointer;
    padding: 0.5rem;
    color: #4b5563;
    border-radius: 50%;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-button:hover {
    background-color: #f3f4f6;
    color: #111827;
}

.icon-button .icon {
    font-size: 1.25rem;
}

.notification-btn .badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background-color: #ef4444;
    color: white;
    font-size: 0.65rem;
    font-weight: 700;
    height: 16px;
    min-width: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
}

.divider {
    height: 24px;
    width: 1px;
    background-color: #e5e7eb;
}

.btn-logout {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    color: #4b5563;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-logout:hover {
    background-color: #fee2e2;
    color: #dc2626;
}

/* Page Content */
.page-content {
    flex: 1;
    padding: 2rem;
}

.content-container {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

/* Responsive */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .main-wrapper {
        margin-left: 0;
    }

    .menu-toggle {
        display: block;
    }

    .search-bar {
        width: 200px;
    }
}

@media (max-width: 640px) {
    .search-bar {
        display: none;
    }

    .page-content {
        padding: 1rem;
    }
}
</style>
