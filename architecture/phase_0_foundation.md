# HumaNode HRMS - Phase 0 Foundation Architecture (Laravel 11)

This document contains the production-ready architecture blueprint for **Phase 0: Project Foundation** of the HumaNode HRMS SaaS platform. It establishes the enterprise folder structure, database standards, multi-tenant resolver pattern, repository & service layer patterns, API versioning, audit logging, queue configuration, and standardized API response mechanisms.

---

## STEP 1 — LARAVEL PROJECT CREATION COMMANDS

Run the following commands to scaffold the HumaNode HRMS backend directory:

```bash
# Create Laravel 11 Project
composer create-project laravel/laravel:^11.0 backend

cd backend

# Install Primary Packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require predis/predis
composer require maatwebsite/excel:^3.1
composer require intervention/image:^3.0

# Install Developer Debugging Tools
composer require barryvdh/laravel-debugbar --dev
composer require laravel/telescope --dev
composer require nunomaduro/collision --dev

# Publish Vendor Assets
php artisan sanctum:stateful-hosts
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
php artisan telescope:install
```

### PostgreSQL, Redis, Queue Config (`.env`)
Configure the local server environment by editing the `.env` file:

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=humanode_hrms
DB_USERNAME=postgres
DB_PASSWORD=secret

QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
```

---

## STEP 2 — DESIGN ENTERPRISE FOLDER STRUCTURE

In Laravel 11, the directory structure is highly streamlined. To support enterprise-scale systems, create these directories inside `app/`:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── V1/                 # Version 1 API Controllers
│   │   │   └── V2/                 # Version 2 API Controllers
│   │   └── Web/                    # Blade-based Web Panel Controllers
│   └── Middleware/                 # Custom routing middleware (TenantResolver)
├── Models/                         # Eloquent Models (User, Employee, Company)
├── Repositories/
│   ├── Contracts/                  # Core Interfaces defining database operations
│   └── Eloquent/                   # Concrete Eloquent implementations of Interfaces
├── Services/                       # Service Layer containing all business logic
├── Actions/                        # Single-purpose action classes (e.g. ProcessClockIn)
├── DTOs/                           # Data Transfer Objects for type-safe data passing
├── Traits/                         # Reusable traits (ApiResponseTrait, TenantScoped)
├── Helpers/                        # Global helper functions
├── Enums/                          # Strict PHP Enums (Role, Status, DocumentType)
├── Events/                         # Custom application triggers
├── Listeners/                      # Listeners handling events asynchronously
├── Notifications/                  # Notification classes (Mail, Database, SMS)
├── Jobs/                           # Queueable background tasks (Payroll, PDF Generation)
├── Policies/                       # Model-specific Spatie authorization policies
├── Exceptions/                     # Custom exception definitions (ApiException)
└── Rules/                          # Complex custom validation rule logic
```

### Purpose of Key Folders
*   **Repositories:** Abstracts data query operations away from business logic. Allows changing the database engine easily without altering services.
*   **Services:** The core brain of the app. All calculations, third-party calls, and business decisions live here.
*   **Actions:** Dedicated classes for simple operations that don't need a full service (e.g., `GenerateEmployeeId`).
*   **DTOs:** Validates and structures request arrays into type-safe objects before passing them to services.
*   **Traits:** Shared behaviors, such as formatting API response outputs.
*   **Enums:** Restricts states (e.g., status maps for requests) to prevent magic string failures.

---

## STEP 3 — DATABASE DESIGN STANDARDS

### Naming Conventions
*   **Tables:** Plural, snake_case (e.g., `companies`, `leave_requests`).
*   **Foreign Keys:** Singular `table_id` (e.g., `company_id`, `employee_id`) using `UUID` constraints.

### Standard Database Fields
Every table must implement these fields. Here is the blueprint migration trait structure:

```php
abstract class TenantBaseMigration extends Migration
{
    protected function addStandardColumns(Blueprint $table)
    {
        $table->uuid('id')->primary();
        $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
        $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
        
        // Audit Fields
        $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
        
        $table->timestamps();
        $table->softDeletes(); // Adds deleted_at
    }
}
```

---

## STEP 4 — MULTI-TENANT ARCHITECTURE

Logical separation is handled via a global scope filtering queries by the authenticated user's `company_id`.

### Core Tables Migration

