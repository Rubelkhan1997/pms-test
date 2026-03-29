# 🔄 Migration Guides

Database migration documentation, schemas, and reorganization notes.

---

## 📄 Documents

| File | Description | Tables | Status |
|------|-------------|--------|--------|
| [`COMPLETE_MIGRATION_GUIDE.md`](./COMPLETE_MIGRATION_GUIDE.md) | Complete migration reference | 150+ | Reference |
| [`MIGRATION_COMPLETION_SUMMARY.md`](./MIGRATION_COMPLETION_SUMMARY.md) | 62 new tables summary | 62 | Complete |
| [`MIGRATION_FIXES_REQUIRED.md`](./MIGRATION_FIXES_REQUIRED.md) | Required fixes documentation | - | Resolved |
| [`MIGRATION_REORGANIZATION_SUMMARY.md`](./MIGRATION_REORGANIZATION_SUMMARY.md) | Migration file mergers | - | Complete |

---

## 📊 Migration Statistics

### Total Migrations
- **Files:** 20+
- **Tables:** 150+
- **Extensions:** 62 tables (March 29, 2026)

### Categories Covered
1. **Core & Authentication** - Permissions, roles, users
2. **Multi-tenancy** - Tenants, subscriptions, databases
3. **FrontDesk** - Reservations, rooms, rates
4. **Guest** - Guest profiles, agents, history
5. **Housekeeping** - Tasks, maintenance, inspections
6. **POS** - Orders, menus, inventory
7. **HR** - Employees, attendance, payroll
8. **Accounting** - Invoices, payments, taxes
9. **Channel Manager** - OTA mappings, sync
10. **Night Audit** - Audit logs, reports

---

## 🔧 Migration Order

Migrations should run in this order:
1. Core/Authentication (001-010)
2. Multi-tenancy (011-020)
3. FrontDesk (021-040)
4. Guest (041-060)
5. Housekeeping (061-080)
6. POS (081-100)
7. HR (101-120)
8. Accounting (121-140)
9. Extensions (141-160)

---

## ⚠️ Note

These are **historical documents**. For current migration status:
```bash
php artisan migrate:status
```

---

**Generated:** March 2026 | **Status:** Archive
