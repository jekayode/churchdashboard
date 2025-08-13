# Church Dashboard Application Architecture

## Technology Stack
- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel Storage (local)
- **Queue System**: Redis/Database
- **Cache**: Redis/File

## Project Structure

```
church-dashboard/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── GenerateMonthlyReports.php
│   │       └── UpdateMemberStatuses.php
│   ├── Events/
│   │   ├── MemberStatusUpdated.php
│   │   ├── EventRegistrationCreated.php
│   │   └── CheckInRecorded.php
│   ├── Exceptions/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── EventController.php
│   │   │   │   ├── MemberController.php
│   │   │   │   └── CheckInController.php
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── RegisterController.php
│   │   │   ├── SuperAdmin/
│   │   │   │   ├── BranchController.php
│   │   │   │   ├── PastorController.php
│   │   │   │   ├── MinistryController.php
│   │   │   │   ├── DepartmentController.php
│   │   │   │   ├── MemberController.php
│   │   │   │   ├── SmallGroupController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── ImportExportController.php
│   │   │   ├── BranchPastor/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── MemberController.php
│   │   │   │   ├── MinistryController.php
│   │   │   │   ├── DepartmentController.php
│   │   │   │   ├── SmallGroupController.php
│   │   │   │   ├── EventController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── ExpenseController.php
│   │   │   │   ├── CheckInController.php
│   │   │   │   └── SettingsController.php
│   │   │   ├── MinistryLeader/
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── DepartmentController.php
│   │   │   ├── DepartmentLeader/
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── MemberController.php
│   │   │   ├── Member/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── EventController.php
│   │   │   │   └── SmallGroupController.php
│   │   │   └── Public/
│   │   │       ├── HomeController.php
│   │   │       ├── EventController.php
│   │   │       ├── BranchController.php
│   │   │       ├── SmallGroupController.php
│   │   │       └── CheckInController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   ├── CheckBranchAccess.php
│   │   │   └── CheckMinistryAccess.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── RegisterRequest.php
│   │   │   ├── SuperAdmin/
│   │   │   │   ├── StoreBranchRequest.php
│   │   │   │   ├── StorePastorRequest.php
│   │   │   │   ├── StoreMinistryRequest.php
│   │   │   │   └── StoreMemberRequest.php
│   │   │   ├── BranchPastor/
│   │   │   │   ├── StoreMemberRequest.php
│   │   │   │   ├── StoreEventRequest.php
│   │   │   │   └── StoreExpenseRequest.php
│   │   │   └── Public/
│   │   │       └── EventRegistrationRequest.php
│   │   └── Resources/
│   │       ├── BranchResource.php
│   │       ├── MemberResource.php
│   │       ├── EventResource.php
│   │       └── ReportResource.php
│   ├── Jobs/
│   │   ├── ProcessEventReport.php
│   │   ├── GenerateMonthlyReport.php
│   │   └── SendEventReminder.php
│   ├── Listeners/
│   │   ├── UpdateMemberStatusOnAssignment.php
│   │   ├── SendEventRegistrationNotification.php
│   │   └── LogCheckInActivity.php
│   ├── Mail/
│   │   ├── EventRegistrationConfirmation.php
│   │   ├── MonthlyReport.php
│   │   └── EventReminder.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Branch.php
│   │   ├── Ministry.php
│   │   ├── Department.php
│   │   ├── SmallGroup.php
│   │   ├── Member.php
│   │   ├── Event.php
│   │   ├── EventRegistration.php
│   │   ├── EventReport.php
│   │   ├── CheckIn.php
│   │   ├── Expense.php
│   │   ├── YearlyProjection.php
│   │   └── Report.php
│   ├── Notifications/
│   │   ├── EventRegistrationNotification.php
│   │   └── MonthlyReportNotification.php
│   ├── Policies/
│   │   ├── BranchPolicy.php
│   │   ├── MemberPolicy.php
│   │   ├── EventPolicy.php
│   │   └── ReportPolicy.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Services/
│   │   ├── BranchService.php
│   │   ├── MemberService.php
│   │   ├── EventService.php
│   │   ├── ReportService.php
│   │   ├── ImportExportService.php
│   │   ├── CheckInService.php
│   │   └── DashboardService.php
│   └── Traits/
│       ├── HasBranchScope.php
│       ├── HasRoles.php
│       └── ImportExportable.php
├── bootstrap/
│   ├── app.php
│   └── cache/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── mail.php
│   ├── queue.php
│   └── sanctum.php
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── BranchFactory.php
│   │   ├── MemberFactory.php
│   │   └── EventFactory.php
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php
│   │   ├── 2024_01_02_000000_create_branches_table.php
│   │   ├── 2024_01_03_000000_create_ministries_table.php
│   │   ├── 2024_01_04_000000_create_departments_table.php
│   │   ├── 2024_01_05_000000_create_small_groups_table.php
│   │   ├── 2024_01_06_000000_create_members_table.php
│   │   ├── 2024_01_07_000000_create_events_table.php
│   │   ├── 2024_01_08_000000_create_event_registrations_table.php
│   │   ├── 2024_01_09_000000_create_event_reports_table.php
│   │   ├── 2024_01_10_000000_create_check_ins_table.php
│   │   ├── 2024_01_11_000000_create_expenses_table.php
│   │   ├── 2024_01_12_000000_create_yearly_projections_table.php
│   │   ├── 2024_01_13_000000_create_reports_table.php
│   │   ├── 2024_01_14_000000_create_member_department_table.php
│   │   ├── 2024_01_15_000000_create_member_small_group_table.php
│   │   └── 2024_01_16_000000_create_personal_access_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── BranchSeeder.php
│       ├── MinistrySeeder.php
│       └── DepartmentSeeder.php
├── public/
│   ├── index.php
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── storage/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── components/
│   │   │   ├── chart.js
│   │   │   ├── datepicker.js
│   │   │   ├── modal.js
│   │   │   └── table.js
│   │   └── pages/
│   │       ├── dashboard.js
│   │       ├── members.js
│   │       ├── events.js
│   │       └── reports.js
│   ├── lang/
│   │   └── en/
│   │       ├── auth.php
│   │       ├── pagination.php
│   │       ├── passwords.php
│   │       └── validation.php
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   ├── guest.blade.php
│       │   └── public.blade.php
│       ├── components/
│       │   ├── alert.blade.php
│       │   ├── button.blade.php
│       │   ├── card.blade.php
│       │   ├── chart.blade.php
│       │   ├── input.blade.php
│       │   ├── modal.blade.php
│       │   ├── navigation.blade.php
│       │   ├── sidebar.blade.php
│       │   └── table.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── super-admin/
│       │   ├── dashboard.blade.php
│       │   ├── branches/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── pastors/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── ministries/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── departments/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── members/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── small-groups/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   └── reports/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       ├── branch-pastor/
│       │   ├── dashboard.blade.php
│       │   ├── members/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── ministries/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── departments/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── small-groups/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   └── show.blade.php
│       │   ├── events/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   ├── show.blade.php
│       │   │   └── check-in.blade.php
│       │   ├── reports/
│       │   │   ├── index.blade.php
│       │   │   ├── attendance.blade.php
│       │   │   ├── monthly.blade.php
│       │   │   └── comparison.blade.php
│       │   ├── expenses/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── import.blade.php
│       │   └── settings/
│       │       ├── index.blade.php
│       │       └── projections.blade.php
│       ├── ministry-leader/
│       │   ├── dashboard.blade.php
│       │   └── departments/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       ├── department-leader/
│       │   ├── dashboard.blade.php
│       │   └── members/
│       │       ├── index.blade.php
│       │       └── assign.blade.php
│       ├── member/
│       │   ├── dashboard.blade.php
│       │   ├── events/
│       │   │   ├── index.blade.php
│       │   │   ├── show.blade.php
│       │   │   └── register.blade.php
│       │   └── small-groups/
│       │       ├── index.blade.php
│       │       └── register-interest.blade.php
│       └── public/
│           ├── home.blade.php
│           ├── events/
│           │   ├── index.blade.php
│           │   ├── show.blade.php
│           │   └── register.blade.php
│           ├── branches/
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           ├── small-groups/
│           │   ├── index.blade.php
│           │   └── show.blade.php
│           └── check-in/
│               └── index.blade.php
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── console.php
│   └── channels.php
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── imports/
│   │   │   ├── exports/
│   │   │   └── reports/
│   │   └── private/
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   └── RegisterTest.php
│   │   ├── SuperAdmin/
│   │   │   ├── BranchManagementTest.php
│   │   │   ├── MemberManagementTest.php
│   │   │   └── ReportTest.php
│   │   ├── BranchPastor/
│   │   │   ├── MemberManagementTest.php
│   │   │   ├── EventManagementTest.php
│   │   │   └── ReportTest.php
│   │   └── Public/
│   │       ├── EventRegistrationTest.php
│   │       └── CheckInTest.php
│   └── Unit/
│       ├── Models/
│       │   ├── UserTest.php
│       │   ├── BranchTest.php
│       │   ├── MemberTest.php
│       │   └── EventTest.php
│       └── Services/
│           ├── MemberServiceTest.php
│           ├── EventServiceTest.php
│           └── ReportServiceTest.php
├── .env
├── .env.example
├── artisan
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
└── README.md
```

