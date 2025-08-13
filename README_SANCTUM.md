# Laravel Sanctum API Authentication - Church Dashboard

This document explains how to use the Laravel Sanctum API authentication system configured for the Church Dashboard application.

## Overview

Laravel Sanctum has been successfully configured with the following features:
- User registration and login
- API token generation and management
- Token-based authentication for API routes
- Role-based route protection (structure ready)
- Comprehensive test coverage

## API Endpoints

### Authentication Endpoints

#### Register a New User
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "church_1|abcdef123456..."
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "church_1|abcdef123456..."
}
```

#### Get Authenticated User
```http
GET /api/auth/user
Authorization: Bearer church_1|abcdef123456...
```

**Response:**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### Logout (Revoke Current Token)
```http
POST /api/auth/logout
Authorization: Bearer church_1|abcdef123456...
```

**Response:**
```json
{
    "message": "Logged out successfully"
}
```

#### Revoke All Tokens
```http
POST /api/auth/revoke-all-tokens
Authorization: Bearer church_1|abcdef123456...
```

**Response:**
```json
{
    "message": "All tokens revoked successfully"
}
```

## Configuration

### Token Settings
- **Token Prefix:** `church_` (configurable via `SANCTUM_TOKEN_PREFIX`)
- **Token Expiration:** 24 hours (configurable via `SANCTUM_TOKEN_EXPIRATION`)
- **Stateful Domains:** Configured for local development

### Environment Variables
Add these to your `.env` file if you need to customize:
```env
SANCTUM_TOKEN_PREFIX=church_
SANCTUM_TOKEN_EXPIRATION=1440  # 24 hours in minutes
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
```

## Role-Based Route Structure

The API routes are structured to support the Church Dashboard role system:

- `/api/admin/*` - Super Admin routes
- `/api/pastor/*` - Branch Pastor routes  
- `/api/ministry/*` - Ministry Leader routes
- `/api/department/*` - Department Leader routes
- `/api/member/*` - Church Member routes
- `/api/public/*` - Public access routes

## Testing

Run the authentication tests:
```bash
php artisan test --filter=AuthenticationTest
```

## Usage Examples

### Using cURL

#### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com", 
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Access Protected Route
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer church_1|your-token-here"
```

## Next Steps

1. **Implement Role System:** Create roles and permissions for the different user types
2. **Add Role Middleware:** Implement middleware to check user roles for route access
3. **Extend User Model:** Add fields for church-specific user data
4. **Create Role-Specific Controllers:** Implement controllers for each user role's functionality

## Security Notes

- Tokens are prefixed with `church_` for security scanning
- Tokens expire after 24 hours by default
- All authentication routes use proper validation
- Passwords are hashed using Laravel's built-in hashing
- CSRF protection is enabled for stateful requests 