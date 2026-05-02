<script setup lang="ts">
import { computed, ref } from 'vue'

// ─── Types ────────────────────────────────────────────────────────────
export interface GalleryFile {
  file: File
  preview: string
}

export interface GalleryForm {
  files: GalleryFile[]
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = defineProps<{ modelValue: GalleryForm }>()
const emit = defineEmits<{ 'update:modelValue': [value: GalleryForm] }>()

const form = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

// ─── Selection State ──────────────────────────────────────────────────
const selectedIndices = ref<Set<number>>(new Set())

const isSelectMode = computed(() => selectedIndices.value.size > 0)

const isAllSelected = computed(
  () => form.value.files.length > 0 && selectedIndices.value.size === form.value.files.length,
)

function toggleSelect(index: number) {
  const next = new Set(selectedIndices.value)
  if (next.has(index)) next.delete(index)
  else next.add(index)
  selectedIndices.value = next
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedIndices.value = new Set()
  } else {
    selectedIndices.value = new Set(form.value.files.map((_, i) => i))
  }
}

function deleteSelected() {
  const files = form.value.files.filter((_, i) => !selectedIndices.value.has(i))
  emit('update:modelValue', { files })
  selectedIndices.value = new Set()
}

function clearSelection() {
  selectedIndices.value = new Set()
}

// ─── Helpers ──────────────────────────────────────────────────────────
function onFilesAdded(e: Event) {
  const incoming = Array.from((e.target as HTMLInputElement).files ?? [])
  const next: GalleryFile[] = incoming.map((file) => ({
    file,
    preview: URL.createObjectURL(file),
  }))
  emit('update:modelValue', { files: [...form.value.files, ...next] })
    ; (e.target as HTMLInputElement).value = ''
}

function removeFile(index: number) {
  const files = form.value.files.filter((_, i) => i !== index)
  // Fix selection indices after removal
  const next = new Set<number>()
  selectedIndices.value.forEach((si) => {
    if (si < index) next.add(si)
    else if (si > index) next.add(si - 1)
  })
  selectedIndices.value = next
  emit('update:modelValue', { files })
}

function setCover(index: number) {
  if (index === 0) return
  const files = [...form.value.files]
  const [picked] = files.splice(index, 1)
  emit('update:modelValue', { files: [picked, ...files] })
}

// ─── Drag-to-reorder ─────────────────────────────────────────────────
const dragFrom = ref<number | null>(null)
const dragOver = ref<number | null>(null)

function onDragStart(index: number) {
  dragFrom.value = index
}

function onDragEnter(index: number) {
  dragOver.value = index
}

function onDrop(toIndex: number) {
  const from = dragFrom.value
  if (from === null || from === toIndex) return

  const files = [...form.value.files]
  const [moved] = files.splice(from, 1)
  files.splice(toIndex, 0, moved)
  emit('update:modelValue', { files })

  dragFrom.value = null
  dragOver.value = null
}

function onDragEnd() {
  dragFrom.value = null
  dragOver.value = null
}
</script>

