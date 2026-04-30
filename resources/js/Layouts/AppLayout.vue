// FILE: resources/js/Layouts/AppLayout.vue
<template>
    <div class="flex h-screen bg-[#F8F9FB] overflow-hidden">
        <!-- ================================================================
             SIDEBAR
        ================================================================ -->
        <aside
            :class="[
                'relative flex flex-col bg-white  border-slate-200/80',
                'transition-[width] duration-300 ease-in-out z-40 flex-shrink-0',
                sidebarOpen ? 'w-[220px]' : 'w-[60px]',
            ]"
        >
            <!-- Logo -->
            <div
                class="flex items-center gap-3 px-3.5 h-[60px] border-slate-100 overflow-hidden flex-shrink-0"
            >
                <div
                    class="w-8 h-8 flex-shrink-0 rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center shadow-sm shadow-cyan-200"
                >
                    <Building2 class="w-4 h-4 text-white" />
                </div>
                <Transition name="fade-slide">
                    <span
                        v-if="sidebarOpen"
                        class="text-[15px] font-bold text-slate-800 whitespace-nowrap tracking-tight"
                    >
                        PMS
                    </span>
                </Transition>
            </div>

            <!-- Navigation -->
            <nav
                class="flex-1 overflow-y-auto overflow-x-visible py-3 space-y-0.5 px-2"
            >
                <template v-for="item in navItems" :key="item.label">
                    <!-- ── Group item (has children) ─────────────────── -->
                    <NavGroupItem
                        v-if="item.children"
                        :item="item"
                        :is-expanded="sidebarOpen"
                        :is-open="openGroups.includes(item.label)"
                        :is-active-fn="isActive"
                        @toggle="toggleGroup(item.label)"
                        @flyout-enter="(el) => onFlyoutEnter(item, el)"
                        @flyout-leave="onFlyoutLeave"
                    />

                    <!-- ── Single item ────────────────────────────────── -->
                    <NavSingleItem
                        v-else
                        :item="item"
                        :is-expanded="sidebarOpen"
                        :is-active-fn="isActive"
                        @flyout-enter="(el) => onFlyoutEnter(item, el)"
                        @flyout-leave="onFlyoutLeave"
                    />
                </template>
            </nav>

            <!-- Collapse toggle -->
            <div class="p-2 border-t border-slate-100 flex-shrink-0">
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="w-full flex items-center justify-center gap-2 py-2 rounded-xl text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all duration-150"
                    :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'"
                >
                    <PanelLeftClose
                        class="w-4 h-4 transition-transform duration-300 flex-shrink-0"
                        :class="{ 'rotate-180': !sidebarOpen }"
                        :stroke-width="1.8"
                    />
                    <Transition name="fade-slide">
                        <span v-if="sidebarOpen" class="text-[12px] font-medium"
                            >Collapse</span
                        >
                    </Transition>
                </button>
            </div>
        </aside>

        <!-- ================================================================
             MAIN AREA
        ================================================================ -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header
                class="flex items-center justify-between px-6 bg-white border-slate-200/80 h-[60px] z-20 flex-shrink-0"
            >
                <!-- Mobile: hamburger -->
                <button
                    class="md:hidden p-2 rounded-lg text-slate-400 hover:bg-slate-100"
                    @click="sidebarOpen = !sidebarOpen"
                    title="Toggle menu"
                >
                    <Menu class="w-5 h-5" :stroke-width="1.8" />
                </button>

                <!-- Page title -->
                <h1
                    class="text-[15px] font-semibold text-slate-800 tracking-tight"
                >
                    {{ pageTitle }}
                </h1>

                <!-- Right controls -->
                <div class="flex items-center gap-1.5">
                    <!-- Search -->
                    <button
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200/70 rounded-lg text-slate-400 text-[13px] transition-colors w-44"
                    >
                        <Search
                            class="w-3.5 h-3.5 flex-shrink-0"
                            :stroke-width="2"
                        />
                        <span>Search...</span>
                        <kbd
                            class="ml-auto text-[11px] bg-white border border-slate-200 rounded px-1 text-slate-400"
                            >⌘K</kbd
                        >
                    </button>

                    <!-- Language switcher -->
                    <LanguageSwitcher />

                    <!-- Notifications -->
                    <button
                        class="relative p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
                        title="Notifications"
                    >
                        <Bell class="w-[18px] h-[18px]" :stroke-width="1.8" />
                        <span
                            class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-red-500 rounded-full ring-2 ring-white"
                        />
                    </button>

                    <div class="w-px h-6 bg-slate-200 mx-1" />

                    <!-- User menu -->
                    <div class="flex items-center gap-2.5 pl-1">
                        <!-- Avatar -->
                        <div
                            class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 flex items-center justify-center text-white font-bold text-[12px] shadow-sm shadow-cyan-200 flex-shrink-0"
                        >
                            {{ userInitials }}
                        </div>
                        <!-- Name + role -->
                        <div class="hidden sm:block">
                            <p
                                class="text-[13px] font-semibold text-slate-700 leading-none"
                            >
                                {{ userName }}
                            </p>
                            <p
                                class="text-[11px] text-slate-400 capitalize mt-0.5"
                            >
                                {{ userRole }}
                            </p>
                        </div>
                        <!-- Logout -->
                        <button
                            @click="handleLogout"
                            :disabled="isLoggingOut"
                            class="p-1.5 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-colors disabled:opacity-50 ml-1"
                            title="Logout"
                        >
                            <svg
                                v-if="isLoggingOut"
                                class="animate-spin h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            <LogOut
                                v-else
                                class="h-[15px] w-[15px]"
                                :stroke-width="2"
                            />
                        </button>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-hidden bg-white">
                <div
                    class="h-full overflow-y-auto rounded-tl-xl bg-[#EEF3F9] p-6"
                >
                    <slot />
                </div>
            </main>
        </div>

        <!-- ================================================================
             FLYOUT POPUP  (Teleported to <body> — avoids overflow clipping)
        ================================================================ -->
        <Teleport to="body">
            <Transition name="flyout">
                <div
                    v-if="flyout.visible && !sidebarOpen"
                    :style="flyout.style"
                    class="fixed z-[9999] bg-white rounded-xl border border-slate-200/80 shadow-xl shadow-slate-200/50 py-2 min-w-[160px]"
                    @mouseenter="cancelFlyoutClose"
                    @mouseleave="scheduleFlyoutClose"
                >
                    <!-- Group flyout: title + children -->
                    <template v-if="flyout.item?.children">
                        <p
                            class="px-4 py-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider"
                        >
                            {{ flyout.item.label }}
                        </p>
                        <Link
                            v-for="child in flyout.item.children"
                            :key="child.href"
                            :href="child.href"
                            :class="[
                                'flex items-center gap-2.5 px-4 py-2 text-[13px] transition-colors duration-150',
                                isActive(child.href)
                                    ? 'text-cyan-700 font-medium bg-cyan-50'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800',
                            ]"
                        >
                            <span
                                class="w-1 h-1 rounded-full flex-shrink-0"
                                :class="
                                    isActive(child.href)
                                        ? 'bg-cyan-500'
                                        : 'bg-slate-300'
                                "
                            />
                            {{ child.label }}
                        </Link>
                    </template>

                    <!-- Single item flyout: tooltip only -->
                    <template v-else-if="flyout.item">
                        <div class="flex items-center gap-2 px-4 py-1.5">
                            <span
                                class="text-[13px] font-medium text-slate-700"
                                >{{ flyout.item.label }}</span
                            >
                            <span
                                v-if="flyout.item.badge"
                                class="bg-cyan-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 leading-none"
                                >{{ flyout.item.badge }}</span
                            >
                        </div>
                    </template>
                </div>
            </Transition>
        </Teleport>

        <!-- Mobile overlay -->
        <Transition name="overlay">
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-black/20 backdrop-blur-[2px] z-30 md:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>
    </div>
