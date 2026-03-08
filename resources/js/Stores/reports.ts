import { defineStore } from 'pinia';

export const useReportsStore = defineStore('reports', {
    state: () => ({ items: [] as unknown[] }),
});
