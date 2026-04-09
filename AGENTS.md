# AI Coding Rules

## Stack
- Laravel 12, PHP 8.3
- Vue 3 + TypeScript + Inertia.js v2
- Pinia, Tailwind CSS
- Sanctum session auth

## Core Rules
1. Always analyze existing code before writing new code.
2. Match exact naming, folder structure, and coding style from existing files.
3. Backend module pattern: `app/Modules/[Module]/...`
4. API response format must be:
   - `{ status: 1|0, data: ..., message: string }`
5. Do not add new libraries unless explicitly requested.
6. Naming:
   - PHP: snake_case fields, class names PascalCase
   - TS/JS: camelCase variables/functions, PascalCase types/interfaces
7. `Helpers/` = pure JS/TS helper functions
8. `Composables/` = Vue reactive logic
9. `Utils/` = utility + formatting + validation functions
10. `Plugins/` = Vue plugins/directives
11. Enums live in `app/Enums` (not in module folders)
12. Frontend pages: `resources/js/Pages/[Module]/[Feature]/*`
    (e.g. `Pages/FrontDesk/Reservation/`, `Pages/FrontDesk/Hotel/`)

13. Mappers live in `resources/js/Utils/Mappers/` (lowercase filenames, e.g. `hotel.ts`, `reservation.ts`)

## Routing Rules
- NEVER use Ziggy or `route()` helper in frontend.
- NEVER use `import { route } from 'ziggy-js'`.
- For navigation use Inertia:
  - `router.visit('/path')`
- For form submit use Inertia:
  - `router.post('/path', data)`
  - `router.put('/path', data)`
  - `router.delete('/path')`
- Always use hardcoded URL strings or constants.

## Must Analyze First (Frontend)
- `resources/js/app.ts`

Before generating any Vue/Store/Composable/Plugin/global setup code:
1. Read and follow `resources/js/app.ts`.
2. Match existing:
   - Inertia app initialization
   - plugin registration
   - global component registration
   - page resolver/layout pattern
3. Do not introduce conflicting setup logic.

## Reference Files (Current Project)
- API Controller: `app/Modules/FrontDesk/Controllers/Api/V1/ReservationController.php`
- Web Controller: `app/Modules/FrontDesk/Controllers/Web/ReservationController.php`
- Service: `app/Modules/FrontDesk/Services/ReservationService.php`
- Store: `resources/js/Stores/FrontDesk/reservationStore.ts`
- Composable: `resources/js/Composables/FrontDesk/useReservations.ts`
- Page: `resources/js/Pages/FrontDesk/Reservation/Index.vue`
- Types: `resources/js/Types/FrontDesk/reservation.ts`
- Mappers: `resources/js/Utils/Mappers/reservation.ts`
- Shared Table: `resources/js/Components/Table.vue`

## Output Rules
- Keep output practical and implementation-first.
- If generating file content in chat, provide one file at a time.
- Start each generated file with:
  - `// FILE: path/to/file`

Follow this AGENTS.md strictly.
