import { ref } from 'vue';
import axios from 'axios';

export function useHousekeeping() {
    const items = ref([] as unknown[]);

    async function fetchAll(): Promise<void> {
        const { data } = await axios.get('/api/v1/housekeeping/tasks');
        items.value = data.data;
    }

    return { items, fetchAll };
}