## Core Components Explanation

### Models & Relationships

#### User Model
```php
// Represents system users (Super Admin, Branch Pastor, Ministry Leader, Department Leader)
class User extends Authenticatable
{
    // Roles: super_admin, branch_pastor, ministry_leader, department_leader
    // Relationships: belongsTo(Branch), hasMany(Member) through branch
}
```

#### Branch Model
```php
// Church branches
class Branch extends Model
{
    // Fields: name, logo, venue, service_time, phone, email, map_embed_code, status
    // Relationships: belongsTo(User as pastor), hasMany(Ministry, SmallGroup, Member, Event)
}
```

#### Member Model
```php
// Church members (separate from User - members are people, Users are system users)
class Member extends Model
{
    // Fields: name, email, dob, anniversary, gender, marital_status, occupation, 
    // nearest_bus_stop, date_joined, membership_class_date, teci_status, 
    // growth_level, leadership_trainings, status
    // Relationships: belongsTo(Branch), belongsToMany(Department, SmallGroup)
}
```

#### Ministry Model
```php
// Church ministries
class Ministry extends Model
{
    // Fields: name, description, status
    // Relationships: belongsTo(Branch), hasMany(Department), belongsTo(User as leader)
}
```

#### Department Model
```php
// Departments under ministries
class Department extends Model
{
    // Fields: name, description, status
    // Relationships: belongsTo(Ministry), belongsTo(User as leader), belongsToMany(Member)
}
```

