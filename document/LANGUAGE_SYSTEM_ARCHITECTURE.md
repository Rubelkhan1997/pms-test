# 🌍 Language System (i18n) Architecture

## Overview

This document describes the complete internationalization (i18n) architecture for the PMS application, covering both Laravel backend and Vue 3 frontend implementation.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Laravel Backend](#laravel-backend)
3. [Vue Frontend](#vue-frontend)
4. [State Management](#state-management)
5. [Implementation Guide](#implementation-guide)
6. [Best Practices](#best-practices)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        User Interface Layer                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │ LanguageSwitcher│  │  Vue Components │  │  Translations   │  │
│  │   Component     │  │  (useI18n hook) │  │   Display       │  │
│  └────────┬────────┘  └────────┬────────┘  └─────────────────┘  │
│           │                    │                                  │
│           ▼                    ▼                                  │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │              Pinia Store (languageStore)                     │ │
│  │  - currentLanguage: 'en' | 'bn' | 'ar'                       │ │
│  │  - availableLanguages: [...]                                 │ │
│  │  - setLanguage(code: string)                                 │ │
│  └─────────────────────────┬───────────────────────────────────┘ │
│                            │                                      │
└────────────────────────────┼──────────────────────────────────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
                ▼                         ▼
┌───────────────────────────┐   ┌───────────────────────────┐
│   Vue Frontend (Pinia)    │   │   Laravel Backend         │
│  - Locales/en.ts          │   │  - lang/en/messages.php   │
│  - Locales/bn.ts          │   │  - lang/bn/messages.php   │
│  - Locales/ar.ts          │   │  - lang/ar/messages.php   │
│  - useI18n composable     │   │  - App::setLocale()       │
│  - t('key.path')          │   │  - __('translation.key')  │
└───────────────────────────┘   └───────────────────────────┘
```

---

## Laravel Backend

### File Structure

```
lang/
├── en/
│   ├── messages.php        # General UI messages
│   ├── validation.php      # Validation error messages
│   ├── auth.php            # Authentication texts
│   ├── navigation.php      # Menu and navigation
│   └── reservations.php    # Reservation specific texts
├── bn/
│   ├── messages.php
│   ├── validation.php
│   ├── auth.php
│   ├── navigation.php
│   └── reservations.php
└── ar/
    ├── messages.php
    ├── validation.php
    ├── auth.php
    ├── navigation.php
    └── reservations.php
```

### Translation Files

#### `lang/en/messages.php`

```php
<?php

return [
    // Common
    'welcome' => 'Welcome',
    'loading' => 'Loading...',
    'no_data' => 'No data available',
    'error_occurred' => 'An error occurred',
    
    // Actions
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'create' => 'Create',
    'update' => 'Update',
    'confirm' => 'Confirm',
    
    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'cancelled' => 'Cancelled',
    
    // Success Messages
    'created_success' => 'Created successfully',
    'updated_success' => 'Updated successfully',
    'deleted_success' => 'Deleted successfully',
];
```

#### `lang/bn/messages.php`

```php
<?php

return [
    // Common
    'welcome' => 'স্বাগতম',
    'loading' => 'লোড হচ্ছে...',
    'no_data' => 'কোনো তথ্য নেই',
    'error_occurred' => 'একটি ত্রুটি ঘটেছে',
    
    // Actions
    'save' => 'সংরক্ষণ',
    'cancel' => 'বাতিল',
    'delete' => 'মুছুন',
    'edit' => 'সম্পাদনা',
    'create' => 'তৈরি করুন',
    'update' => 'আপডেট',
    'confirm' => 'নিশ্চিত করুন',
    
    // Status
    'active' => 'সক্রিয়',
    'inactive' => 'নিষ্ক্রিয়',
    'pending' => 'অমীমাংসিত',
    'confirmed' => 'নিশ্চিত',
    'cancelled' => 'বাতিল',
    
    // Success Messages
    'created_success' => 'সফলভাবে তৈরি হয়েছে',
    'updated_success' => 'সফলভাবে আপডেট হয়েছে',
    'deleted_success' => 'সফলভাবে মুছে ফেলা হয়েছে',
];
```

### Controller Usage

```php
<?php

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class ReservationController extends Controller
{
    public function index(): JsonResponse
    {
        // Get current locale (from session, cookie, or user preference)
        $locale = session('locale', config('app.locale'));
        App::setLocale($locale);
        
        return response()->json([
            'status' => 1,
            'data' => [
                'message' => __('messages.welcome'),
                'reservations' => $reservations
            ]
        ]);
    }
    
    public function store(Request $request): JsonResponse
    {
        // Validation with translated messages
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
        ], [
            'guest_name.required' => __('validation.required', ['attribute' => __('reservations.guest_name')]),
        ]);
        
        // Success response with translation
        return response()->json([
            'status' => 1,
            'message' => __('messages.created_success'),
            'data' => $reservation
        ]);
    }
}
```

### Middleware for Language Detection

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Priority: Header > Session > Cookie > Config Default
        $locale = $request->header('X-Locale')
            ?? session('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale');
        
        // Validate locale against supported languages
        $supportedLocales = ['en', 'bn', 'ar'];
        if (in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        }
        
        return $next($request);
    }
}
```

---

## Vue Frontend

### File Structure

```
resources/js/
├── app.ts                      # Main app entry (i18n plugin registration)
├── Locales/                    # Translation files
│   ├── en.ts
│   ├── bn.ts
│   ├── ar.ts
│   └── index.ts               # Locale loader
├── Stores/
│   ├── languageStore.ts       # Language state management
│   └── index.ts
├── Composables/
│   ├── useI18n.ts             # Translation composable
│   └── index.ts
└── Components/
    └── LanguageSwitcher.vue   # UI component
```

### Locales Files

#### `resources/js/Locales/en.ts`

```typescript
export default {
  // Common
  welcome: 'Welcome',
  loading: 'Loading...',
  no_data: 'No data available',
  error_occurred: 'An error occurred',
  
  // Navigation
  navigation: {
    dashboard: 'Dashboard',
    reservations: 'Reservations',
    rooms: 'Rooms',
    guests: 'Guests',
    reports: 'Reports',
    settings: 'Settings',
  },
  
  // Actions
  actions: {
    save: 'Save',
    cancel: 'Cancel',
    delete: 'Delete',
    edit: 'Edit',
    create: 'Create',
    update: 'Update',
    confirm: 'Confirm',
    search: 'Search',
    filter: 'Filter',
    export: 'Export',
  },
  
  // Auth
  auth: {
    login: 'Login',
    logout: 'Logout',
    register: 'Register',
    email: 'Email',
    password: 'Password',
    remember_me: 'Remember me',
    forgot_password: 'Forgot password?',
  },
  
  // Reservations
  reservations: {
    title: 'Reservations',
    new_reservation: 'New Reservation',
    check_in: 'Check-in',
    check_out: 'Check-out',
    guest_name: 'Guest Name',
    room_number: 'Room Number',
    status: 'Status',
    dates: 'Dates',
  },
  
  // Status
  status: {
    pending: 'Pending',
    confirmed: 'Confirmed',
    checked_in: 'Checked In',
    checked_out: 'Checked Out',
    cancelled: 'Cancelled',
  },
  
  // Messages
  messages: {
    confirm_delete: 'Are you sure you want to delete this item?',
    delete_success: 'Deleted successfully',
    create_success: 'Created successfully',
    update_success: 'Updated successfully',
    error_occurred: 'An error occurred',
  },
};
```

#### `resources/js/Locales/bn.ts`

```typescript
export default {
  // Common
  welcome: 'স্বাগতম',
  loading: 'লোড হচ্ছে...',
  no_data: 'কোনো তথ্য নেই',
  error_occurred: 'একটি ত্রুটি ঘটেছে',
  
  // Navigation
  navigation: {
    dashboard: 'ড্যাশবোর্ড',
    reservations: 'রিজার্ভেশন',
    rooms: 'রুম',
    guests: 'অতিথি',
    reports: 'রিপোর্ট',
    settings: 'সেটিংস',
  },
  
  // Actions
  actions: {
    save: 'সংরক্ষণ',
    cancel: 'বাতিল',
    delete: 'মুছুন',
    edit: 'সম্পাদনা',
    create: 'তৈরি করুন',
    update: 'আপডেট',
    confirm: 'নিশ্চিত করুন',
    search: 'অনুসন্ধান',
    filter: 'ফিল্টার',
    export: 'রপ্তানি',
  },
  
  // Auth
  auth: {
    login: 'লগইন',
    logout: 'লগআউট',
    register: 'রেজিস্ট্রেশন',
    email: 'ইমেইল',
    password: 'পাসওয়ার্ড',
    remember_me: 'মনে রাখুন',
    forgot_password: 'পাসওয়ার্ড ভুলে গেছেন?',
  },
  
  // Reservations
  reservations: {
    title: 'রিজার্ভেশন',
    new_reservation: 'নতুন রিজার্ভেশন',
    check_in: 'চেক-ইন',
    check_out: 'চেক-আউট',
    guest_name: 'অতিথির নাম',
    room_number: 'রুম নম্বর',
    status: 'অবস্থা',
    dates: 'তারিখ',
  },
  
  // Status
  status: {
    pending: 'অমীমাংসিত',
    confirmed: 'নিশ্চিত',
    checked_in: 'চেক-ইন করা',
    checked_out: 'চেক-আউট করা',
    cancelled: 'বাতিল',
  },
  
  // Messages
  messages: {
    confirm_delete: 'আপনি কি এই আইটেমটি মুছে ফেলতে চান?',
    delete_success: 'সফলভাবে মুছে ফেলা হয়েছে',
    create_success: 'সফলভাবে তৈরি হয়েছে',
    update_success: 'সফলভাবে আপডেট হয়েছে',
    error_occurred: 'একটি ত্রুটি ঘটেছে',
  },
};
```

#### `resources/js/Locales/index.ts`

```typescript
import en from './en';
import bn from './bn';
import ar from './ar';

export const locales = {
  en,
  bn,
  ar,
} as const;

export type LocaleCode = keyof typeof locales;
export type Locale = typeof locales[LocaleCode];

export type TranslationKey = keyof typeof en;

export default locales;
```

---

## State Management

### Language Store

#### `resources/js/Stores/languageStore.ts`

```typescript
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '@/Services/apiClient';
import { toast } from '@/Plugins';

export interface Language {
  code: string;
  name: string;
  nativeName: string;
  flag: string;
  direction: 'ltr' | 'rtl';
}

export interface LanguageState {
  currentLanguage: string;
  availableLanguages: Language[];
  loading: boolean;
}

const createInitialState = (): LanguageState => ({
  currentLanguage: 'en',
  availableLanguages: [
    {
      code: 'en',
      name: 'English',
      nativeName: 'English',
      flag: '🇬🇧',
      direction: 'ltr',
    },
    {
      code: 'bn',
      name: 'Bangla',
      nativeName: 'বাংলা',
      flag: '🇧🇩',
      direction: 'ltr',
    },
    {
      code: 'ar',
      name: 'Arabic',
      nativeName: 'العربية',
      flag: '🇸🇦',
      direction: 'rtl',
    },
  ],
  loading: false,
});

export const useLanguageStore = defineStore('language', () => {
  // ─────────────────────────────────────────────────────────
  // State
  // ─────────────────────────────────────────────────────────
  const currentLanguage = ref<string>('en');
  const availableLanguages = ref<Language[]>(createInitialState().availableLanguages);
  const loading = ref<boolean>(false);

  // ─────────────────────────────────────────────────────────
  // Getters
  // ─────────────────────────────────────────────────────────
  const currentLanguageObj = computed(() => {
    return availableLanguages.value.find(
      (lang) => lang.code === currentLanguage.value
    );
  });

  const isRTL = computed(() => {
    return currentLanguageObj.value?.direction === 'rtl';
  });

  // ─────────────────────────────────────────────────────────
  // Actions
  // ─────────────────────────────────────────────────────────
  
  /**
   * Initialize language from localStorage or default
   */
  function initialize(): void {
    const savedLanguage = localStorage.getItem('language');
    if (savedLanguage && availableLanguages.value.some((l) => l.code === savedLanguage)) {
      currentLanguage.value = savedLanguage;
    }
  }

  /**
   * Set current language
   */
  async function setLanguage(code: string): Promise<void> {
    const language = availableLanguages.value.find((l) => l.code === code);
    if (!language) {
      toast.error('Language not supported');
      return;
    }

    loading.value = true;

    try {
      currentLanguage.value = code;
      localStorage.setItem('language', code);

      // Update backend locale via API
      await updateBackendLocale(code);

      toast.success(`Language changed to ${language.name}`);
    } catch (error) {
      toast.error('Failed to change language');
      console.error('Language change error:', error);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Update backend locale
   */
  async function updateBackendLocale(code: string): Promise<void> {
    try {
      await apiClient.post('/user/locale', { locale: code });
    } catch (error) {
      console.error('Backend locale update failed:', error);
      // Don't throw - frontend language change should succeed even if backend fails
    }
  }

  /**
   * Reset store to initial state
   */
  function $reset(): void {
    const initialState = createInitialState();
    currentLanguage.value = initialState.currentLanguage;
    loading.value = initialState.loading;
  }

  // ─────────────────────────────────────────────────────────
  // Public API
  // ─────────────────────────────────────────────────────────
  return {
    // State
    currentLanguage,
    availableLanguages,
    loading,

    // Getters
    currentLanguageObj,
    isRTL,

    // Actions
    initialize,
    setLanguage,
    $reset,
  };
});
```

---

## Composable

### useI18n

#### `resources/js/Composables/useI18n.ts`

```typescript
import { computed } from 'vue';
import { useLanguageStore } from '@/Stores/languageStore';
import { locales, LocaleCode } from '@/Locales';

/**
 * Composable for internationalization
 * 
 * @example
 * ```typescript
 * const { t, currentLang } = useI18n();
 * 
 * // In template
 * <h1>{{ t('welcome') }}</h1>
 * <p>{{ t('navigation.dashboard') }}</p>
 * ```
 */
export function useI18n() {
  const languageStore = useLanguageStore();
  
  const currentLang = computed(() => languageStore.currentLanguage as LocaleCode);

  /**
   * Translate a key to current language
   * Supports nested keys with dot notation
   * 
   * @param key - Translation key (e.g., 'navigation.dashboard')
   * @param params - Optional parameters to replace in translation
   * @returns Translated string or key if not found
   */
  function t(key: string, params: Record<string, string> = {}): string {
    const keys = key.split('.');
    let value: any = locales[currentLang.value];

    // Traverse nested object
    for (const k of keys) {
      value = value?.[k];
    }

    // Fallback to English if not found
    if (value === undefined) {
      value = getNestedValue(locales.en, keys);
    }

    // Return key if still not found
    if (value === undefined) {
      console.warn(`Translation missing for key: "${key}" in language: "${currentLang.value}"`);
      return key;
    }

    // Replace parameters
    if (typeof value === 'string' && Object.keys(params).length > 0) {
      Object.entries(params).forEach(([paramKey, paramValue]) => {
        value = value.replace(`:${paramKey}`, paramValue);
      });
    }

    return value;
  }

  /**
   * Helper to get nested value from object
   */
  function getNestedValue(obj: any, keys: string[]): any {
    return keys.reduce((acc, key) => acc?.[key], obj);
  }

  /**
   * Check if translation exists for a key
   */
  function has(key: string): boolean {
    const keys = key.split('.');
    let value: any = locales[currentLang.value];

    for (const k of keys) {
      if (value?.[k] === undefined) {
        return false;
      }
      value = value[k];
    }

    return true;
  }

  return {
    t,
    has,
    currentLang,
  };
}
```

---

## UI Component

### Language Switcher

#### `resources/js/Components/LanguageSwitcher.vue`

```vue
<template>
  <div class="relative" v-if="languages.length > 1">
    <!-- Language Button -->
    <button
      @click="isOpen = !isOpen"
      class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 hover:border-cyan-500 hover:bg-cyan-50 transition"
    >
      <span class="text-lg">{{ currentLanguage.flag }}</span>
      <span class="text-sm font-medium text-slate-700">{{ currentLanguage.name }}</span>
      <svg
        class="w-4 h-4 text-slate-500"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M19 9l-7 7-7-7"
        />
      </svg>
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-50"
      v-click-outside="() => (isOpen = false)"
    >
      <div class="py-2">
        <button
          v-for="lang in languages"
          :key="lang.code"
          @click="selectLanguage(lang)"
          class="w-full px-4 py-2 text-left hover:bg-cyan-50 transition flex items-center gap-3"
          :class="{ 'bg-cyan-50 text-cyan-700': lang.code === currentLanguage.code }"
        >
          <span class="text-xl">{{ lang.flag }}</span>
          <div>
            <div class="font-medium text-slate-800">{{ lang.name }}</div>
            <div class="text-xs text-slate-500">{{ lang.nativeName }}</div>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue';
  import { useLanguageStore } from '@/Stores/languageStore';
  import type { Language } from '@/Stores/languageStore';

  // ─────────────────────────────────────────────────────────
  // Store
  // ─────────────────────────────────────────────────────────
  const languageStore = useLanguageStore();

  // ─────────────────────────────────────────────────────────
  // State
  // ─────────────────────────────────────────────────────────
  const isOpen = ref(false);

  // ─────────────────────────────────────────────────────────
  // Computed
  // ─────────────────────────────────────────────────────────
  const currentLanguage = computed(() => languageStore.currentLanguageObj);
  const languages = computed(() => languageStore.availableLanguages);

  // ─────────────────────────────────────────────────────────
  // Methods
  // ─────────────────────────────────────────────────────────
  async function selectLanguage(lang: Language) {
    await languageStore.setLanguage(lang.code);
    isOpen.value = false;
  }
</script>
```

---

## Implementation Guide

### Step 1: Install Dependencies (Optional)

If using vue-i18n (optional - custom composable is recommended):

```bash
npm install vue-i18n@9
```

### Step 2: Create Directory Structure

```bash
# Backend
mkdir -p lang/en lang/bn lang/ar

# Frontend
mkdir -p resources/js/Locales
mkdir -p resources/js/Stores
mkdir -p resources/js/Composables
mkdir -p resources/js/Components
```

### Step 3: Update `app.ts`

```typescript
// resources/js/app.ts
import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { toast, confirm } from '@/Plugins';
import AppLayout from '@/Layouts/AppLayout.vue';

// Import language store
import { useLanguageStore } from '@/Stores/languageStore';

createInertiaApp({
    title: (title) => title ? `${title} - PMS` : 'PMS',

    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const pageModule = pages[`./Pages/${name}.vue`];

        if (!pageModule) {
            throw new Error(`Page "${name}" not found`);
        }

        const page = pageModule.default || pageModule;

        if (page.layout === undefined) {
            page.layout = AppLayout;
        }

        return page;
    },

    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.component('Head', Head);
        app.component('Link', Link);

        app.use(plugin);
        app.use(createPinia());
        app.use(toast);
        app.use(confirm);

        // Initialize language from localStorage
        const languageStore = useLanguageStore();
        languageStore.initialize();

        // Provide language store globally
        app.provide('languageStore', languageStore);

        app.mount(el);
    },

    progress: {
        color: '#3b82f6',
        showSpinner: true
    }
});
```

### Step 4: Create Laravel API Route

```php
// routes/api.php
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/user/locale', [UserController::class, 'setLocale']);
});
```

```php
// app/Http/Controllers/UserController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function setLocale(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|in:en,bn,ar',
        ]);

        $locale = $request->input('locale');
        
        // Save to session
        session(['locale' => $locale]);
        
        // Save to user model (if authenticated)
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Locale updated successfully',
        ]);
    }
}
```

### Step 5: Usage in Vue Components

```vue
<template>
  <Head :title="t('navigation.dashboard')" />
  
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-semibold text-slate-800">
          {{ t('navigation.dashboard') }}
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          {{ t('messages.welcome') }}, {{ userName }}!
        </p>
      </div>
      
      <!-- Language Switcher -->
      <LanguageSwitcher />
    </div>

    <!-- Content -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-lg font-semibold text-slate-800 mb-4">
        {{ t('reservations.title') }}
      </h2>
      
      <button
        @click="createReservation"
        class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition"
      >
        {{ t('reservations.new_reservation') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useI18n } from '@/Composables/useI18n';
  import { useAuth } from '@/Composables/Auth/useAuth';
  import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

  const { t } = useI18n();
  const { userName } = useAuth();

  function createReservation() {
    // ...
  }
</script>
```

---

## Best Practices

### 1. Translation Keys

- Use **dot notation** for nested keys: `navigation.dashboard`
- Keep keys **consistent** across all languages
- Use **descriptive** keys: `reservations.guest_name` not `field1`
- Group by **feature/module**: `auth.login`, `auth.logout`

### 2. Parameters in Translations

```typescript
// Locales/en.ts
export default {
  messages: {
    welcome_user: 'Welcome, :name!',
    items_count: ':count items',
  },
};

// Component
const { t } = useI18n();
t('messages.welcome_user', { name: 'John' }); // "Welcome, John!"
t('messages.items_count', { count: 5 }); // "5 items"
```

### 3. RTL Support

```vue
<template>
  <div :dir="isRTL ? 'rtl' : 'ltr'" :class="{ 'text-right': isRTL }">
    <!-- Content -->
  </div>
</template>

<script setup>
import { useLanguageStore } from '@/Stores/languageStore';
const languageStore = useLanguageStore();
const isRTL = computed(() => languageStore.isRTL);
</script>
```

### 4. Lazy Loading Translations

For large applications, load translations on demand:

```typescript
// Locales/index.ts
export async function loadLocale(code: string) {
  return await import(`./${code}.ts`);
}
```

### 5. Missing Translation Warning

```typescript
// useI18n.ts
if (value === undefined) {
  console.warn(`Translation missing: "${key}" in "${currentLang.value}"`);
  return key; // Fallback to key
}
```

### 6. Testing

```typescript
// tests/Js/Composables/useI18n.test.ts
import { describe, it, expect } from 'vitest';
import { useI18n } from '@/Composables/useI18n';

describe('useI18n', () => {
  it('should return translation for key', () => {
    const { t } = useI18n();
    expect(t('welcome')).toBe('Welcome');
  });

  it('should handle nested keys', () => {
    const { t } = useI18n();
    expect(t('navigation.dashboard')).toBe('Dashboard');
  });

  it('should replace parameters', () => {
    const { t } = useI18n();
    expect(t('messages.welcome_user', { name: 'John' }))
      .toBe('Welcome, John!');
  });
});
```

---

## Related Documentation

- [Currency System Architecture](./CURRENCY_SYSTEM_ARCHITECTURE.md)
- [Vue Composables Guide](./VUE_COMPOSABLES.md)
- [Pinia Stores Guide](./PINIA_STORES.md)

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-04-02 | Initial draft |

---

**Author:** PMS Development Team  
**Last Updated:** 2026-04-02
