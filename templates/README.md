# Templates - Ready to Use

## 📁 Structure

```
templates/
├── backend/
│   ├── migration.php          # Database migration
│   ├── model.php              # Eloquent model
│   ├── service.php            # Service layer
│   ├── api-controller.php     # RESTful API controller
│   ├── web-controller.php     # Inertia web controller
│   ├── form-request.php       # Form validation
│   ├── api-routes.php         # API routes snippet
│   └── web-routes.php         # Web routes snippet
├── frontend/
│   ├── types.ts               # TypeScript types
│   ├── mappers.ts             # API ↔ Frontend mappers
│   ├── store.ts               # Pinia store (in root templates/)
│   ├── composable.ts          # Vue composable (in root templates/)
│   ├── page-index.vue         # Index page with table
│   ├── page-create.vue        # Create form page
│   ├── page-edit.vue          # Edit form page
│   └── page-show.vue          # Show detail page
├── QUICK_REFERENCE.md         # Quick copy-paste guide
└── store.ts                   # (Already exists in root)
└── composable.ts              # (Already exists in root)
```

---

## 🚀 How to Use

### Option 1: Use Master Prompt (Recommended - Easiest)

1. Open: `document/MASTER_CRUD_PROMPT.txt`
2. Copy the prompt
3. Replace placeholders with your feature details
4. Paste to AI
5. **ALL 19 files generated automatically!**

✅ **No manual file creation needed!**

---

### Option 2: Use Templates Manually

1. Copy template file
2. Find & replace placeholders
3. Save to your project

#### Example:

```bash
# Copy store template
cp templates/store.ts resources/js/Stores/FrontDesk/guestStore.ts

# Find & Replace:
[Item]        → Guest
[Module]      → FrontDesk
[Feature]     → guest
[feature]     → guests
[item]        → guest
[items]       → guests
/module/      → /front-desk/
```

---

## 📝 Placeholder Guide

### Backend Placeholders

| Placeholder | Replace With | Example |
|------------|--------------|---------|
| `[MODULE]` | Module name | FrontDesk, Admin |
| `[MODEL]` | Model class name | Hotel, Room, Guest |
| `[TABLE]` | Database table | hotels, rooms, guests |
| `[SERVICE]` | Service class | HotelService, RoomService |
| `[CONTROLLER]` | Controller name | Hotel, Room, Guest |
| `[COLUMNS]` | Migration columns | Your field definitions |
| `[FILLABLE]` | Fillable fields | name, code, email |
| `[CASTS]` | Type casts | status enum, decimals |
| `[VALIDATION_RULES]` | Validation rules | required, unique, email |

### Frontend Placeholders

| Placeholder | Replace With | Example |
|------------|--------------|---------|
| `[MODULE_NAME]` | Module namespace | FrontDesk, Admin |
| `[MODEL_NAME]` | TypeScript type | Hotel, Room, Guest |
| `[FEATURE_NAME]` | Feature name | Hotel, Room, Guest |
| `[model_name]` | Lowercase singular | hotel, room, guest |
| `[feature]` | Lowercase plural | hotels, rooms, guests |
| `[model_name_plural]` | CamelCase plural | hotels, rooms, guests |
| `[feature_name]` | Translation key | hotel, room, guest |
| `[feature_name_plural]` | Permission name | hotels, rooms, guests |
| `[web_route]` | Web route path | hotels, rooms, guests |
| `[api-prefix]/[feature]` | API path | front-desk/hotels |
| `[DISPLAY_FIELD]` | Display field | name, firstName, number |
| `[COLUMNS]` | Table columns count | 2, 3, 4 |
| `[FIELDS]` | Form/API fields | Your field list |

---

## ⚡ Quick Examples

### Example 1: Create Guest Module

**Using Master Prompt:**
```
Copy document/MASTER_CRUD_PROMPT.txt
Replace:
  [FEATURE_NAME] → Guest
  [MODULE_NAME] → FrontDesk
  [MODEL_NAME] → Guest
  [TABLE_NAME] → guests
  [API Route] → /front-desk/guests
  [Web Route] → /guests
  Add fields, relationships, enums
Paste to AI
Done!
```

