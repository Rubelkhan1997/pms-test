<!-- components/AppSearchBox.vue -->
<template>
  <div class="hidden sm:block relative" ref="searchWrapperRef">
    <!-- Trigger Button -->
    <button
      @click="toggleSearch"
      class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200/70 rounded-md text-slate-400 text-[13px] transition-colors w-56"
    >
      <Search class="w-3.5 h-3.5 shrink-0" :stroke-width="2" />
      <span>Search...</span>
      <kbd class="ml-auto text-[11px] bg-white border border-slate-200 rounded px-1 text-slate-400">
        ⌘K
      </kbd>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0 scale-95 -translate-y-1"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-95 -translate-y-1"
    >
      <div
        v-if="searchOpen"
        class="absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-xl shadow-slate-200/60 border border-slate-200/80 overflow-hidden z-50"
      >
        <!-- Input row -->
        <div class="flex items-center gap-2.5 px-3 py-2.5 border-b border-slate-100">
          <Search class="w-3.5 h-3.5 text-slate-400 shrink-0" :stroke-width="2" />
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="flex-1 text-[13px] text-slate-700 placeholder:text-slate-400 outline-none bg-transparent"
            @keydown.esc="searchOpen = false"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="text-slate-300 hover:text-slate-500 transition-colors"
          >
            <X class="w-3.5 h-3.5" />
          </button>
        </div>

        <!-- Empty hint -->
        <div v-if="!searchQuery" class="px-4 py-6 text-center">
          <p class="text-[12px] text-slate-400">Type to search...</p>
        </div>

        <!-- Results -->
        <div v-else class="max-h-64 overflow-y-auto">
          <div v-if="searchResults.length === 0" class="px-4 py-6 text-center">
            <p class="text-[12px] text-slate-400">
              No results for "<span class="text-slate-600">{{ searchQuery }}</span>"
            </p>
          </div>

          <button
            v-for="result in searchResults"
            :key="result.id"
            class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 transition-colors text-left"
            @click="selectResult(result)"
          >
            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
              <component :is="result.icon" class="w-3.5 h-3.5 text-slate-500" :stroke-width="1.8" />
            </div>
            <div>
              <p class="text-[13px] font-medium text-slate-700">{{ result.title }}</p>
              <p class="text-[11px] text-slate-400">{{ result.subtitle }}</p>
            </div>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script lang="ts">
import { LayoutDashboard, FolderOpen, Users, FileText, Settings } from 'lucide-vue-next'

export interface SearchItem {
  id: number
  title: string
  subtitle: string
  icon: any
  route?: string
}

export const defaultItems: SearchItem[] = [
  { id: 1, title: 'Dashboard', subtitle: 'Overview page', icon: LayoutDashboard, route: '/dashboard' },
  { id: 2, title: 'Projects', subtitle: 'Manage projects', icon: FolderOpen, route: '/projects' },
  { id: 3, title: 'Users', subtitle: 'Manage users', icon: Users, route: '/users' },
  { id: 4, title: 'Reports', subtitle: 'View reports', icon: FileText, route: '/reports' },
  { id: 5, title: 'Settings', subtitle: 'App settings', icon: Settings, route: '/settings' },
]
</script>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { Search, X } from 'lucide-vue-next'

const props = withDefaults(defineProps<{
  items?: SearchItem[]
}>(), {
  items: () => defaultItems,
})

const emit = defineEmits<{
  (e: 'select', item: SearchItem): void
}>()

const searchOpen = ref(false)
const searchQuery = ref('')
const searchInputRef = ref<HTMLInputElement | null>(null)
const searchWrapperRef = ref<HTMLElement | null>(null)

const searchResults = computed(() => {
  if (!searchQuery.value.trim()) return []
  const q = searchQuery.value.toLowerCase()
  return props.items.filter(
    item =>
      item.title.toLowerCase().includes(q) ||
      item.subtitle.toLowerCase().includes(q)
  )
})

async function toggleSearch() {
  searchOpen.value = !searchOpen.value
  if (searchOpen.value) {
    searchQuery.value = ''
    await nextTick()
    searchInputRef.value?.focus()
  }
}

function selectResult(item: SearchItem) {
  emit('select', item)
  searchOpen.value = false
  searchQuery.value = ''
}

function handleClickOutside(e: MouseEvent) {
  if (searchWrapperRef.value && !searchWrapperRef.value.contains(e.target as Node)) {
    searchOpen.value = false
  }
}

function handleKeydown(e: KeyboardEvent) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    toggleSearch()
  }
  if (e.key === 'Escape') {
    searchOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
  document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('mousedown', handleClickOutside)
})
</script>