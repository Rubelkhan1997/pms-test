<template>
    <div class="space-y-2">

        <!-- Row 1: Room Type, Short Code, Number of Rooms, Base Adult, Base Child, Max Adult, Max Child -->
        <div class="flex items-end gap-3">
            <div class="flex-3">
                <FormInput :modelValue="form.roomType" label="Room Type" name="roomType" required
                    @update:modelValue="update('roomType', $event)" />
            </div>
            <div class="flex-[1.5]">
                <FormInput :modelValue="form.shortCode" label="Short Code" name="shortCode" required
                    @update:modelValue="update('shortCode', $event)" />
            </div>
            <div class="flex-1">
                <FormInput :modelValue="form.numberOfRooms" label="Number of Rooms" name="numberOfRooms" type="number"
                    required @update:modelValue="update('numberOfRooms', $event)" />
            </div>
            <div class="flex-1">
                <FormInput :modelValue="form.baseAdult" label="Base Adult" name="baseAdult" type="number" required
                    @update:modelValue="update('baseAdult', $event)" />
            </div>
            <div class="flex-1">
                <FormInput :modelValue="form.baseChild" label="Base Child" name="baseChild" type="number" required
                    @update:modelValue="update('baseChild', $event)" />
            </div>
            <div class="flex-1">
                <FormInput :modelValue="form.maxAdult" label="Max Adult" name="maxAdult" type="number" required
                    @update:modelValue="update('maxAdult', $event)" />
            </div>
            <div class="flex-1">
                <FormInput :modelValue="form.maxChild" label="Max Child" name="maxChild" type="number" required
                    @update:modelValue="update('maxChild', $event)" />
            </div>
        </div>

        <!-- Row 2: Description -->
        <div>

            <FormTextarea id="description" v-model="form.description" label="Description"
                placeholder="Enter room type description..." :rows="4" required hint="Max 500 characters"
                wrapper-class="mb-0" />
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-100" />

        <!-- Row 3: Bed Type, View Type, Room Size -->
        <div class="flex items-end gap-4">
            <div class="flex-[2]">
                <FormSelect :modelValue="form.bedType" label="Which beds are available in this room type?"
                    name="bedType" :options="bedTypeOptions" @update:modelValue="update('bedType', $event)" />
            </div>
            <div class="flex-1">
                <FormSelect :modelValue="form.viewType" label="View Type" name="viewType" :options="viewTypeOptions"
                    @update:modelValue="update('viewType', $event)" />
            </div>
            <div class="flex-1 relative mb-4">
                <!-- Room Size with unit selector -->
                <label class="text-gray-800 font-medium mb-2 text-[14px] block">Room Size</label>
                <div
                    class="flex items-center border border-slate-200 rounded-md overflow-hidden focus-within:border-primary transition-colors">
                    <input :value="form.roomSize" @input="update('roomSize', ($event.target as HTMLInputElement).value)"
                        type="number" placeholder="0"
                        class="flex-1 h-9 px-3 py-1 text-[13.5px] focus:outline-none placeholder:text-slate-300 bg-white" />
                    <select :value="form.roomSizeUnit"
                        @change="update('roomSizeUnit', ($event.target as HTMLSelectElement).value)"
                        class="px-3 py-1 h-9 text-[13px] text-slate-500 border-l border-slate-200 bg-slate-50 focus:outline-none cursor-pointer">
                        <option value="sqft">sq ft</option>
                        <option value="sqm">sq m</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-100" />

        <!-- Advance Settings -->
        <div class="space-y-4">
            <h3 class="text-[16px] font-semibold text-slate-700">Advance Settings</h3>

           <div class="space-y-2">
    <label class="block text-[15px] font-medium text-slate-700">
        Is this room type published in IBE?
    </label>
    <div class="flex items-center gap-5">
        <FormRadio v-model="form.publishedInIBE" name="publishedInIBE" value="yes" label="Yes" />
        <FormRadio v-model="form.publishedInIBE" name="publishedInIBE" value="no" label="No" />
    </div>

    <!-- Conditional field -->
    <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
    >
        <div v-if="form.publishedInIBE === 'yes'" class="pt-1 max-w-xs">
            <FormInput
                :modelValue="form.ibeInventory"
                label="Do you want to ration inventory on IBE"
                name="ibeInventory"
                type="number"
                placeholder="0"
                @update:modelValue="update('ibeInventory', $event)"
            />
        </div>
    </Transition>
</div>

            <!-- Color -->
            <div class="flex items-center gap-3">
                <label class="text-[14px] font-medium text-slate-800">Color:</label>
                <input type="color" :value="form.color"
                    @input="update('color', ($event.target as HTMLInputElement).value)"
                    class="w-9 h-9 p-0.5 border border-slate-200 rounded-lg cursor-pointer bg-white" />
                <span class="text-[12px] text-slate-400">{{ form.color }}</span>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormSelect from '@/Components/Form/FormSelect.vue'
import FormRadio from '@/Components/Form/FormRadio.vue'
import FormTextarea from '../Form/FormTextarea.vue'

// ─── Form State ──────────────────────────────────────────
const form = reactive({
    roomType: '',
    shortCode: '',
    numberOfRooms: '',
    baseAdult: '',
    baseChild: '',
    maxAdult: '',
    maxChild: '',
    description: '',
    bedType: '',
    viewType: '',
    roomSize: '',
    roomSizeUnit: 'sqft',
    publishedInIBE: 'no',
    ibeInventory: '',
    color: '#000000',
})

function update(key: keyof typeof form, value: string) {
    form[key] = value
}

// ─── Options ─────────────────────────────────────────────
const bedTypeOptions = [
    { label: 'Single', value: 'single' },
    { label: 'Double', value: 'double' },
    { label: 'Twin', value: 'twin' },
    { label: 'King', value: 'king' },
    { label: 'Queen', value: 'queen' },
    { label: 'Bunk', value: 'bunk' },
]

const viewTypeOptions = [
    { label: 'Sea View', value: 'sea' },
    { label: 'Garden View', value: 'garden' },
    { label: 'Pool View', value: 'pool' },
    { label: 'City View', value: 'city' },
    { label: 'Mountain View', value: 'mountain' },
    { label: 'No View', value: 'none' },
]
</script>