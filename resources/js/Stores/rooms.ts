import { defineStore } from 'pinia';

export const useRoomsStore = defineStore('rooms', {
    state: () => ({ items: [] as unknown[] }),
});
