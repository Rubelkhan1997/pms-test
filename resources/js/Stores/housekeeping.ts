import { defineStore } from 'pinia';

export const useHousekeepingStore = defineStore('housekeeping', {
    state: () => ({ items: [] as unknown[] }),
});
