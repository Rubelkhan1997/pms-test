/**
 * Directives Barrel Export
 * Central export file for all Vue directives
 */

// Permission directive
export {
    permissionDirective,
    setupPermission,
    usePermission,
    install as installPermission
} from './permission';

// Focus directive
export {
    focusDirective,
    useFocus,
    install as installFocus
} from './focus';
