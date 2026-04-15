## 1. Backend Data Enrichment

- [x] 1.1 Update `app/Services/MenuService.php` to eager load `dietTypes` and `allergens` in the category tree query.
- [x] 1.2 Modify `formatProduct` in `MenuService.php` to include `diet_types` and `allergens` in the returned array.
- [x] 1.3 Verify JSON response in controller/API using a test or manual check.

## 2. Frontend Component Enhancements

- [x] 2.1 Implement `getContrastColor` helper function in `resources/js/Pages/Menu/Index.jsx`.
- [x] 2.2 Modify the vertical info column in the detail drawer to map over `selectedItem.diet_types`.
- [x] 2.3 Modify the vertical info column in the detail drawer to map over `selectedItem.allergens`.
- [x] 2.4 Apply brutalist rotation and solid background color based on DB values.

## 3. Polish & Verification

- [x] 3.1 Verify that standard tags (#etiket) still display correctly alongside new ones.
- [x] 3.2 Test contrasting on very light vs very dark backgrounds.
- [x] 3.3 Ensure the drawer remains scrollable and layout doesn't break with many tags.
