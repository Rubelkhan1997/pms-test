import { defineStore } from 'pinia';

export const useMobileStore = defineStore('mobile', {
    state: () => ({ items: [] as unknown[] }),
});
