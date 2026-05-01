<template>
  <div v-if="languages.length > 1 && currentLanguage">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <button
          class="flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200/70 rounded-md text-slate-400 text-[13px] transition-colors"
          :aria-label="'Switch language'"
        >
          <span class="text-base leading-none" aria-hidden="true">{{ currentLanguage.flag }}</span>
          <span class="text-[13px] font-medium text-slate-600 hidden sm:inline">{{ currentLanguage.name }}</span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-400" :stroke-width="2.2" />
        </button>
      </DropdownMenuTrigger>

      <DropdownMenuContent
        class="w-52 rounded-xl border border-slate-200/80 shadow-lg shadow-slate-200/60 p-1.5"
        align="end"
        :side-offset="6"
      >
        <DropdownMenuItem
          v-for="lang in languages"
          :key="lang.code"
          @click="selectLanguage(lang)"
          class="flex items-center gap-3 px-2.5 py-2 rounded-lg cursor-pointer transition-colors outline-none"
          :class="
            lang.code === currentLanguage.code
              ? 'bg-cyan-50 text-cyan-700 focus:bg-cyan-50'
              : 'text-slate-600 hover:bg-slate-50 focus:bg-slate-50'
          "
        >
          <span class="text-lg leading-none" aria-hidden="true">{{ lang.flag }}</span>
          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-medium leading-none">{{ lang.name }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ lang.nativeName }}</p>
          </div>
          <Check
            v-if="lang.code === currentLanguage.code"
            class="w-3.5 h-3.5 text-cyan-600 shrink-0"
            :stroke-width="2.5"
          />
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ChevronDown, Check } from 'lucide-vue-next'
import { useLanguageStore } from '@/Stores/languageStore'
import type { Language } from '@/Stores/languageStore'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/Components/ui/dropdown-menu'

const languageStore = useLanguageStore()

const languages = computed(() => languageStore.availableLanguages)
const currentLanguage = computed<Language | null>(() =>
  languageStore.currentLanguageObj ?? languages.value[0] ?? null
)

async function selectLanguage(lang: Language) {
  if (lang.code === languageStore.currentLanguage) return
  await languageStore.setLanguage(lang.code)
}
</script>