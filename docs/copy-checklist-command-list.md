# Copy Checklist + Command List (Laravel PMS)

?? ????? ?????? ????? ???? Laravel project-? ?????? project ???? ?? ?? copy ???? ??? ??? ??? command run ???? ????

## 1) Copy Checklist

### Must Copy

- `AGENTS.md`
- `bootstrap/app.php`
- `config/multitenancy.php`
- `routes/tenant.php`
- `routes/super-admin.php`
- `routes/super-admin-api.php`
- `routes/api.php`
- `app/Models/Tenant.php`
- `app/Http/Middleware/` (custom middleware files)
- `app/Modules/` (all business modules)
- `resources/js/app.ts`
- `resources/js/Pages/`
- `resources/js/Stores/`
- `resources/js/Composables/`
- `resources/js/Types/`
- `resources/js/Utils/Mappers/`
- `composer.json`
- `package.json`
- `.env.example`

### Do Not Copy

- `.env`
- `vendor/`
- `node_modules/`
- `storage/logs/`
- `bootstrap/cache/`
- `.git/`

## 2) Command List (Fresh Project Setup)

## A. New Project + Base Install

```bash
composer create-project laravel/laravel pms-new
cd pms-new
npm install
```

## B. Required Packages (if missing)

```bash
composer require inertiajs/inertia-laravel laravel/sanctum spatie/laravel-multitenancy spatie/laravel-permission spatie/laravel-data
npm install vue @inertiajs/vue3 @vitejs/plugin-vue typescript
```

## C. Copy Files/Folders from Old Project

Windows PowerShell example (old project path adjust ??? ???):

```powershell
$old = "D:\laragon\www\pms-test"
$new = "D:\laragon\www\pms-new"

Copy-Item "$old\AGENTS.md" "$new\AGENTS.md" -Force
Copy-Item "$old\bootstrap\app.php" "$new\bootstrap\app.php" -Force
Copy-Item "$old\config\multitenancy.php" "$new\config\multitenancy.php" -Force

Copy-Item "$old\routes\tenant.php" "$new\routes\tenant.php" -Force
Copy-Item "$old\routes\super-admin.php" "$new\routes\super-admin.php" -Force
Copy-Item "$old\routes\super-admin-api.php" "$new\routes\super-admin-api.php" -Force
Copy-Item "$old\routes\api.php" "$new\routes\api.php" -Force

Copy-Item "$old\app\Models\Tenant.php" "$new\app\Models\Tenant.php" -Force
Copy-Item "$old\app\Http\Middleware\*" "$new\app\Http\Middleware\" -Recurse -Force
Copy-Item "$old\app\Modules\*" "$new\app\Modules\" -Recurse -Force

Copy-Item "$old\resources\js\app.ts" "$new\resources\js\app.ts" -Force
Copy-Item "$old\resources\js\Pages\*" "$new\resources\js\Pages\" -Recurse -Force
Copy-Item "$old\resources\js\Stores\*" "$new\resources\js\Stores\" -Recurse -Force
Copy-Item "$old\resources\js\Composables\*" "$new\resources\js\Composables\" -Recurse -Force
Copy-Item "$old\resources\js\Types\*" "$new\resources\js\Types\" -Recurse -Force
Copy-Item "$old\resources\js\Utils\Mappers\*" "$new\resources\js\Utils\Mappers\" -Recurse -Force

Copy-Item "$old\composer.json" "$new\composer.json" -Force
Copy-Item "$old\package.json" "$new\package.json" -Force
Copy-Item "$old\.env.example" "$new\.env.example" -Force
```

## D. Re-install Dependencies

```bash
composer install
npm install
```

## E. Environment + App Key

```bash
cp .env.example .env
php artisan key:generate
```

????? `.env` ? DB config ??? (example: `pms_landlord`)?

## F. Database + Cache Clear

```bash
php artisan migrate
php artisan optimize:clear
```

## G. Run Project

```bash
php artisan serve
npm run dev
```

## H. Verify

```bash
php artisan route:list
php artisan test
```

## 3) Quick Validation Checklist

- [ ] Tenant domain route load ?????
- [ ] Admin domain route load ?????
- [ ] Login ??? ????
- [ ] API responses `{ status, data, message }` format follow ????
- [ ] `currentTenant` error ???? ??

## 4) Common Fix

??? `Target class [currentTenant] does not exist` ???:

- Tenant middleware global `web` stack-? ??? ??
- Tenant-specific middleware ???? tenant routes-? apply ???

---

?? checklist follow ???? ?????? project ???? clean ???? ???? project bootstrap ??? ?????