</template>

<script setup lang="ts">
// ─── Vue core ─────────────────────────────────────────────────
import { ref, reactive, computed, onMounted, defineComponent, h } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { inject } from "vue";

// ─── Internal ─────────────────────────────────────────────────
import { useAuth } from "@/Composables/Auth/useAuth";
import LanguageSwitcher from "@/Components/LanguageSwitcher.vue";
import type { confirm as ConfirmType } from "@/Plugins/confirm";

// ─── Lucide icons ─────────────────────────────────────────────
import {
    Building2,
    LayoutDashboard,
    Inbox,
    CalendarDays,
    BedDouble,
    SprayCan,
    Package,
    Banknote,
    Star,
    Settings2,
    ChevronDown,
    PanelLeftClose,
    Search,
    Bell,
    Menu,
    LogOut,
    type LucideIcon,
} from "lucide-vue-next";

// ════════════════════════════════════════════════════════════════
// TYPES
// ════════════════════════════════════════════════════════════════

interface NavChild {
    label: string;
    href: string;
}

interface NavItem {
    label: string;
    icon: LucideIcon;
    href?: string; // undefined for group items
    children?: NavChild[];
    badge?: number;
}

interface FlyoutState {
    visible: boolean;
    item: NavItem | null;
    style: Record<string, string>;
}

