import { ref } from 'vue';
import axios from 'axios';

export function useReports() {
    const items = ref([] as unknown[]);

    async function fetchAll(): Promise<void> {
        const { data } = await axios.get('/api/v1/reports/snapshots');
        items.value = data.data;
    }

    return { items, fetchAll };
}
