# Church Dashboard - Entity Relationship Diagram (ERD)

## Overview
This document outlines the database schema design for the Church Dashboard application, including all core entities, their attributes, and relationships.

## Core Entities

### 1. Users
**Purpose:** Authentication and basic user information
- `id` (Primary Key)
- `name` (string, required)
- `email` (string, unique, required)
- `email_verified_at` (timestamp, nullable)
- `password` (string, required)
- `remember_token` (string, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Has many `personal_access_tokens` (Sanctum)
- Has many `user_roles` (Many-to-Many with Role)
- Has many `event_registrations`

### 2. Roles
**Purpose:** Define user access levels and permissions
- `id` (Primary Key)
- `name` (string, unique) - super_admin, branch_pastor, ministry_leader, department_leader, church_member, public_user
- `display_name` (string) - Human readable name
- `description` (text, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to many `users` (Many-to-Many)

### 3. Branches
**Purpose:** Church branch locations and information
- `id` (Primary Key)
- `name` (string, required)
- `logo` (string, nullable) - File path
- `venue` (string, required)
- `service_time` (string, required)
- `phone` (string, nullable)
- `email` (string, nullable)
- `map_embed_code` (text, nullable)
- `pastor_id` (Foreign Key to Users)
- `status` (enum: active, inactive, suspended)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `user` (pastor)
- Has many `members`
- Has many `ministries`
- Has many `small_groups`
- Has many `events`
- Has many `projections`

### 4. Members
**Purpose:** Church member detailed information
- `id` (Primary Key)
- `user_id` (Foreign Key to Users, nullable) - Links to User account if they have one
- `branch_id` (Foreign Key to Branches)
- `name` (string, required)
- `email` (string, nullable)
- `phone` (string, nullable)
- `date_of_birth` (date, nullable) - Day & Month required
- `anniversary` (date, nullable)
- `gender` (enum: male, female)
- `marital_status` (enum: single, married, divorced, widowed)
- `occupation` (string, nullable)
- `nearest_bus_stop` (string, nullable)
- `date_joined` (date, nullable)
- `date_attended_membership_class` (date, nullable)
- `teci_status` (enum: not_started, 100_level, 200_level, 300_level, 400_level, 500_level, graduated, paused)
- `growth_level` (enum: core, pastor, growing, new_believer)
- `leadership_trainings` (json) - Array of trainings: ELP, MLCC, MLCP Basic, MLCP Advanced
- `member_status` (enum: visitor, member, volunteer, leader, minister)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `branch`
- Belongs to `user` (nullable)
- Belongs to many `departments` (Many-to-Many)
- Belongs to many `small_groups` (Many-to-Many)
- Has many `event_registrations`

### 5. Ministries
**Purpose:** Church ministry organization
- `id` (Primary Key)
- `branch_id` (Foreign Key to Branches)
- `name` (string, required)
- `description` (text, nullable)
- `leader_id` (Foreign Key to Members, nullable)
- `status` (enum: active, inactive)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `branch`
- Belongs to `member` (leader)
- Has many `departments`

### 6. Departments
**Purpose:** Departments within ministries
- `id` (Primary Key)
- `ministry_id` (Foreign Key to Ministries)
- `name` (string, required)
- `description` (text, nullable)
- `leader_id` (Foreign Key to Members, nullable)
- `status` (enum: active, inactive)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `ministry`
- Belongs to `member` (leader)
- Belongs to many `members` (Many-to-Many)

### 7. Small Groups
**Purpose:** Small group management
- `id` (Primary Key)
- `branch_id` (Foreign Key to Branches)
- `name` (string, required)
- `description` (text, nullable)
- `leader_id` (Foreign Key to Members, nullable)
- `meeting_day` (string, nullable)
- `meeting_time` (time, nullable)
- `location` (string, nullable)
- `status` (enum: active, inactive)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `branch`
- Belongs to `member` (leader)
- Belongs to many `members` (Many-to-Many)

### 8. Events
**Purpose:** Church events and activities
- `id` (Primary Key)
- `branch_id` (Foreign Key to Branches)
- `name` (string, required)
- `description` (text, nullable)
- `location` (string, required)
- `start_date` (datetime, required)
- `end_date` (datetime, nullable)
- `frequency` (enum: once, weekly, monthly, quarterly, annually, recurrent)
- `registration_type` (enum: link, custom_form)
- `registration_link` (string, nullable)
- `custom_form_fields` (json, nullable) - Array of custom form fields
- `status` (enum: draft, published, cancelled, completed)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `branch`
- Has many `event_registrations`
- Has many `event_reports`

### 9. Event Registrations
**Purpose:** Track event registrations
- `id` (Primary Key)
- `event_id` (Foreign Key to Events)
- `user_id` (Foreign Key to Users, nullable) - For registered users
- `member_id` (Foreign Key to Members, nullable) - For existing members
- `name` (string, required) - For public users
- `email` (string, required)
- `phone` (string, nullable)
- `custom_fields` (json, nullable) - Responses to custom form fields
- `registration_date` (timestamp)
- `checked_in` (boolean, default: false)
- `checked_in_at` (timestamp, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships:**
- Belongs to `event`
- Belongs to `user` (nullable)
- Belongs to `member` (nullable)

### 10. Event Reports
**Purpose:** Event attendance and statistics reporting
- `id` (Primary Key)
- `event_id` (Foreign Key to Events)
- `reported_by` (Foreign Key to Users)
- `attendance_male` (integer, default: 0)
- `attendance_female` (integer, default: 0)
- `attendance_children` (integer, default: 0)
- `first_time_guests` (integer, default: 0)
- `converts` (integer, default: 0)
- `start_time` (datetime, nullable)
- `end_time` (datetime, nullable)
- `number_of_cars` (integer, default: 0)
- `notes` (text, nullable)
- `report_date` (date)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships:**
- Belongs to `event`
- Belongs to `user` (reported_by)

### 11. Expenses
**Purpose:** Branch expense management
- `id` (Primary Key)
- `branch_id` (Foreign Key to Branches)
- `item_name` (string, required)
- `quantity` (integer, required)
- `unit_cost` (decimal, required)
- `total_cost` (decimal, required)
- `expense_date` (date, required)
- `expense_month` (string, required) - Format: YYYY-MM
- `category` (string, nullable)
- `description` (text, nullable)
- `created_by` (Foreign Key to Users)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `branch`
- Belongs to `user` (created_by)

### 12. Projections
**Purpose:** Yearly projections and targets
- `id` (Primary Key)
- `branch_id` (Foreign Key to Branches)
- `year` (integer, required)
- `attendance_target` (integer, required)
- `converts_target` (integer, required)
- `leaders_target` (integer, required)
- `volunteers_target` (integer, required)
- `quarterly_breakdown` (json) - Quarterly targets
- `monthly_breakdown` (json) - Monthly targets
- `created_by` (Foreign Key to Users)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft Delete

**Relationships:**
- Belongs to `branch`
- Belongs to `user` (created_by)

## Pivot Tables (Many-to-Many Relationships)

### user_roles
- `id` (Primary Key)
- `user_id` (Foreign Key to Users)
- `role_id` (Foreign Key to Roles)
- `branch_id` (Foreign Key to Branches, nullable) - Role scope
- `created_at` (timestamp)
- `updated_at` (timestamp)

### member_departments
- `id` (Primary Key)
- `member_id` (Foreign Key to Members)
- `department_id` (Foreign Key to Departments)
- `assigned_at` (timestamp)
- `created_at` (timestamp)
- `updated_at` (timestamp)

### member_small_groups
- `id` (Primary Key)
- `member_id` (Foreign Key to Members)
- `small_group_id` (Foreign Key to Small Groups)
- `joined_at` (timestamp)
- `created_at` (timestamp)
- `updated_at` (timestamp)

## Key Design Decisions

1. **User vs Member Separation**: Users are for authentication, Members contain detailed church information. A Member can optionally link to a User account.

2. **Role-Based Access**: Roles are separate entities with many-to-many relationship to Users, allowing flexible permission management.

3. **Branch-Centric Design**: Most entities belong to a branch, supporting multi-branch church management.

4. **Soft Deletes**: All main entities use soft deletes to maintain data integrity and audit trails.

5. **JSON Fields**: Used for flexible data like custom form fields, leadership trainings, and projection breakdowns.

6. **Status Fields**: Most entities have status fields for lifecycle management.

7. **Audit Fields**: All entities include created_at, updated_at, and where applicable, created_by fields.

## Indexes for Performance

- Foreign key columns
- Email fields (unique indexes)
- Status fields
- Date fields (for reporting queries)
- Branch_id (heavily queried)
- Composite indexes for common query patterns

## Data Integrity Rules

- Cascade delete rules for dependent entities
- Foreign key constraints
- Unique constraints where appropriate
- Check constraints for enum values
- Not null constraints for required fields 