import { defineStore } from 'pinia';

export const usePosStore = defineStore('pos', {
    state: () => ({ items: [] as unknown[] }),
});
