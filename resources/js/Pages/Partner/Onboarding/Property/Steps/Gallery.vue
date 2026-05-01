<script setup lang="ts">
import { computed, ref } from 'vue'

// ─── Types ────────────────────────────────────────────────────────────
export interface GalleryFile {
  file    : File
  preview : string
}

export interface GalleryForm {
  files : GalleryFile[]
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = defineProps<{ modelValue: GalleryForm }>()
const emit  = defineEmits<{ 'update:modelValue': [value: GalleryForm] }>()

const form = computed({
  get : () => props.modelValue,
  set : (val) => emit('update:modelValue', val),
})

// ─── Helpers ──────────────────────────────────────────────────────────
function onFilesAdded(e: Event) {
  const incoming = Array.from((e.target as HTMLInputElement).files ?? [])
  const next: GalleryFile[] = incoming.map((file) => ({
    file,
    preview: URL.createObjectURL(file),
  }))
  emit('update:modelValue', { files: [...form.value.files, ...next] })
  // reset input so same file can be re-added if removed
  ;(e.target as HTMLInputElement).value = ''
}

function removeFile(index: number) {
  const files = form.value.files.filter((_, i) => i !== index)
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

      <div>
        <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-1">
          Property gallery
        </p>
        <p class="text-sm text-gray-400 mb-4">
          Upload photos of your property. Drag to reorder — the first image will be the cover.
        </p>
      </div>

      <!-- Image grid -->
      <div class="grid grid-cols-3 gap-3">

        <!-- Existing images -->
        <div
          v-for="(img, i) in form.files"
          :key="img.preview"
          draggable="true"
          :class="[
            'relative aspect-[4/3] rounded-xl overflow-hidden group border transition-all duration-150 cursor-grab active:cursor-grabbing',
            dragOver === i && dragFrom !== i
              ? 'border-gray-900 scale-[1.02] shadow-md'
              : 'border-gray-100',
          ]"
          @dragstart="onDragStart(i)"
          @dragenter.prevent="onDragEnter(i)"
          @dragover.prevent
          @drop.prevent="onDrop(i)"
          @dragend="onDragEnd"
        >
          <img
            :src="img.preview"
            class="w-full h-full object-cover pointer-events-none"
            :alt="`Gallery image ${i + 1}`"
          />

          <!-- Hover overlay -->
          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors" />

          <!-- Action buttons — visible on hover -->
          <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <!-- Set as cover -->
            <button
              v-if="i !== 0"
              type="button"
              title="Set as cover"
              class="w-7 h-7 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-sm"
              @click="setCover(i)"
            >
              <svg class="w-3.5 h-3.5 text-gray-700" viewBox="0 0 14 14" fill="none">
                <path d="M7 1l1.5 4h4l-3.5 2.5 1.5 4L7 9 3.5 11.5l1.5-4L1.5 5h4z"
                  stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
              </svg>
            </button>

            <!-- Remove -->
            <button
              type="button"
              title="Remove"
              class="w-7 h-7 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-sm"
              @click="removeFile(i)"
            >
              <svg class="w-3 h-3 text-gray-700" viewBox="0 0 12 12" fill="none">
                <path d="M2 2l8 8M10 2l-8 8" stroke="currentColor" stroke-width="1.6"
                  stroke-linecap="round"/>
              </svg>
            </button>
          </div>

          <!-- Cover badge -->
          <span
            v-if="i === 0"
            class="absolute top-2 left-2 text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full font-medium"
          >
            Cover
          </span>

          <!-- Drag handle hint -->
          <div class="absolute bottom-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-60 transition-opacity">
            <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none">
              <circle cx="5" cy="5" r="1" fill="currentColor"/>
              <circle cx="11" cy="5" r="1" fill="currentColor"/>
              <circle cx="5" cy="11" r="1" fill="currentColor"/>
              <circle cx="11" cy="11" r="1" fill="currentColor"/>
            </svg>
          </div>
        </div>

        <!-- Upload placeholder -->
        <label
          class="aspect-[4/3] rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-all"
        >
          <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 16 16" fill="none">
              <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          </div>
          <span class="text-xs text-gray-400">Add photos</span>
          <input
            type="file"
            accept="image/*"
            multiple
            class="hidden"
            @change="onFilesAdded"
          />
        </label>

      </div>

      <!-- Helper tip -->
      <p v-if="form.files.length > 0" class="text-xs text-gray-400 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 14 14" fill="none">
          <circle cx="7" cy="7" r="5.5" stroke="currentColor" stroke-width="1.2"/>
          <path d="M7 6v4M7 4.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
        Drag images to reorder. Click ★ to set a new cover photo.
      </p>

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
          <span
            v-if="form.files.length > 0"
            class="text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full"
          >
            {{ form.files.length }} photo{{ form.files.length === 1 ? '' : 's' }}
          </span>
        </div>

        <!-- Empty state -->
        <div v-if="form.files.length === 0" class="flex flex-col items-center py-8 gap-2 text-center">
          <div class="w-9 h-9 rounded-full bg-gray-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-300" viewBox="0 0 16 16" fill="none">
              <rect x="1.5" y="1.5" width="13" height="13" rx="2.5" stroke="currentColor" stroke-width="1.2"/>
              <path d="M1.5 11l3.5-3.5 3 3 2.5-2.5 5 4" stroke="currentColor" stroke-width="1.2"
                stroke-linejoin="round"/>
              <circle cx="5.5" cy="6" r="1.5" fill="currentColor" opacity=".35"/>
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
              <img
                :src="form.files[0].preview"
                class="w-full h-full object-cover"
                alt="Cover"
              />
            </div>
          </div>

          <!-- Remaining photos grid -->
          <div v-if="form.files.length > 1">
            <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest mb-1.5">
              Other photos ({{ form.files.length - 1 }})
            </p>
            <div class="grid grid-cols-3 gap-1.5">
              <div
                v-for="(img, i) in form.files.slice(1)"
                :key="img.preview"
                class="aspect-square rounded-lg overflow-hidden border border-gray-100 bg-gray-50"
              >
                <img
                  :src="img.preview"
                  class="w-full h-full object-cover"
                  :alt="`Photo ${i + 2}`"
                />
              </div>
            </div>
          </div>

        </template>
      </div>
    </aside>

  </div>
</template>