#### Event Model
```php
// Church events
class Event extends Model
{
    // Fields: name, location, date_time, frequency, registration_type, 
    // registration_link, custom_fields, status
    // Relationships: belongsTo(Branch), hasMany(EventRegistration, EventReport, CheckIn)
}
```

### Services Layer

#### MemberService
- **Purpose**: Handle all member-related business logic
- **Key Methods**:
  - `createMember()` - Create new member and auto-assign status
  - `updateMemberStatus()` - Update status based on assignments
  - `assignToDepartment()` - Assign member to department
  - `assignToSmallGroup()` - Assign member to small group
  - `getMembersWithFilters()` - Get filtered member lists

#### EventService
- **Purpose**: Manage events, registrations, and check-ins
- **Key Methods**:
  - `createEvent()` - Create new event with recurrence
  - `registerForEvent()` - Handle event registration
  - `checkInMember()` - Process event check-in
  - `submitEventReport()` - Process event attendance reports
  - `getEventStats()` - Generate event statistics

#### ReportService
- **Purpose**: Generate various reports and statistics
- **Key Methods**:
  - `getDashboardStats()` - Get dashboard statistics
  - `generateMonthlyReport()` - Generate monthly reports
  - `getAttendanceComparison()` - Compare attendance data
  - `getProjectionVsActual()` - Compare projections vs actuals