// ════════════════════════════════════════════════════════════════
// NAV ITEMS  — edit this array to add/remove menu entries
// ════════════════════════════════════════════════════════════════

const navItems: NavItem[] = [
    { label: "Dashboard", href: "/dashboard", icon: LayoutDashboard },
    { label: "Inbox", href: "/inbox", icon: Inbox },
    { label: "Calendar", href: "/calendar", icon: CalendarDays },
    {
        label: "Rooms",
        icon: BedDouble,
        children: [
            { label: "All Rooms", href: "/rooms" },
            { label: "Room Types", href: "/rooms/types" },
        ],
    },
    { label: "Housekeeping", href: "/housekeeping", icon: SprayCan },
    { label: "Inventory", href: "/inventory", icon: Package },
    {
        label: "Finance",
        icon: Banknote,
        children: [
            { label: "Overview", href: "/finance" },
            { label: "Invoices", href: "/finance/invoices" },
        ],
    },
    { label: "Reviews", href: "/reviews", icon: Star, badge: 5 },
    { label: "Settings", href: "/settings", icon: Settings2 },
];

// ════════════════════════════════════════════════════════════════
// INLINE SUB-COMPONENTS
// (kept here so this file is self-contained & easy to find)
// ════════════════════════════════════════════════════════════════

/**
 * NavGroupItem
 * Renders a collapsible accordion (expanded sidebar) or
 * an icon-only button that triggers flyout (collapsed sidebar).
 */
const NavGroupItem = defineComponent({
    name: "NavGroupItem",
    props: {
        item: { type: Object as () => NavItem, required: true },
        isExpanded: { type: Boolean, required: true },
        isOpen: { type: Boolean, required: true },
        isActiveFn: {
            type: Function as () => (href: string) => boolean,
            required: true,
        },
    },
    emits: ["toggle", "flyout-enter", "flyout-leave"],
    setup(props, { emit }) {
        const groupActive = computed(
            () =>
                props.item.children?.some((c) => props.isActiveFn(c.href)) ??
                false,
        );

        return () => {
            const { item, isExpanded, isOpen, isActiveFn } = props;

            return h("div", { class: "relative" }, [
                // ── Expanded: clickable row with chevron ──
                isExpanded
                    ? h(
                          "button",
                          {
                              class: [
                                  "w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-150",
                                  groupActive.value
                                      ? "bg-cyan-50 text-cyan-700"
                                      : "text-slate-500 hover:bg-slate-50 hover:text-slate-700",
                              ],
                              onClick: () => emit("toggle"),
                          },
                          [
                              h(item.icon as any, {
                                  class: "w-[18px] h-[18px] flex-shrink-0",
                                  strokeWidth: groupActive.value ? 2.2 : 1.8,
                              }),
                              h(
                                  "span",
                                  { class: "flex-1 text-left" },
                                  item.label,
                              ),
                              h(ChevronDown, {
                                  class: [
                                      "w-3.5 h-3.5 transition-transform duration-200 text-slate-400",
                                      isOpen ? "rotate-180" : "",
                                  ],
                                  strokeWidth: 2,
                              }),
                          ],
                      )
                    : // ── Collapsed: icon only, flyout on hover ──
                      h(
                          "button",
                          {
                              class: [
                                  "w-full flex items-center justify-center py-2.5 rounded-xl transition-all duration-150",
                                  groupActive.value
                                      ? "bg-cyan-50 text-cyan-600"
                                      : "text-slate-400 hover:bg-slate-50 hover:text-slate-700",
                              ],
                              onMouseenter: (e: MouseEvent) =>
                                  emit("flyout-enter", e.currentTarget),
                              onMouseleave: () => emit("flyout-leave"),
                          },
                          [
                              h(item.icon as any, {
                                  class: "w-[18px] h-[18px]",
                                  strokeWidth: groupActive.value ? 2.2 : 1.8,
                              }),
                          ],
                      ),

                // ── Accordion children (expanded only) ──
                isExpanded && isOpen
                    ? h(
                          "div",
                          {
                              class: "mt-0.5 mb-1 ml-3 pl-3.5 border-l-2 border-slate-100 space-y-0.5",
                          },
                          item.children!.map((child) =>
                              h(
                                  Link,
                                  {
                                      key: child.href,
                                      href: child.href,
                                      class: [
                                          "flex items-center gap-2 px-3 py-2 rounded-lg text-[12.5px] transition-all duration-150",
                                          isActiveFn(child.href)
                                              ? "text-cyan-700 font-semibold bg-cyan-50/60"
                                              : "text-slate-400 hover:text-slate-700 hover:bg-slate-50",
                                      ],
                                  },
                                  () => [
                                      h("span", {
                                          class: [
                                              "w-1 h-1 rounded-full flex-shrink-0",
                                              isActiveFn(child.href)
                                                  ? "bg-cyan-500"
                                                  : "bg-slate-300",
                                          ],
                                      }),
                                      child.label,
                                  ],
                              ),
                          ),
                      )
                    : null,
            ]);
        };
    },
});

