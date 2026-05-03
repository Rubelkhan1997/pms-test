<template>
    <div class="space-y-4">

        <!-- Header -->
        <div>
            <p class="text-[14px] text-slate-700 leading-relaxed">
                Add images for this room type so guests can see exactly what they're booking.
            </p>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="text-[13px] font-semibold text-slate-700">Limit:</span>
                <span class="text-[13px] text-slate-600">10 images per room type, each under 2MB</span>
            </div>
        </div>

        <!-- Toolbar — only when images exist -->
        <div v-if="files.length > 0" class="flex items-center gap-2">
            <button type="button"
                class="flex items-center gap-1.5 text-[12px] font-medium px-3 py-1.5 rounded-lg border transition-all"
                :class="isAllSelected
                    ? 'bg-slate-800 text-white border-slate-800'
                    : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                @click="toggleSelectAll">
                <span :class="[
                    'w-3.5 h-3.5 rounded border flex items-center justify-center shrink-0 transition-colors',
                    isAllSelected ? 'bg-white border-white' : isSelectMode ? 'bg-slate-800 border-slate-800' : 'border-slate-400',
                ]">
                    <svg v-if="isAllSelected || isSelectMode" class="w-2 h-2" :class="isAllSelected ? 'text-slate-800' : 'text-white'" viewBox="0 0 10 10" fill="none">
                        <path v-if="isAllSelected" d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        <path v-else d="M2 5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </span>
                {{ isAllSelected ? 'Deselect all' : 'Select all' }}
            </button>

            <Transition name="fade">
                <span v-if="isSelectMode" class="text-[12px] text-slate-400">
                    {{ selectedIndices.size }} selected
                </span>
            </Transition>

            <div class="flex-1" />

            <Transition name="fade">
                <button v-if="isSelectMode" type="button"
                    class="flex items-center gap-1.5 text-[12px] font-medium px-3 py-1.5 rounded-lg border border-red-200 text-red-500 bg-red-50 hover:bg-red-100 transition-all"
                    @click="deleteSelected">
                    <Trash2 class="w-3.5 h-3.5" :stroke-width="1.8" />
                    Delete ({{ selectedIndices.size }})
                </button>
            </Transition>

            <Transition name="fade">
                <button v-if="isSelectMode" type="button"
                    class="text-[12px] text-slate-400 hover:text-slate-600 transition-colors px-2"
                    @click="clearSelection">
                    Cancel
                </button>
            </Transition>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-5 gap-3">

            <!-- Images -->
            <div
                v-for="(img, i) in files" :key="img.preview"
                draggable="true"
                class="relative aspect-4/3 rounded-lg overflow-hidden group border-2 transition-all duration-150 cursor-grab active:cursor-grabbing"
                :class="selectedIndices.has(i)
                    ? 'border-primary ring-2 ring-primary/20'
                    : dragOver === i && dragFrom !== i
                        ? 'border-primary scale-[1.02]'
                        : 'border-transparent'"
                @dragstart="onDragStart(i)"
                @dragenter.prevent="onDragEnter(i)"
                @dragover.prevent
                @drop.prevent="onDrop(i)"
                @dragend="onDragEnd">

                <img :src="img.preview" class="w-full h-full object-cover pointer-events-none" />

                <!-- Overlay -->
                <div class="absolute inset-0 transition-colors"
                    :class="selectedIndices.has(i) ? 'bg-black/25' : 'bg-black/0 group-hover:bg-black/30'" />

                <!-- Checkbox top-left -->
                <button type="button"
                    class="absolute top-2 left-2 w-5 h-5 rounded flex items-center justify-center transition-all duration-150"
                    :class="selectedIndices.has(i)
                        ? 'opacity-100 bg-primary border border-primary'
                        : 'opacity-0 group-hover:opacity-100 bg-white/80 border border-white/60'"
                    @click.stop="toggleSelect(i)">
                    <svg v-if="selectedIndices.has(i)" class="w-2.5 h-2.5 text-white" viewBox="0 0 10 10" fill="none">
                        <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <!-- Delete top-right -->
                <button type="button"
                    class="absolute top-2 right-2 w-7 h-7 bg-white/90 hover:bg-white rounded-full flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 shadow-sm"
                    :class="selectedIndices.has(i) ? 'opacity-0!' : ''"
                    @click="removeFile(i)">
                    <X class="w-3 h-3 text-slate-700" :stroke-width="2.2" />
                </button>

                <!-- Drag dots bottom center -->
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-70 transition-opacity">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 16 16" fill="none">
                        <circle cx="5" cy="5" r="1.2" fill="currentColor" />
                        <circle cx="11" cy="5" r="1.2" fill="currentColor" />
                        <circle cx="5" cy="11" r="1.2" fill="currentColor" />
                        <circle cx="11" cy="11" r="1.2" fill="currentColor" />
                    </svg>
                </div>

                <!-- Image number bottom-left -->
                <span class="absolute bottom-2 left-2 text-[10px] px-1.5 py-0.5 bg-black/40 text-white rounded font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                    {{ i + 1 }}
                </span>
            </div>

            <!-- Upload box — same size as images -->
            <label v-if="files.length < 10"
                class="aspect-4/3 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-primary hover:bg-cyan-50/20 transition-all group">
                <div class="w-10 h-10 rounded-lg bg-slate-100 group-hover:bg-primary/30 flex items-center justify-center transition-colors">
                    <Upload class="w-4.5 h-4.5 text-slate-500 group-hover:text-primary transition-colors" :stroke-width="1.8" />
                </div>
                <span class="text-[13px] text-slate-700 group-hover:text-primary transition-colors font-medium">Upload Image</span>
                <input type="file" accept="image/*" multiple class="hidden" @change="onFilesAdded" />
            </label>

        </div>

        <!-- Footer info -->
        <div class="flex items-center justify-between">
            <p v-if="files.length > 0" class="text-[12px] text-slate-400 flex items-center gap-1.5">
                <GripHorizontal class="w-3.5 h-3.5" :stroke-width="1.8" />
                Drag to reorder images
            </p>
            <p class="text-[12px] text-slate-400 ml-auto">
                <span :class="remaining === 0 ? 'text-red-500 font-medium' : ''">
                    {{ remaining }} image{{ remaining === 1 ? '' : 's' }} left
                </span>
            </p>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { X, Trash2, Upload, GripHorizontal } from 'lucide-vue-next'

