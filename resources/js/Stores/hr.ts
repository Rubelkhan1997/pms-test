import { defineStore } from 'pinia';

export const useHrStore = defineStore('hr', {
    state: () => ({ items: [] as unknown[] }),
});