#### ImportExportService
- **Purpose**: Handle data import/export operations
- **Key Methods**:
  - `importMembers()` - Import members from CSV/Excel
  - `exportMembers()` - Export members to CSV/Excel
  - `importExpenses()` - Import expenses data
  - `exportReports()` - Export reports

### State Management

#### Authentication State
- **Location**: Laravel Sanctum + Session
- **Purpose**: Manage user authentication and sessions
- **Access**: Available globally through `auth()` helper

#### User Context State
- **Location**: Middleware + Service Container
- **Purpose**: Store current user's branch, role, and permissions
- **Access**: Injected into controllers via middleware

#### Dashboard State
- **Location**: Alpine.js data properties
- **Purpose**: Manage frontend component state
- **Access**: Component-level state management

#### Form State
- **Location**: Alpine.js + Livewire (if needed)
- **Purpose**: Handle form interactions and validation
- **Access**: Form component state

### API Endpoints Structure

#### Authentication Routes
```
POST /login
POST /logout
POST /register
```

#### Super Admin Routes
```
GET|POST /super-admin/branches
GET|POST /super-admin/pastors
GET|POST /super-admin/ministries
GET|POST /super-admin/departments
GET|POST /super-admin/members
GET|POST /super-admin/small-groups
GET /super-admin/reports
POST /super-admin/import/{type}
GET /super-admin/export/{type}
```

#### Branch Pastor Routes
```
GET /branch-pastor/dashboard
GET|POST /branch-pastor/members
GET|POST /branch-pastor/ministries
GET|POST /branch-pastor/departments
GET|POST /branch-pastor/small-groups
GET|POST /branch-pastor/events
POST /branch-pastor/events/{id}/check-in
GET|POST /branch-pastor/expenses
GET /branch-pastor/reports
POST /branch-pastor/settings/projections
```

#### Public Routes
```
GET /
GET /events
POST /events/{id}/register
GET /branches
GET /small-groups
POST /check-in
```

### Database Schema Overview

#### Core Tables
- **users**: System users (pastors, leaders)
- **branches**: Church branches
- **ministries**: Church ministries
- **departments**: Ministry departments
- **small_groups**: Small groups
- **members**: Church members
- **events**: Church events
- **event_registrations**: Event registrations
- **event_reports**: Event attendance reports
- **check_ins**: Event check-ins
- **expenses**: Branch expenses
- **yearly_projections**: Branch yearly targets
- **reports**: Generated reports

#### Pivot Tables
- **member_department**: Member-Department assignments
- **member_small_group**: Member-SmallGroup assignments

### Middleware & Security

#### Role-Based Access Control
- **CheckRole**: Verify user has required role
- **CheckBranchAccess**: Ensure user can access branch data
- **CheckMinistryAccess**: Verify ministry-level permissions

#### Data Scoping
- **HasBranchScope**: Automatically filter data by user's branch
- **Policies**: Define authorization rules for each model

### Frontend Architecture

#### Layouts
- **app.blade.php**: Main authenticated layout
- **guest.blade.php**: Unauthenticated layout  
- **public.blade.php**: Public-facing layout

#### Components
- **Reusable**: Alert, Button, Card, Input, Modal, Table
- **Interactive**: Chart.js integration, Datepicker, Dynamic tables

#### JavaScript Organization
- **app.js**: Main application JavaScript
- **components/**: Reusable JS components
- **pages/**: Page-specific JavaScript

### Queue & Job System

#### Background Jobs
- **ProcessEventReport**: Process event attendance reports
- **GenerateMonthlyReport**: Generate monthly summary reports
- **SendEventReminder**: Send event reminder notifications

#### Scheduled Tasks
- **GenerateMonthlyReports**: Auto-generate monthly reports
- **UpdateMemberStatuses**: Update member statuses based on assignments

### File Storage Structure

#### Storage Organization
- **public/imports/**: CSV/Excel import files
- **public/exports/**: Generated export files
- **public/reports/**: Generated PDF reports
- **private/**: Sensitive documents and backups

This architecture provides a scalable, maintainable foundation for the Church Dashboard application with clear separation of concerns, role-based access control, and extensible design patterns.