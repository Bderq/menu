# Product Diet Types & Allergens

## Goal
Canlı veritabanını riske atmadan, Ürünler (Products) için dinamik olarak yönetilebilen (Diyet Türü ve Alerjen) çoka çok (Many-to-Many) ilişki mimarisini ve admin paneli (Filament) arayüzlerini oluşturmak.

## Tasks
- [ ] Task 1: Create independent tables migration for `allergens` and `diet_types` (columns: `name`, `icon`, `color`) → Verify: Migration file exists and handles safe table creation.
- [ ] Task 2: Create pivot tables migration for `allergen_product` and `diet_type_product` → Verify: Migration uses correct foreign keys with `cascadeOnDelete` so dangling records are cleaned.
- [ ] Task 3: Create `Allergen` and `DietType` Eloquent Models → Verify: Models have `$guarded = []` and `BelongsToMany` relation to `Product`.
- [ ] Task 4: Update `Product` Model → Verify: Added `allergens()` and `dietTypes()` `BelongsToMany` relationships.
- [ ] Task 5: Create `AllergenResource` and `DietTypeResource` for Admin Panel → Verify: Admin can create/edit new allergens and diet types in the sidebar.
- [ ] Task 6: Update `ProductResource` Form/Infolist → Verify: Added `Select::make('allergens')->multiple()->relationship('allergens', 'name')` and same for `dietTypes` to the Product create/edit form.

## Done When
- [ ] Migrations run cleanly without dropping any existing tables in the live database.
- [ ] Admin can add "Vegan", "Glutensiz", "Fıstık", "Süt" gibi etiketler.
- [ ] Admin can attach multiple tags to a Master Product via Filament forms.
- [ ] Changes apply dynamically without pushing new code.

## Notes
- "Proje yayında" (Live Project) olduğu için migration'lar kesinlikle `dropTable` veya mevcut `products` tablosu isimlerini/kolonlarını silecek komutlar İÇERMEMELİDİR. Sadece `Schema::create` kullanılarak ek tablo oluşturulacaktır.
- Sadece `Admin` paneli işleri yapılacaktır, Frontend yapılmış varsayılacaktır.
