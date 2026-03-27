# PMS Frontend - Future Modules

This folder contains utility modules that will be used as the project grows.

## 📁 Folder Structure

```
resources/js/
├── Utils/          # Utility functions (date, format, validation, etc.)
├── Plugins/        # Vue plugins (toast, confirm, directives)
└── Styles/         # SCSS variables and mixins
```

---

## 🔮 When to Use

### **Utils/**

Use when you need reusable utility functions:

```typescript
// Example: When you have many date formatting needs
import { formatDate, formatCurrency } from '@/Utils';

const formattedDate = formatDate('2024-01-15');
const formattedPrice = formatCurrency(1500);
```

**Current Files:**
- `date.ts` - Date formatting utilities
- `format.ts` - Currency, number formatting
- `validation.ts` - Form validation helpers
- `storage.ts` - LocalStorage/SessionStorage helpers
- `constants.ts` - App-wide constants

---

### **Plugins/**

Use when you need global Vue plugins:

```typescript
// app.ts
import { toast } from '@/Plugins';
app.use(toast);

// In components
$toast.success('Saved!');
$toast.error('Failed!');
```

**Current Files:**
- `toast.ts` - Toast notifications (not implemented)
- `confirm.ts` - Confirmation dialogs (not implemented)
- `directives/` - Custom directives (permission, focus, etc.)

**When to Enable:**
- When you need toast notifications across the app
- When you need confirmation dialogs
- When you need custom directives

---

### **Styles/**

Use when you need global SCSS variables and mixins:

```vue
<style lang="scss">
@import '@/Styles/main.scss';

.my-component {
    @include flex-center;
    color: $primary-color;
}
</style>
```

**Current Files:**
- `variables.scss` - Color, spacing, typography variables
- `mixins.scss` - Reusable CSS mixins
- `main.scss` - Main SCSS entry point

**When to Enable:**
- When you have consistent styling across components
- When you need theme customization
- When Tailwind CSS is not enough

---

## 🚀 Implementation Checklist

### Utils/
- [ ] Implement `formatDate()` function
- [ ] Implement `formatCurrency()` function
- [ ] Implement `validateEmail()` function
- [ ] Use in components

### Plugins/
- [ ] Implement toast notification system
- [ ] Implement confirmation dialog
- [ ] Create permission directive
- [ ] Register in app.ts

### Styles/
- [ ] Define color palette
- [ ] Create common mixins
- [ ] Import in main CSS file
- [ ] Use in components

---

## 📝 Note

**Don't delete these folders!** They are intentionally kept for future use.
As the project grows, these utilities will become essential for maintainability.

**Current Priority:**
1. ✅ Core functionality (Composables, Stores, API) - **DONE**
2. ⏳ UI Components (AppButton, AppInput, etc.) - **TODO**
3. ⏳ Utils/Plugins/Styles - **Future**
