/**
 * Stores Barrel Export
 * Central export file for all Pinia stores
 */

// FrontDesk module stores
export { useReservationsStore } from './FrontDesk/reservationStore';
export { useRoomsStore } from './FrontDesk/roomStore';
export { useGuestsStore } from './FrontDesk/guestStore';

// Housekeeping module stores
export { useHousekeepingStore } from './Housekeeping/housekeepingStore';

// Auth store (to be implemented)
// export { useAuthStore } from './authStore';

// Settings store (to be implemented)
// export { useSettingsStore } from './settingsStore';

// Notification store (to be implemented)
// export { useNotificationStore } from './notificationStore';