/**
 * NavSingleItem
 * Renders a simple link. Shows tooltip flyout when sidebar is collapsed.
 */
const NavSingleItem = defineComponent({
    name: "NavSingleItem",
    props: {
        item: { type: Object as () => NavItem, required: true },
        isExpanded: { type: Boolean, required: true },
        isActiveFn: {
            type: Function as () => (href: string) => boolean,
            required: true,
        },
    },
    emits: ["flyout-enter", "flyout-leave"],
    setup(props, { emit }) {
        return () => {
            const { item, isExpanded, isActiveFn } = props;
            const active = isActiveFn(item.href ?? "");

            return h(
                "div",
                {
                    class: "relative",
                    onMouseenter: !isExpanded
                        ? (e: MouseEvent) =>
                              emit("flyout-enter", e.currentTarget)
                        : undefined,
                    onMouseleave: !isExpanded
                        ? () => emit("flyout-leave")
                        : undefined,
                },
                [
                    h(
                        Link,
                        {
                            href: item.href ?? "",
                            class: [
                                "flex items-center gap-3 rounded-xl text-[13px] font-medium transition-all duration-150",
                                isExpanded
                                    ? "px-3 py-2.5"
                                    : "justify-center py-2.5",
                                active
                                    ? "bg-cyan-50 text-cyan-700"
                                    : "text-slate-500 hover:bg-slate-50 hover:text-slate-700",
                            ],
                        },
                        () => [
                            h(item.icon as any, {
                                class: "w-[18px] h-[18px] flex-shrink-0",
                                strokeWidth: active ? 2.2 : 1.8,
                            }),
                            isExpanded
                                ? h(
                                      "span",
                                      { class: "whitespace-nowrap flex-1" },
                                      item.label,
                                  )
                                : null,
                            // Badge (expanded)
                            item.badge && isExpanded
                                ? h(
                                      "span",
                                      {
                                          class: "ml-auto bg-cyan-500 text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 min-w-[18px] text-center leading-none",
                                      },
                                      String(item.badge),
                                  )
                                : null,
                            // Badge dot (collapsed)
                            item.badge && !isExpanded
                                ? h("span", {
                                      class: "absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-cyan-500 rounded-full",
                                  })
                                : null,
                        ],
                    ),
                ],
            );
        };
    },
});

// ════════════════════════════════════════════════════════════════
// LAYOUT STATE
// ════════════════════════════════════════════════════════════════

const sidebarOpen = ref(true);
const openGroups = ref<string[]>([]);

// Flyout state — position is calculated via getBoundingClientRect()
const flyout = reactive<FlyoutState>({
    visible: false,
    item: null,
    style: {},
});

let flyoutCloseTimer: ReturnType<typeof setTimeout> | null = null;
const FLYOUT_CLOSE_DELAY_MS = 80;

// ════════════════════════════════════════════════════════════════
// AUTH / PAGE
// ════════════════════════════════════════════════════════════════

const confirm = inject("confirm") as typeof ConfirmType;
const { logout, userName, userRole, loading, initializeFromInertia } =
    useAuth();