**Using Templates Manually:**
```bash
# Backend
cp templates/backend/migration.php database/migrations/xxxx_xx_xx_create_guests_table.php
cp templates/backend/model.php app/Modules/FrontDesk/Models/Guest.php
cp templates/backend/service.php app/Modules/FrontDesk/Services/GuestService.php
cp templates/backend/api-controller.php app/Modules/FrontDesk/Controllers/Api/V1/GuestController.php
cp templates/backend/web-controller.php app/Modules/FrontDesk/Controllers/Web/GuestController.php
cp templates/backend/form-request.php app/Modules/FrontDesk/Http/Requests/GuestStoreRequest.php

# Find & Replace in all files:
[MODULE] → FrontDesk
[MODEL] → Guest
[TABLE] → guests
[SERVICE] → GuestService
[CONTROLLER] → Guest

# Frontend
cp templates/frontend/types.ts resources/js/Types/FrontDesk/guest.ts
cp templates/frontend/mappers.ts resources/js/Utils/Mappers/guest.ts
cp templates/store.ts resources/js/Stores/FrontDesk/guestStore.ts
cp templates/composable.ts resources/js/Composables/FrontDesk/useGuests.ts
cp templates/frontend/page-index.vue resources/js/Pages/FrontDesk/Guest/Index.vue
cp templates/frontend/page-create.vue resources/js/Pages/FrontDesk/Guest/Create.vue
cp templates/frontend/page-edit.vue resources/js/Pages/FrontDesk/Guest/Edit.vue
cp templates/frontend/page-show.vue resources/js/Pages/FrontDesk/Guest/Show.vue

# Find & Replace in all files:
[MODULE_NAME] → FrontDesk
[MODEL_NAME] → Guest
[FEATURE_NAME] → Guest
[model_name] → guest
[feature_plural] → guests
[web_route] → guests
```

---

## 📋 File Generation Checklist

### Backend (10 files)
- [ ] Migration
- [ ] Model
- [ ] Service
- [ ] API Controller
- [ ] Web Controller
- [ ] Store Request
- [ ] Update Request
- [ ] Resource
- [ ] API Routes
- [ ] Web Routes

### Frontend (8 files)
- [ ] Types
- [ ] Mappers
- [ ] Pinia Store
- [ ] Composable
- [ ] Index.vue
- [ ] Create.vue
- [ ] Edit.vue
- [ ] Show.vue

### Tests (1 file)
- [ ] Feature Test

**Total: 19 files**

---

## 🎯 After File Generation

```bash
# 1. Run migration
php artisan migrate

# 2. Clear config cache
php artisan config:clear

# 3. Build frontend
npm run dev
# or
npm run build

# 4. Test in browser
# Visit: http://localhost:8000/[web-route]
```

---

## 💡 Tips

### 1. Always Use Master Prompt (Easiest)
- One prompt → All 19 files
- No manual copy-paste
- No find-replace errors
- Perfect code every time

### 2. Backup Before Generation
```bash
# Backup your project first
git add .
git commit -m "Backup before new module"
```

### 3. Test After Generation
```bash
# Check for syntax errors
npm run build

# Check backend
php artisan route:list
php artisan migrate --pretend
```

### 4. Common Issues
- **Pinia not working** → Check app.ts has Pinia setup
- **Imports failing** → Verify paths match your structure
- **API 404** → Check routes are registered
- **Permission errors** → Update permission checks

---

## 📚 Related Documentation

- `document/MASTER_CRUD_PROMPT.txt` - Master prompt for AI
- `docs/PINIA_PATTERN.md` - Pinia state management guide
- `docs/NEW_PROJECT_SETUP.md` - New project setup guide
- `docs/SIMPLE_PROMPT.md` - Simplified prompt
- `AGENTS.md` - AI coding rules

---

**Last Updated:** April 11, 2026
