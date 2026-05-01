<script setup lang="ts">
import { computed } from 'vue'

// ─── Types ────────────────────────────────────────────────────────────
export interface AmenityItem {
  label    : string
  selected : boolean
}

export interface AmenityGroup {
  name  : string
  items : AmenityItem[]
}

export interface AmenitiesForm {
  selectedFeatured : number[]
  amenityGroups    : AmenityGroup[]
}

// ─── Props & Emits ────────────────────────────────────────────────────
const props = defineProps<{ modelValue: AmenitiesForm }>()
const emit  = defineEmits<{ 'update:modelValue': [value: AmenitiesForm] }>()

const form = computed({
  get : () => props.modelValue,
  set : (val) => emit('update:modelValue', val),
})

// ─── Featured amenities list ──────────────────────────────────────────
const featuredAmenities = [
  { icon: '🏊', label: 'Swimming Pool'  },
  { icon: '🅿️', label: 'Free Parking'   },
  { icon: '📶', label: 'Free Wi-Fi'     },
  { icon: '🍳', label: 'Breakfast'      },
  { icon: '🏋️', label: 'Gym'            },
  { icon: '🛎️', label: '24h Front Desk' },
  { icon: '🍽️', label: 'Restaurant'     },
  { icon: '♿', label: 'Accessible'     },
  { icon: '🛁', label: 'Private Bath'   },
  { icon: '❄️', label: 'Air Conditioning'},
  { icon: '🌿', label: 'Garden'         },
  { icon: '🚌', label: 'Airport Shuttle'},
]

// ─── Helpers ──────────────────────────────────────────────────────────
function isFeaturedSelected(index: number) {
  return form.value.selectedFeatured.includes(index)
}

function toggleFeatured(index: number) {
  const current = form.value.selectedFeatured
  const next = current.includes(index)
    ? current.filter((i) => i !== index)
    : [...current, index]

  emit('update:modelValue', { ...form.value, selectedFeatured: next })
}

function toggleGroupItem(groupIndex: number, itemIndex: number) {
  const groups = form.value.amenityGroups.map((g, gi) =>
    gi !== groupIndex ? g : {
      ...g,
      items: g.items.map((item, ii) =>
        ii !== itemIndex ? item : { ...item, selected: !item.selected }
      ),
    }
  )
  emit('update:modelValue', { ...form.value, amenityGroups: groups })
}

// ─── Preview computed ─────────────────────────────────────────────────
const totalSelected = computed(() => {
  const featuredCount = form.value.selectedFeatured.length
  const groupCount    = form.value.amenityGroups
    .flatMap((g) => g.items)
    .filter((it) => it.selected).length
  return featuredCount + groupCount
})
</script>

<template>
  <div class="flex gap-6 items-start">

    <!-- ══════════════════════════════════
         LEFT — Form
    ══════════════════════════════════════ -->
    <div class="flex-1 min-w-0 space-y-6">

      <!-- ── Featured amenities ── -->
      <section>
        <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
          Featured amenities
        </p>

        <div class="grid grid-cols-3 gap-2">
          <button
            v-for="(amenity, i) in featuredAmenities"
            :key="i"
            type="button"
            :class="[
              'flex items-center gap-2 px-3 py-2.5 border rounded-lg text-sm transition-colors text-left',
              isFeaturedSelected(i)
                ? 'border-gray-900 bg-gray-50 text-gray-900'
                : 'border-gray-200 text-gray-500 hover:bg-gray-50',
            ]"
            @click="toggleFeatured(i)"
          >
            <span class="text-base leading-none">{{ amenity.icon }}</span>
            <span class="text-xs">{{ amenity.label }}</span>
          </button>
        </div>
      </section>

      <!-- ── Grouped amenities ── -->
      <section
        v-for="(group, gi) in form.amenityGroups"
        :key="gi"
      >
        <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">
          {{ group.name }}
        </p>

        <div class="grid grid-cols-2 gap-2">
          <button
            v-for="(item, ii) in group.items"
            :key="ii"
            type="button"
            :class="[
              'flex items-center gap-2 px-3 py-2.5 border rounded-lg text-sm transition-colors text-left',
              item.selected
                ? 'border-gray-900 bg-gray-50 text-gray-900'
                : 'border-gray-200 text-gray-500 hover:bg-gray-50',
            ]"
            @click="toggleGroupItem(gi, ii)"
          >
            <!-- Checkbox indicator -->
            <span
              :class="[
                'w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors',
                item.selected ? 'bg-gray-900 border-gray-900' : 'border-gray-300',
              ]"
            >
              <svg v-if="item.selected" class="w-2.5 h-2.5 text-white" viewBox="0 0 10 10" fill="none">
                <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor" stroke-width="1.5"
                  stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
            {{ item.label }}
          </button>
        </div>
      </section>

    </div>

    <!-- ══════════════════════════════════
         RIGHT — Live Preview
    ══════════════════════════════════════ -->
    <aside class="w-72 shrink-0 sticky top-6">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">

        <div class="flex items-center justify-between mb-3">
          <p class="text-xs font-medium text-gray-400 uppercase tracking-widest">
            Selected amenities
          </p>
          <span
            v-if="totalSelected > 0"
            class="text-[10px] px-2 py-0.5 bg-gray-900 text-white rounded-full"
          >
            {{ totalSelected }}
          </span>
        </div>

        <!-- Empty state -->
        <div v-if="totalSelected === 0" class="flex flex-col items-center py-8 gap-2 text-center">
          <div class="w-9 h-9 rounded-full bg-gray-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-300" viewBox="0 0 16 16" fill="none">
              <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="text-xs text-gray-400">No amenities selected yet</p>
        </div>

        <template v-else>

          <!-- Featured pills -->
          <div v-if="form.selectedFeatured.length > 0" class="mb-4">
            <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest mb-2">
              Featured
            </p>
            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="i in form.selectedFeatured"
                :key="i"
                class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-900 text-white rounded-full text-xs"
              >
                <span class="leading-none">{{ featuredAmenities[i].icon }}</span>
                {{ featuredAmenities[i].label }}
              </span>
            </div>
          </div>

          <!-- Group pills -->
          <template v-for="(group, gi) in form.amenityGroups" :key="gi">
            <div
              v-if="group.items.some((it) => it.selected)"
              class="mb-3"
            >
              <p class="text-[10px] font-medium text-gray-300 uppercase tracking-widest mb-1.5">
                {{ group.name }}
              </p>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="item in group.items.filter((it) => it.selected)"
                  :key="item.label"
                  class="inline-flex items-center gap-1 px-2.5 py-1 border border-gray-200 rounded-full text-xs text-gray-600"
                >
                  <svg class="w-2.5 h-2.5 text-gray-400" viewBox="0 0 10 10" fill="none">
                    <path d="M1.5 5l2.5 2.5 4.5-4.5" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  {{ item.label }}
                </span>
              </div>
            </div>
          </template>

          <!-- Footer count -->
          <p class="text-xs text-gray-400 pt-3 border-t border-gray-100">
            {{ totalSelected }} amenit{{ totalSelected === 1 ? 'y' : 'ies' }} selected
          </p>

        </template>
      </div>
    </aside>

  </div>
</template>