```php
// Companies Table
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('subdomain')->unique();
    $table->string('logo_path')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

// Branches Table
Schema::create('branches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
    $table->string('name');
    $table->string('city');
    $table->timestamps();
    $table->softDeletes();
});
```

### Tenant Global Scope (`app/Traits/TenantScoped.php`)

```php
namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait TenantScoped
{
    protected static function bootTenantScoped()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
```

### Tenant Scope Implementation (`app/Models/Scopes/TenantScope.php`)

```php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && auth()->user()->company_id) {
            $builder->where($model->getTable() . '.company_id', auth()->user()->company_id);
        }
    }
}
```

---

## STEP 5 — REPOSITORY PATTERN

### 1. Repository Interface (`app/Repositories/Contracts/EmployeeRepositoryInterface.php`)

```php
namespace App\Repositories\Contracts;

interface EmployeeRepositoryInterface
{
    public function all(array $filters = []);
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
```

### 2. Concrete Repository (`app/Repositories/Eloquent/EmployeeRepository.php`)

```php
namespace App\Repositories\Eloquent;

use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function all(array $filters = [])
    {
        return Employee::query()
            ->when($filters['department_id'] ?? null, fn($q, $id) => $q->where('department_id', $id))
            ->get();
    }

    public function find(int $id)
    {
        return Employee::findOrFail($id);
    }

    public function create(array $data)
    {
        return Employee::create($data);
    }

    public function update(int $id, array $data)
    {
        $employee = $this->find($id);
        $employee->update($data);
        return $employee;
    }

    public function delete(int $id)
    {
        return $this->find($id)->delete();
    }
}
```

---

## STEP 6 — SERVICE LAYER DESIGN

The service handles all business decisions, keeping controllers thin and clean.

### Employee Service (`app/Services/EmployeeService.php`)

```php
namespace App\Services;

use App\Repositories\Contracts\EmployeeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(protected EmployeeRepositoryInterface $employeeRepo) {}

    public function onboardEmployee(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Business Logic: Generate employee ID
            $data['employee_id'] = 'EMP-' . date('Y') . '-' . rand(1000, 9999);
            
            // Create user login credentials
            $user = \App\Models\User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => bcrypt('Welcome@HumaNode123'),
                'role' => 'Employee',
            ]);

            $data['user_id'] = $user->id;

            return $this->employeeRepo->create($data);
        });
    }
}
```

### Thin Controller (`app/Http/Controllers/Api/V1/EmployeeController.php`)

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected EmployeeService $employeeService) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'department_id' => 'required|exists:departments,id',
        ]);

        $employee = $this->employeeService->onboardEmployee($validated);

        return $this->successResponse('Employee onboarded successfully', $employee, 201);
    }
}
```

---

## STEP 7 — API VERSIONING

API routing is versioned to allow backward compatibility for our mobile applications.

### Routing Config (`routes/api.php` in Laravel 11)
Laravel 11 declares routes inside `routes/api.php`. Define route namespaces like this:

```php
use Illuminate\Support\Facades\Route;

// API Version 1
Route::prefix('v1')->namespace('App\Http\Controllers\Api\V1')->group(function () {
    Route::post('login', 'AuthController@login');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('employees', 'EmployeeController@index');
        Route::post('attendance/clock-in', 'AttendanceController@clockIn');
    });
});

// API Version 2 (For future upgrades)
Route::prefix('v2')->namespace('App\Http\Controllers\Api\V2')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('attendance/clock-in', 'AttendanceController@clockInV2');
    });
});
```

---

## STEP 8 — AUTHENTICATION & AUTHORIZATION FOUNDATION

### Sanctum Login Controller Logic

```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', [], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return $this->successResponse('Login successful', [
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }
}
```

### Roles and Permissions Database Seeder (`database/seeders/RolesSeeder.php`)

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        Permission::create(['name' => 'clock_in']);
        Permission::create(['name' => 'approve_leaves']);
        Permission::create(['name' => 'run_payroll']);
        Permission::create(['name' => 'manage_users']);

        // Create Roles and Assign Permissions
        Role::create(['name' => 'Super Admin']);
        
        $companyAdmin = Role::create(['name' => 'Company Admin']);
        $companyAdmin->givePermissionTo(Permission::all());

        $hr = Role::create(['name' => 'HR']);
        $hr->givePermissionTo(['clock_in', 'approve_leaves', 'run_payroll']);

        $manager = Role::create(['name' => 'Manager']);
        $manager->givePermissionTo(['clock_in', 'approve_leaves']);

        $employee = Role::create(['name' => 'Employee']);
        $employee->givePermissionTo(['clock_in']);
    }
}
```

