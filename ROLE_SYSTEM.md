# Role System Documentation

## Overview

This backend implements a role-based access control system that aligns with the frontend middleware authentication logic.

## Role Values and Permissions

### Role 1995 - Admin

-   **Frontend Role**: Admin (highest privileges)
-   **Backend Access**: Full access to all endpoints
-   **Can**: Manage users, manage categories, access all dashboard features

### Role 1999 - Product Manager

-   **Frontend Role**: Product Manager
-   **Backend Access**: Category management + limited user access
-   **Can**: Manage categories, view users, but cannot add/edit users
-   **Cannot**: Access user creation/editing endpoints

### Role 2001 - Regular User

-   **Frontend Role**: Regular User (restricted)
-   **Backend Access**: Limited access
-   **Can**: Access basic dashboard features
-   **Cannot**: Access admin or manager features

### Role 3276 - Manager

-   **Frontend Role**: Manager
-   **Backend Access**: Limited user management
-   **Can**: View users, but cannot add/edit users
-   **Cannot**: Access user creation/editing endpoints

## Frontend-Backend Role Mapping

| Frontend Role | Backend Role | Description                                      |
| ------------- | ------------ | ------------------------------------------------ |
| 1995          | 1995         | Admin - Full access                              |
| 1999          | 1999         | Product Manager - Category + limited user access |
| 2001          | 2001         | Regular User - Restricted access                 |
| 3276          | 3276         | Manager - Limited user access                    |

## Middleware Implementation

### CheckAdmin

-   Allows only role `1995` (Admin)
-   Protects user management endpoints

### CheckProductManager

-   Allows roles `1995` (Admin) and `1999` (Product Manager)
-   Protects category management endpoints

### CheckManagerAccess

-   Restricts role `1999` (Product Manager) from user add/edit operations
-   Implements the same logic as frontend middleware

## API Endpoints

### Public Routes

-   `POST /api/register` - User registration
-   `POST /api/login` - User authentication
-   `POST /api/passowrd` - Password reset request
-   `POST /api/reset-password` - Password reset
-   `GET /api/login-google` - Google OAuth redirect
-   `GET /api/auth/google/callback` - Google OAuth callback

### Protected Routes (Require Authentication)

-   `GET /api/user` - Get authenticated user
-   `GET /api/logout` - User logout

### Admin Routes (Role 1995)

-   `GET /api/users` - Get all users
-   `GET /api/user/{id}` - Get specific user
-   `POST /api/user/edit/{id}` - Edit user
-   `POST /api/user/add` - Add new user
-   `DELETE /api/user/{id}` - Delete user

### Category Management Routes (Roles 1995, 1999)

-   `GET /api/categories` - Get all categories
-   `GET /api/category/{id}` - Get specific category
-   `POST /api/category/edit/{id}` - Edit category
-   `POST /api/category/add` - Add new category
-   `DELETE /api/category/{id}` - Delete category

## Database Seeding

The database seeder creates test users for each role:

-   **admin@example.com** / password (Role: 1995)
-   **manager@example.com** / password (Role: 1999)
-   **user@example.com** / password (Role: 2001)
-   **manager2@example.com** / password (Role: 3276)

## Running the Application

1. Install dependencies: `composer install`
2. Set up environment variables
3. Run migrations: `php artisan migrate`
4. Seed the database: `php artisan db:seed`
5. Start the server: `php artisan serve`

## Testing Authentication

Use the seeded accounts to test different role permissions:

-   Admin (1995): Full access to all endpoints
-   Product Manager (1999): Category management + limited user access
-   Regular User (2001): Basic dashboard access
-   Manager (3276): Limited user access
