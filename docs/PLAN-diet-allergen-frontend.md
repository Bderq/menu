# 📋 PLAN: Diet & Allergen Frontend Integration

## Context
The goal is to display `DietTypes` and `Allergen` information for each product in the frontend menu UI (Inertia/React). We will use the existing "asymmetric sticker" system located to the right of the product image in the detail drawer.

---

## 🏗️ Phase 1: Data Preparation (Backend)
- **Agent:** `backend-specialist`
- **Task:** Update `MenuService.php` to include `diet_types` and `allergens` relationships in the `formatProduct` method.
- **Details:**
    - Load `dietTypes` and `allergens` in the `getFormattedMenuData` query.
    - Map these relations to a structured array in `formatProduct`: `['name' => 'Vegan', 'color' => '#00ff00', 'icon' => 'leaf']`.

---

## 🎨 Phase 2: React Component Update (Frontend)
- **Agent:** `frontend-specialist`
- **Task:** Modify `resources/js/Pages/Menu/Index.jsx` to render the stickers.
- **Details:**
    - Update the `selectedItem` detail view (vertical info column).
    - Render `diet_types` first (positive traits).
    - Render `allergens` below them (safety traits).
    - **Styling:** Use a solid background with the provided `color`.
    - **Contrast:** Implement a JS version of the luminance-based contrast logic (`getTextColor`) to ensure black or white text for readability over different colors.
    - **Brutalist Style:** Use a heavy border and slight rotation to match the current sticker aesthetic.

---

## 🔍 Phase 3: Verification & Polish
- **Agent:** `orchestrator`
- **Checklist:**
    - [ ] Ensure all selected labels appear in the detail drawer.
    - [ ] Verify that colors match the matrix defined in the admin panel.
    - [ ] Test with products having multiple labels (check vertical stacking).
    - [ ] Confirm no regressions in the regular tag system (#acılı, #soğuk vb.).

---

## 🚀 Execution Guide
1. Run `python .agent/scripts/lint_runner.py` after backend changes.
2. Run `npm run dev` to verify React compilation.
3. Perform manual audit in browser.
