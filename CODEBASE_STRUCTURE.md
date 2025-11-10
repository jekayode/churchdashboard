# Church Dashboard - Codebase Structure Overview

## Technology Stack
- **Framework**: Laravel 12.18.0
- **PHP**: 8.4.13
- **Frontend**: Blade Templates + Alpine.js 3.14.9 + Tailwind CSS 3.4.17
- **Database**: MySQL
- **Authentication**: Laravel Sanctum 4.1.1
- **File Storage**: Spatie Media Library 11.x
- **Excel Import/Export**: Maatwebsite Excel 3.1
- **Email**: Resend Laravel 0.22.0

## Project Architecture

### Directory Structure
```
app/
├── Console/Commands/          # Artisan commands
├── Http/
│   ├── Controllers/          # Request handlers (organized by feature)
│   │   ├── Api/              # API controllers
│   │   └── Auth/             # Authentication controllers
│   ├── Middleware/           # Custom middleware
│   ├── Requests/             # Form request validation classes
│   └── Resources/            # API resources
├── Jobs/                      # Queue jobs
├── Mail/                      # Mailable classes
├── Models/                    # Eloquent models
├── Notifications/             # Notification classes
├── Policies/                  # Authorization policies
├── Providers/                 # Service providers
└── Services/                  # Business logic services
```

## Core Models & Relationships

### User Model
- **Purpose**: System users (admins, pastors, leaders, members)
- **Key Relationships**:
  - `roles()` - BelongsToMany (with branch_id pivot)
  - `member()` - HasOne (links to Member profile)
  - `pastoredBranches()` - HasMany (branches where user is pastor)
- **Key Methods**:
  - `hasRole($roleName, $branchId)` - Check role
  - `getPrimaryRole()` - Get highest priority role
  - `getPrimaryBranch()` - Get user's primary branch
  - `isSuperAdmin()`, `isBranchPastor()`, etc. - Role checks

### Member Model
- **Purpose**: Church members (separate from User - represents people)
- **Key Relationships**:
  - `user()` - BelongsTo (optional User account)
  - `branch()` - BelongsTo
  - `departments()` - BelongsToMany
  - `smallGroups()` - BelongsToMany
  - `spouse()` - BelongsTo (self-referential)
- **Features**:
  - Media library integration (profile images)
  - Soft deletes
  - Profile completion tracking

### Branch Model
- **Purpose**: Church branches/locations
- **Key Relationships**:
  - `pastor()` - BelongsTo (User)
  - `members()` - HasMany
  - `ministries()` - HasMany
  - `smallGroups()` - HasMany
  - `events()` - HasMany
  - `expenses()` - HasMany
  - `projections()` - HasMany

### Role-Based Access Control

#### Role Hierarchy (Highest to Lowest)
1. `super_admin` - Full system access
2. `branch_pastor` - Branch-level admin
3. `ministry_leader` - Ministry management
4. `department_leader` - Department management
5. `church_member` - Regular member
6. `public_user` - Guest/visitor

#### Role Assignment
- Roles are assigned via `user_roles` pivot table
- Each role assignment includes `branch_id` (except super_admin)
- Users can have multiple roles across different branches

#### Authorization Patterns

**Middleware:**
- `role:role1,role2` - Check if user has any of the specified roles
- `branch.scope` - Automatically scope queries to user's branch
- `authorize.resource` - Auto-authorize resource actions

**Policies:**
- All policies extend `BasePolicy`
- Common methods:
  - `hasAdminPrivileges()` - Super Admin or Branch Pastor
  - `hasLeadershipPrivileges()` - Admin + Ministry/Department Leaders
  - `belongsToSameBranch()` - Check branch access
- Policies registered in `AppServiceProvider`

**Controller Authorization:**
```php
Gate::authorize('viewAny', Member::class);
// or
$this->authorize('update', $member);
```

## Service Layer Pattern

### Service Classes
Services contain business logic, keeping controllers thin:

- **ReportingService** - Dashboard stats, reports, comparisons
- **GuestRegistrationService** - Guest registration workflow
- **ImportExportService** - Data import/export operations
- **EmailCampaignService** - Email campaign management
- **CommunicationService** - SMS/Email communication
- **ProjectionService** - Financial projections
- **EnvironmentAwareEmailService** - Environment-specific email handling

### Service Pattern Example
```php
final class MemberService
{
    public function createMember(array $data): Member
    {
        // Business logic here
        return DB::transaction(function () use ($data) {
            // Create member
            // Send notifications
            // Update related records
        });
    }
}
```

## Routing Structure

### Route Organization
- **Web Routes** (`routes/web.php`):
  - Public routes (no auth)
  - Authenticated routes with role middleware
  - Role-specific route groups with prefixes

### Route Patterns
```php
// Role-based route groups
Route::middleware(['auth', 'verified', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Admin routes
    });

// Branch Pastor routes
Route::middleware(['auth', 'verified', 'role:branch_pastor,super_admin'])
    ->prefix('pastor')
    ->name('pastor.')
    ->group(function () {
        // Pastor routes
    });
```

### API Routes
- Located in `routes/api.php`
- Uses Sanctum for authentication
- Stateful API requests (SPA)

## Controller Patterns

### Standard Controller Structure
```php
final class MemberController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Member::class);
        
        // Branch scoping for non-super admins
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()->id;
            // Filter by branch
        }
        
        // Query, filter, paginate
        return response()->json([...]);
    }
}
```

### Common Patterns
1. **Authorization First** - Always check permissions
2. **Branch Scoping** - Filter by branch for non-super admins
3. **Eager Loading** - Use `with()` to prevent N+1 queries
4. **JSON Responses** - Most controllers return JSON for SPA
5. **Error Handling** - Try-catch with proper logging

## Database Patterns

### Key Tables
- `users` - System users
- `members` - Church members
- `branches` - Church branches
- `roles` - System roles
- `user_roles` - User-role assignments (with branch_id)
- `ministries` - Church ministries
- `departments` - Ministry departments
- `small_groups` - Small groups
- `events` - Church events
- `event_registrations` - Event registrations
- `event_reports` - Event attendance reports
- `expenses` - Branch expenses
- `projections` - Financial projections
- `email_campaigns` - Email campaigns
- `communication_logs` - Communication history

### Relationship Patterns
- Many-to-many with pivot tables (e.g., `member_departments`)
- Soft deletes on most models
- Timestamps on all tables
- Branch scoping on most resources

## Frontend Architecture

### View Organization
```
resources/views/
├── layouts/
│   ├── app.blade.php          # Main authenticated layout
│   └── guest.blade.php        # Public layout
├── dashboards/                # Role-specific dashboards
├── admin/                     # Super admin views
├── pastor/                    # Branch pastor views
├── ministry/                  # Ministry leader views
├── department/                # Department leader views
├── member/                    # Member views
└── public/                    # Public-facing views
```

### Frontend Stack
- **Blade Templates** - Server-side rendering
- **Alpine.js** - Lightweight JavaScript framework
- **Tailwind CSS** - Utility-first CSS
- **Vite** - Build tool

### Component Patterns
- Reusable Blade components in `resources/views/components/`
- Alpine.js for interactivity
- API calls via Axios

## Queue & Jobs

### Job Classes
- `SendWelcomeEmailJob` - Send welcome emails
- `SendBulkEmailJob` - Bulk email sending
- `SendBulkSMSJob` - Bulk SMS sending
- `ProcessCampaignStepJob` - Process email campaign steps

### Queue Configuration
- Uses database queue driver (can be Redis)
- Jobs implement `ShouldQueue`
- Retry logic configured per job

## Communication System

