# AI Coding Rules

## Stack
- Laravel 12, PHP 8.3
- Vue 3 + TypeScript + Inertia.js v2
- Pinia, Tailwind CSS
- Sanctum session auth

## Rules
1. Always analyze existing code before writing anything new
2. Match exact coding style, naming, and patterns from existing files
3. Follow Module structure: app/Modules/[Module]/Controllers/Api/V1/
4. API response format always: { status: 1|0, data: ..., message: string }
5. No new libraries unless explicitly asked
6. snake_case for PHP, camelCase for TypeScript/JS
7. Helpers/ = Pure JavaScript/TypeScript functions
8. Composables/ = Vue dependent (reactive)
9. Utils/ = Utility functions
10. Plugins/ = Vue plugins
11. Enums live in app/Enums (not inside Modules)
12. Pages live in resources/js/Pages/[FEATURE_NAME_PLURAL]/ (no module folder)
13. Mappers live in resources/js/Utils/Mappers/ (Pascal folder)


## Reference Files (Always Follow These Patterns)
- Controller: app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php
- Service: app/Modules/FrontDesk/Services/ReservationService.php
- Store: resources/js/Stores/FrontDesk/reservationStore.ts
- Composable: resources/js/Composables/FrontDesk/useReservations.ts
- Page: resources/js/Pages/Reservations/Index.vue
- Types: resources/js/Types/FrontDesk/reservation.ts
- Mappers: resources/js/Utils/Mappers/reservation.ts

## Output Rules
- Code only — no unnecessary explanation
- One file at a time
- Start each file with: `// FILE: path/to/file`
```



Follow the rules in AGENTS.md and analyze the reference files before writing.
