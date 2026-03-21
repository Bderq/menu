# User Authentication & Security

## Overview
Secure authentication and role-based access control for administrative and staff users.

## Core Models
-   `User`: Primary authentication entity.

## Security Model
-   **Authentication:** Managed by Laravel's built-in authentication system and Filament's session management.
-   **Authorization:** Role-based access control (RBAC) to restrict access to specific management features like campaigns or bulk pricing.
-   **Environment Storage:** Sensitive keys (App Key, Database Credentials) managed via `.env`.

## Key Features
-   **Admin Login:** Secure gateway for the Filament management panel.
-   **Profile Management:** Ability for users to manage their own credentials.