---

## STEP 9 — AUDIT LOGGING & ACTIVITY TRACKING

Integrate `spatie/laravel-activitylog` inside Eloquent models to automate logging actions.

### Model Config (`app/Models/Employee.php`)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Employee extends Model
{
    use LogsActivity;

    protected $fillable = ['first_name', 'last_name', 'email', 'department_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'department_id'])
            ->logOnlyDirty()
            ->useLogName('employee_master');
    }
}
```

---

## STEP 10 — EXCEPTION HANDLING ARCHITECTURE

In Laravel 11, customize error handling in `bootstrap/app.php` using the `$exceptions->renderable` closures instead of editing `app/Exceptions/Handler.php`.

### Custom ApiException Class (`app/Exceptions/ApiException.php`)

```php
namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct(string $message = "API Error occurred", int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
```

### Global Custom Renderer (`bootstrap/app.php`)

```php
use App\Exceptions\ApiException;
use Illuminate\Http\Request;

return Application::configure(basename: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ApiException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => []
            ], $e->getCode());
        });
    })->create();
```

---

## STEP 11 — QUEUE & BACKGROUND JOBS

Configure asynchronous jobs using Redis database workers.

### Custom Queue Job (`app/Jobs/ProcessPayroll.php`)

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayroll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $payrollDetails) {}

    public function handle()
    {
        // Background calculations: PF deduction, Tax calculations, slip rendering
    }
}
```

---

## STEP 12 — NOTIFICATION ARCHITECTURE

Use standard channels (Mail, DB, and Broadcast).

### Leave Approved Notification (`app/Notifications/LeaveApproved.php`)

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected $leaveRequest) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Leave Request Approved')
            ->line('Your leave request starting ' . $this->leaveRequest->start_date . ' has been approved.')
            ->action('View Request', url('/leaves'));
    }

    public function toArray($notifiable)
    {
        return [
            'leave_id' => $this->leaveRequest->id,
            'message' => 'Your leave starting ' . $this->leaveRequest->start_date . ' was approved.',
        ];
    }
}
```

---

## STEP 13 — MIGRATION STRUCTURE

To ensure database consistency during automated builds, order migrations using prefixed groupings:

```text
database/migrations/
├── 2026_01_01_000000_create_companies_table.php       # Core Tenants first
├── 2026_01_01_000010_create_branches_table.php        # Branches next
├── 2026_01_01_000020_create_users_table.php           # Users depend on company
├── 2026_01_01_000030_create_permission_tables.php     # Spatie rules
├── 2026_01_01_000040_create_employees_table.php       # HR Profiles
├── 2026_01_01_000050_create_attendance_logs_table.php # Daily Operations
└── 2026_01_01_000060_create_leave_requests_table.php  # Leaves
```

---

## STEP 14 — SEEDER STRUCTURE

Create seed configurations inside `database/seeders/DatabaseSeeder.php` with appropriate sequence execution:

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesSeeder::class,          // Initialize roles and permissions
            CompanySeeder::class,        // Add test tenant companies
            BranchSeeder::class,         // Add company offices
            DepartmentSeeder::class,     // Add corporate departments
            UserSeeder::class,           // Create baseline managers and employees
        ]);
    }
}
```

---

## STEP 15 — CODING STANDARDS

*   **Syntax styling:** Adhere strictly to **PSR-12** standards.
*   **Method Naming:** Use `camelCase` for variables and function methods.
*   **Table Naming:** Use `snake_case` for database column fields.
*   **Controller Boundaries:** Keep controllers limited to route requests, validation mappings, and DTO initialization. Move all business operations to services.

---

## STEP 16 — API RESPONSE STANDARD

Create the format trait in `app/Traits/ApiResponseTrait.php`:

```php
namespace App\Traits;

trait ApiResponseTrait
{
    protected function successResponse(string $message, $data = [], int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    protected function errorResponse(string $message, array $errors = [], int $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
```
