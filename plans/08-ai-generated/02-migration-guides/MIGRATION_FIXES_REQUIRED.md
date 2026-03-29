# 🔧 MIGRATION FIXES REQUIRED

**Date:** March 29, 2026
**Status:** Partially Complete - POS Migration Needs Fixes

---

## ✅ COMPLETED MIGRATIONS (13/16)

All migrations up to and including `night_audit_tables` have run successfully.

---

## ❌ CURRENT ISSUES

### `2026_03_19_000007_create_pos_integration_tables.php`

**Problem:** Foreign key constraints reference tables that don't exist yet.

**Required Order of Table Creation:**
1. pos_categories
2. pos_suppliers
3. pos_menu_items
4. pos_recipes
5. pos_inventory_items (references suppliers, categories)
6. pos_recipe_ingredients (references recipes, inventory_items)
7. pos_purchase_orders (references suppliers)
8. pos_purchase_order_items (references inventory_items)
9. pos_stock_movements (references inventory_items)
10. pos_waste_tracking (references inventory_items)
11. pos_stock_transfers (references outlets)
12. pos_stock_transfer_items (references inventory_items, stock_transfers)

**Current Issues:**
1. `pos_inventory_items.supplier_id` → references `pos_suppliers` (not created yet)
2. `pos_recipe_ingredients.inventory_item_id` → references `pos_inventory_items` (not created yet)
3. `pos_purchase_orders.supplier_id` → references `suppliers` instead of `pos_suppliers`

---

## 🔧 REQUIRED FIXES

### Fix 1: Reorder Table Creation

Move `pos_suppliers` BEFORE `pos_inventory_items`

### Fix 2: Fix Table References

Change `pos_purchase_orders`:
```php
// WRONG
$table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

// CORRECT
$table->foreignId('supplier_id')->constrained('pos_suppliers')->cascadeOnDelete();
```

### Fix 3: Remove Circular Dependencies

For tables that reference each other, use `unsignedBigInteger` instead of `foreignId` and add foreign keys in a later migration.

---

## 📋 ALTERNATIVE APPROACH

Instead of fixing the ordering, split the POS migration into 3 files:

1. `2026_03_19_000007_create_pos_basic_tables.php`
   - outlets, pos_terminals, pos_payment_methods, pos_mappings
   - pos_categories, pos_suppliers, pos_menu_items
   - pos_checks, pos_check_items, pos_transactions, pos_postings
   - pos_sync_logs, pos_revenue_summary

2. `2026_03_19_000008_create_pos_inventory_tables.php`
   - pos_inventory_items
   - pos_recipes, pos_recipe_ingredients
   - pos_purchase_orders, pos_purchase_order_items
   - pos_stock_movements, pos_waste_tracking
   - pos_stock_transfers, pos_stock_transfer_items

3. `2026_03_19_000009_create_hr_management_tables.php`
   - All HR tables

---

## ✅ NEXT STEPS

1. Fix the POS migration file ordering
2. Run remaining migrations:
   - HR management
   - Utility and tenant tables
3. Verify all 150+ tables are created
4. Create missing models
5. Test the application

---

*Migration Status: 81% Complete (13/16 files)*
*Estimated Time to Complete: 30 minutes*