const page = usePage();

onMounted(() => {
    const authUser = (page.props as any).auth?.user ?? null;
    if (authUser) initializeFromInertia(authUser);
});

const isLoggingOut = computed(() => loading.value);

const userInitials = computed(
    () =>
        userName.value
            ?.split(" ")
            .map((n: string) => n[0])
            .slice(0, 2)
            .join("")
            .toUpperCase() ?? "U",
);

const pageTitle = computed(() => {
    const path = page.url;
    const found = navItems
        .flatMap((i) =>
            i.children
                ? i.children.map((c) => ({ ...c }))
                : [{ label: i.label, href: i.href ?? "" }],
        )
        .find((i) => i.href && path.startsWith(i.href));
    return found?.label ?? "Dashboard";
});

// ════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════

/** Returns true when the current URL starts with href */
function isActive(href: string): boolean {
    return page.url.startsWith(href);
}

/** Toggle accordion group open/closed */
function toggleGroup(label: string): void {
    const idx = openGroups.value.indexOf(label);
    if (idx >= 0) openGroups.value.splice(idx, 1);
    else openGroups.value.push(label);
}

// ════════════════════════════════════════════════════════════════
// FLYOUT LOGIC
// Teleport renders the popup at <body> level to avoid any
// overflow clipping from the sidebar.  Position is calculated
// from the trigger element's bounding rect.
// ════════════════════════════════════════════════════════════════

/**
 * Called when the mouse enters a collapsed nav item.
 * @param item    - The NavItem to show in the flyout
 * @param trigger - The DOM element that triggered the hover
 */
function onFlyoutEnter(item: NavItem, trigger: EventTarget | null): void {
    if (flyoutCloseTimer) {
        clearTimeout(flyoutCloseTimer);
        flyoutCloseTimer = null;
    }

    const el = trigger as HTMLElement | null;
    const rect = el?.getBoundingClientRect();

    flyout.item = item;
    flyout.visible = true;
    flyout.style = {
        top: rect ? `${rect.top}px` : "0px",
        left: rect ? `${rect.right + 8}px` : "0px", // 8px gap from sidebar edge
    };
}

/** Called when the mouse leaves a nav item */
function onFlyoutLeave(): void {
    scheduleFlyoutClose();
}

/** Keep flyout open while mouse is inside the popup panel */
function cancelFlyoutClose(): void {
    if (flyoutCloseTimer) {
        clearTimeout(flyoutCloseTimer);
        flyoutCloseTimer = null;
    }
}

/** Hide the flyout after a short delay (allows mouse to travel into panel) */
function scheduleFlyoutClose(): void {
    flyoutCloseTimer = setTimeout(() => {
        flyout.visible = false;
        flyout.item = null;
    }, FLYOUT_CLOSE_DELAY_MS);
}

// ════════════════════════════════════════════════════════════════
// LOGOUT
// ════════════════════════════════════════════════════════════════

async function handleLogout(): Promise<void> {
    const confirmed = await confirm.show({
        title       : 'Logout?',
        message     : 'Are you sure you want to logout?',
        confirmText : 'Yes',
        cancelText  : 'No',
        variant     : 'danger',
        icon        : false,
    });

    if (!confirmed) return;

    try {
        await store.logout()        // ← store directly, toast skip করুন
        router.visit('/login')      // ← আগে navigate
    } catch {
        router.visit('/login')
    }
}
</script>

<style scoped>
/* ── Label slide-in / slide-out ───────────────────────────────── */
.fade-slide-enter-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.fade-slide-leave-active {
    transition: opacity 0.15s ease;
    position: absolute;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(-6px);
}
.fade-slide-leave-to {
    opacity: 0;
}

/* ── Flyout popup ─────────────────────────────────────────────── */
.flyout-enter-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}
.flyout-leave-active {
    transition:
        opacity 0.1s ease,
        transform 0.1s ease;
}
.flyout-enter-from {
    opacity: 0;
    transform: translateX(-6px);
}
.flyout-leave-to {
    opacity: 0;
    transform: translateX(-4px);
}

/* ── Mobile overlay ───────────────────────────────────────────── */
.overlay-enter-active,
.overlay-leave-active {
    transition: opacity 0.2s ease;
}
.overlay-enter-from,
.overlay-leave-to {
    opacity: 0;
}
</style>
