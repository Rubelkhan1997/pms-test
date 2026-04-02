/**
 * Stores Barrel Export
 * Central export file for all Pinia stores
 */

// Auth module stores
export { useAuthStore } from './Auth/authStore';

// FrontDesk module stores
export { useReservationsStore } from './FrontDesk/reservationStore';

// Global stores
export { useLanguageStore } from './languageStore';