<template>
  <div class="flex gap-6 items-start">

    <!-- ══════════════════════════════════
         LEFT — Uploader
    ══════════════════════════════════════ -->
    <div class="flex-1 min-w-0 space-y-4">

      <section class="">
        <div class="pb-3 mb-4  border-gray-10">
          <h2 class="text-base font-semibold text-gray-800 tracking-wide mb-1">
            Gallery
          </h2>
          <p class="text-sm text-gray-500">Upload at least 20 photos that showcase your property's common areas. Images
            should be high-resolution, landscape-oriented, and no larger than 2048x1536 px. High-quality visuals attract
            and engage potential guests. Photos added here may also be shared with your online channels.
          </p>
        </div>
      </section>
      <!-- ── Toolbar ── -->
      <div v-if="form.files.length > 0" class="flex items-center gap-3">
        <!-- Select All toggle -->
        <button type="button"
          class="flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-lg border transition-all" :class="isAllSelected
            ? 'bg-gray-900 text-white border-gray-900'
            : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'" @click="toggleSelectAll">
          <!-- Checkbox icon -->
          <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 14 14" fill="none">
            <rect x="1" y="1" width="12" height="12" rx="2.5" :fill="isAllSelected ? 'currentColor' : 'none'"
              stroke="currentColor" stroke-width="1.3" />
            <path v-if="isAllSelected" d="M3.5 7l2.5 2.5L10.5 5" stroke="white" stroke-width="1.4"
              stroke-linecap="round" stroke-linejoin="round" />
            <path v-else-if="isSelectMode && !isAllSelected" d="M3.5 7h7" stroke="currentColor" stroke-width="1.4"
              stroke-linecap="round" />
          </svg>
          {{ isAllSelected ? 'Deselect all' : 'Select all' }}
        </button>

        <!-- Selected count badge -->
        <Transition name="fade">
          <span v-if="isSelectMode" class="text-xs font-medium text-gray-500">
            {{ selectedIndices.size }} selected
          </span>
        </Transition>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Bulk delete button — visible when items selected -->
        <Transition name="fade">
          <button v-if="isSelectMode" type="button"
            class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition-all"
            @click="deleteSelected">
            <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="none">
              <path
                d="M2 3.5h10M5 3.5V2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v1M11.5 3.5l-.7 7.5a1 1 0 0 1-1 .9H4.2a1 1 0 0 1-1-.9L2.5 3.5"
                stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M5.5 6.5v3M8.5 6.5v3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" />
            </svg>
            Delete ({{ selectedIndices.size }})
          </button>
        </Transition>

        <!-- Cancel selection -->
        <Transition name="fade">
          <button v-if="isSelectMode" type="button"
            class="text-xs text-gray-400 hover:text-gray-600 transition-colors px-2 py-1.5" @click="clearSelection">
            Cancel
          </button>
        </Transition>
      </div>

      <!-- Image grid -->
      <div class="grid grid-cols-3 gap-3">

        <!-- Existing images -->
        <div v-for="(img, i) in form.files" :key="img.preview" draggable="true" :class="[
          'relative aspect-4/3 rounded-lg overflow-hidden group border transition-all duration-150 cursor-grab active:cursor-grabbing',
          selectedIndices.has(i)
            ? 'border-primary ring-1 ring-primary ring-offset-1'
            : dragOver === i && dragFrom !== i
              ? 'border-primary scale-[1.02] shadow-md'
              : 'border-gray-100',
        ]" @dragstart="onDragStart(i)" @dragenter.prevent="onDragEnter(i)" @dragover.prevent @drop.prevent="onDrop(i)"
          @dragend="onDragEnd">
          <img :src="img.preview" class="w-full h-full object-cover pointer-events-none"
            :alt="`Gallery image ${i + 1}`" />

          <!-- Hover / selected overlay -->
          <div class="absolute inset-0 transition-colors" :class="selectedIndices.has(i)
            ? 'bg-black/20'
            : 'bg-black/0 group-hover:bg-black/25'" />

          <!-- ── Checkbox (top-left) — always visible when selected, shows on hover ── -->
          <button type="button"
            class="absolute top-2 left-2 w-5 h-5 rounded flex items-center justify-center transition-all duration-150"
            :class="selectedIndices.has(i)
              ? 'opacity-100 bg-gray-900 border border-gray-900'
              : 'opacity-0 group-hover:opacity-100 bg-white/85 border border-gray-300 hover:border-gray-500'"
            @click.stop="toggleSelect(i)" :title="selectedIndices.has(i) ? 'Deselect' : 'Select'">
            <svg v-if="selectedIndices.has(i)" class="w-3 h-3 text-white" viewBox="0 0 12 12" fill="none">
              <path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </button>

          <!-- Action buttons (top-right) — visible on hover when NOT selected -->
          <div class="absolute top-2 right-2 flex gap-1 transition-opacity"
            :class="selectedIndices.has(i) ? 'opacity-0' : 'opacity-0 group-hover:opacity-100'">
            <!-- Set as cover -->
            <button v-if="i !== 0" type="button" title="Set as cover"
              class="w-7 h-7 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-sm"
              @click="setCover(i)">
              <svg class="w-3.5 h-3.5 text-gray-700" viewBox="0 0 14 14" fill="none">
                <path d="M7 1l1.5 4h4l-3.5 2.5 1.5 4L7 9 3.5 11.5l1.5-4L1.5 5h4z" stroke="currentColor"
                  stroke-width="1.2" stroke-linejoin="round" />
              </svg>
            </button>

            <!-- Remove single -->
            <button type="button" title="Remove"
              class="w-7 h-7 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-sm"
              @click="removeFile(i)">
              <svg class="w-3 h-3 text-gray-700" viewBox="0 0 12 12" fill="none">
                <path d="M2 2l8 8M10 2l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
              </svg>
            </button>
          </div>

          <!-- Cover badge -->
          <span v-if="i === 0 && !selectedIndices.has(0)"
            class="absolute top-2 left-8 text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full font-medium transition-opacity"
            :class="selectedIndices.has(0) ? 'opacity-0' : 'opacity-100'">
            Cover
          </span>
          <span v-if="i === 0 && !isSelectMode"
            class="absolute top-2 left-8 text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full font-medium">
            Cover
          </span>

          <!-- Drag handle hint -->
          <div class="absolute bottom-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-60 transition-opacity">
            <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none">
              <circle cx="5" cy="5" r="1" fill="currentColor" />
              <circle cx="11" cy="5" r="1" fill="currentColor" />
              <circle cx="5" cy="11" r="1" fill="currentColor" />
              <circle cx="11" cy="11" r="1" fill="currentColor" />
            </svg>
          </div>
        </div>

        <!-- Upload placeholder -->
        <label
          class="aspect-[4/3] rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-all">
          <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 16 16" fill="none">
              <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
          </div>
          <span class="text-xs text-gray-400">Add photos</span>
          <input type="file" accept="image/*" multiple class="hidden" @change="onFilesAdded" />
        </label>

      </div>

      <!-- Helper tip -->
      <p v-if="form.files.length > 0" class="text-xs text-gray-400 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 14 14" fill="none">
          <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.2" />
          <path d="M7 6v4M7 4.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
        </svg>
        Hover to select or drag to reorder. Click ★ to set a new cover photo.
      </p>

      <div class="border-t border-gray-100 pt-5 space-y-4">

        <div>
          <h3 class="text-sm font-semibold text-gray-800 mb-1">Video Links</h3>
          <p class="text-sm text-gray-500">
            Add video URLs from the supported platforms below.
          </p>
        </div>

        <!-- Provider + URL row -->
        <div class="flex gap-2 items-start">

          <!-- Provider dropdown -->
          <div class="shrink-0">
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Provider</label>
            <div class="relative">
              <select
                class="appearance-none w-36 px-3 py-2 text-sm border border-gray-200 rounded-md outline-none bg-white text-gray-700 cursor-pointer focus:border-orange-400 focus:ring-2 focus:ring-orange-50 transition-colors pr-8">
                <option value="youtube">YouTube</option>
                <option value="dailymotion">Daily Motion</option>
                <option value="vimeo">Vimeo</option>
              </select>
              <!-- Chevron icon -->
              <span class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 14 14" fill="none">
                  <path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
              </span>
            </div>
          </div>

          <!-- URL input -->
          <div class="flex-1 min-w-0">
            <label class="block text-xs text-gray-500 mb-1.5 font-medium">Url</label>
            <div class="flex gap-2">
              <input type="url" placeholder="Paste video URL…"
                class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-md outline-none transition-colors placeholder-gray-300 text-gray-700 focus:border-gray-400 focus:ring-2 focus:ring-gray-100" />
              <button type="button"
                class="shrink-0 px-4 py-2 text-sm font-medium rounded-md bg-primary text-white hover:bg-secondary active:scale-95 transition-all">
                Add
              </button>
            </div>
            <!-- Error state -->
            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 hidden">
              <svg class="w-3 h-3 shrink-0" viewBox="0 0 12 12" fill="none">
                <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.2" />
                <path d="M6 3.5v3M6 8v.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
              </svg>
              Invalid URL. Only YouTube, Vimeo, and Dailymotion links are supported.
            </p>
          </div>

        </div>



      </div>

    </div>

    <!-- ══════════════════════════════════
         RIGHT — Live Preview
    ══════════════════════════════════════ -->
    <aside class="w-72 shrink-0 sticky top-6">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">

        <div class="flex items-center justify-between mb-3">
          <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">
            Gallery preview
          </p>
          <span v-if="form.files.length > 0" class="text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full">
            {{ form.files.length }} photo{{ form.files.length === 1 ? '' : 's' }}
          </span>
        </div>

        <!-- Empty state -->
        <div v-if="form.files.length === 0" class="flex flex-col items-center py-8 gap-2 text-center">
          <div class="w-9 h-9 rounded-full bg-gray-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-300" viewBox="0 0 16 16" fill="none">
              <rect x="1.5" y="1.5" width="13" height="13" rx="2.5" stroke="currentColor" stroke-width="1.2" />
              <path d="M1.5 11l3.5-3.5 3 3 2.5-2.5 5 4" stroke="currentColor" stroke-width="1.2"
                stroke-linejoin="round" />
              <circle cx="5.5" cy="6" r="1.5" fill="currentColor" opacity=".35" />
            </svg>
          </div>
          <p class="text-xs text-gray-400">No photos uploaded yet</p>
        </div>

        <template v-else>

          <!-- Cover photo -->
          <div class="mb-3">
            <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest mb-1.5">
              Cover photo
            </p>
            <div class="aspect-video rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
              <img :src="form.files[0].preview" class="w-full h-full object-cover" alt="Cover" />
            </div>
          </div>

          <!-- Remaining photos grid -->
          <div v-if="form.files.length > 1">
            <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest mb-1.5">
              Other photos ({{ form.files.length - 1 }})
            </p>
            <div class="grid grid-cols-3 gap-1.5">
              <div v-for="(img, i) in form.files.slice(1)" :key="img.preview"
                class="aspect-square rounded-lg overflow-hidden border border-gray-100 bg-gray-50">
                <img :src="img.preview" class="w-full h-full object-cover" :alt="`Photo ${i + 2}`" />
              </div>
            </div>
          </div>

        </template>
      </div>
    </aside>

  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>