// ─── Types ───────────────────────────────────────────────
interface GalleryFile {
    file: File
    preview: string
}

// ─── State ───────────────────────────────────────────────
const files = ref<GalleryFile[]>([])
const selectedIndices = ref<Set<number>>(new Set())
const dragFrom = ref<number | null>(null)
const dragOver = ref<number | null>(null)

const MAX = 10

// ─── Computed ────────────────────────────────────────────
const isSelectMode  = computed(() => selectedIndices.value.size > 0)
const isAllSelected = computed(() => files.value.length > 0 && selectedIndices.value.size === files.value.length)
const remaining     = computed(() => MAX - files.value.length)

// ─── Selection ───────────────────────────────────────────
function toggleSelect(i: number) {
    const next = new Set(selectedIndices.value)
    next.has(i) ? next.delete(i) : next.add(i)
    selectedIndices.value = next
}

function toggleSelectAll() {
    selectedIndices.value = isAllSelected.value
        ? new Set()
        : new Set(files.value.map((_, i) => i))
}

function clearSelection() {
    selectedIndices.value = new Set()
}

function deleteSelected() {
    files.value = files.value.filter((_, i) => !selectedIndices.value.has(i))
    selectedIndices.value = new Set()
}

// ─── Upload ──────────────────────────────────────────────
function onFilesAdded(e: Event) {
    const input = e.target as HTMLInputElement
    const incoming = Array.from(input.files ?? []).slice(0, remaining.value)
    const next: GalleryFile[] = incoming.map(file => ({
        file,
        preview: URL.createObjectURL(file),
    }))
    files.value = [...files.value, ...next]
    input.value = ''
}

function removeFile(i: number) {
    const next = new Set<number>()
    selectedIndices.value.forEach(si => {
        if (si < i) next.add(si)
        else if (si > i) next.add(si - 1)
    })
    selectedIndices.value = next
    files.value = files.value.filter((_, idx) => idx !== i)
}

// ─── Drag to reorder ─────────────────────────────────────
function onDragStart(i: number) { dragFrom.value = i }
function onDragEnter(i: number) { dragOver.value = i }
function onDragEnd()            { dragFrom.value = null; dragOver.value = null }

function onDrop(to: number) {
    const from = dragFrom.value
    if (from === null || from === to) return
    const arr = [...files.value]
    const [moved] = arr.splice(from, 1)
    arr.splice(to, 0, moved)
    files.value = arr
    dragFrom.value = null
    dragOver.value = null
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>