### Features
- Email campaigns with steps
- SMS sending (via Africa's Talking)
- Message templates
- Communication logs
- Performance tracking

### Key Models
- `EmailCampaign` - Campaign definitions
- `EmailCampaignStep` - Campaign steps
- `EmailCampaignEnrollment` - User enrollments
- `MessageTemplate` - Reusable templates
- `CommunicationLog` - Communication history
- `CommunicationSetting` - Provider settings

## Testing Structure

### Test Organization
```
tests/
├── Feature/                   # Feature tests
│   ├── Auth/                  # Authentication tests
│   ├── Member/                # Member management tests
│   └── ...
└── Unit/                      # Unit tests
    ├── Models/                # Model tests
    └── Services/              # Service tests
```

### Testing Patterns
- PHPUnit for testing
- Feature tests for full workflows
- Unit tests for isolated logic
- Factories for test data

## Key Conventions

### Naming Conventions
- **Controllers**: PascalCase, singular (e.g., `MemberController`)
- **Models**: PascalCase, singular (e.g., `Member`)
- **Services**: PascalCase, singular with "Service" suffix
- **Policies**: PascalCase, singular with "Policy" suffix
- **Routes**: kebab-case (e.g., `/admin/members`)

### Code Style
- PHP 8.4 features (typed properties, enums, etc.)
- Strict types (`declare(strict_types=1);`)
- Final classes where appropriate
- Explicit return types
- Constructor property promotion

### Error Handling
- Try-catch blocks in controllers
- Logging with context
- User-friendly error messages
- JSON error responses for API

## Adding New Features - Guidelines

### 1. Create Model
```bash
php artisan make:model ModelName -m
```

### 2. Create Migration
- Add relationships
- Add indexes
- Consider soft deletes

### 3. Create Policy
```bash
php artisan make:policy ModelNamePolicy --model=ModelName
```
- Extend `BasePolicy`
- Implement standard methods (viewAny, view, create, update, delete)

### 4. Create Service (if needed)
- Place business logic in services
- Keep controllers thin

### 5. Create Controller
```bash
php artisan make:controller ControllerName
```
- Use `AuthorizesRequests` trait
- Check permissions first
- Scope by branch for non-super admins
- Return JSON responses

### 6. Create Form Request (for validation)
```bash
php artisan make:request StoreModelNameRequest
```

### 7. Register Routes
- Add to appropriate route group
- Use role middleware
- Use prefixes for organization

### 8. Create Views (if needed)
- Follow existing view structure
- Use Blade components
- Add Alpine.js for interactivity

### 9. Write Tests
- Feature tests for workflows
- Unit tests for services
- Use factories for test data

## Common Patterns to Follow

### Branch Scoping
```php
if (!$user->isSuperAdmin()) {
    $branchId = $user->getPrimaryBranch()->id;
    $query->where('branch_id', $branchId);
}
```

### Authorization
```php
Gate::authorize('viewAny', Model::class);
// or
$this->authorize('update', $model);
```

### Eager Loading
```php
Model::with(['relationship1', 'relationship2'])->get();
```

### JSON Responses
```php
return response()->json([
    'success' => true,
    'data' => $data,
]);
```

### Error Handling
```php
try {
    // Operation
} catch (\Exception $e) {
    Log::error('Operation failed', [
        'error' => $e->getMessage(),
        'context' => [...],
    ]);
    
    return response()->json([
        'success' => false,
        'message' => 'User-friendly message',
    ], 500);
}
```

## Important Files to Reference

- `bootstrap/app.php` - Application configuration, middleware
- `app/Providers/AppServiceProvider.php` - Policy registration, service bindings
- `app/Policies/BasePolicy.php` - Base authorization logic
- `app/Http/Middleware/RoleBasedAccess.php` - Role middleware
- `app/Models/User.php` - User model with role methods
- `routes/web.php` - Route definitions

## Next Steps for Feature Development

1. Understand the feature requirements
2. Identify affected models and relationships
3. Plan database changes (migrations)
4. Create/update models
5. Create policies for authorization
6. Create services for business logic
7. Create controllers for HTTP handling
8. Create form requests for validation
9. Register routes
10. Create views (if needed)
11. Write tests
12. Update documentation

