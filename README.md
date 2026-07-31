# University Student Marketplace (UniMarket)

A closed, campus-exclusive peer-to-peer marketplace web application designed for university students to safely buy, sell, and trade textbooks, electronics, dorm essentials, and student services.

---

## Project Overview

UniMarket addresses peer-to-peer commerce challenges on university campuses by enforcing student identity authentication and establishing a zero-monetary-risk "Reserve & Meet" exchange framework.

---

## Key Features Implemented (Week 5 / DP10)

- **Database Architecture**: Relational MySQL schema containing `users`, `categories`, `products`, and `transactions` tables.
- **PDO Connection Wrapper**: Centralized `Database` class in `backend/config/database.php` providing secure PDO connections with UTF-8 character encoding and exception handling.
- **Database Authentication**: Secure login system querying the `users` table, supporting password verification (`password_verify()`) and fallback for legacy plain-text comparisons.
- **Session Management**: Session regeneration (`session_regenerate_id()`), storage of `user_id`, `full_name`, and `role` in `$_SESSION`.
- **Dashboard Protection**: Guard checks on authenticated pages redirecting unauthorized visitors back to `login.php`.
- **Dynamic Dashboard Display**: Logged-in user's full name is rendered securely with `htmlspecialchars()`.
- **Session Cleanup & Logout**: Complete session data unsetting, session cookie expiration, and server session destruction upon logout.

---

## Project Structure

```text
university-student-marketplace/
├── backend/
│   └── config/
│       ├── config.php          # Database host, database name, and credentials
│       └── database.php        # Centralized PDO Database connection class
├── database/
│   ├── schema.sql              # MySQL DDL table schema definitions
│   ├── seed.sql                # Seed data for categories, users, products, transactions
│   └── ERD.md                  # Entity Relationship Diagram documentation
├── docs/
│   └── DP10_IMPLEMENTATION_REPORT.md # Week 5 implementation report
├── frontend/
│   ├── css/
│   │   ├── login.css           # Authentication form layout styles
│   │   └── styles.css          # Site-wide base typography, header, and layout styles
│   ├── js/
│   │   └── app.js              # Frontend interactive logic (mobile nav, categories, stats)
│   └── pages/
│       ├── index.php           # Public landing page and student registration form
│       ├── login.php           # Database authentication login form
│       ├── dashboard.php       # Protected student dashboard
│       └── logout.php          # Session destruction and logout script
├── .env.example                # Environment variable configuration template
├── .gitignore                  # Git exclusion rules
├── generate_hash.php           # CLI utility for generating bcrypt password hashes
├── package.json                # Project manifest
└── README.md                   # Project documentation
```

---

## Setup & Local Installation

### Requirements
- XAMPP / WAMP / LAMP stack (PHP 7.4+ or 8.x, MySQL / MariaDB server)
- Apache HTTP Server

### Database Setup
1. Start **Apache** and **MySQL** in your control panel (e.g., XAMPP Control Panel).
2. Open PHPMyAdmin or your MySQL CLI and create the database:
   ```sql
   CREATE DATABASE university_student_marketplace;
   ```
3. Import the database schema:
   ```bash
   mysql -u root university_student_marketplace < database/schema.sql
   ```
4. Import seed data:
   ```bash
   mysql -u root university_student_marketplace < database/seed.sql
   ```

### Running the Application
1. Place or clone the repository inside your web server directory (e.g., `C:\xampp\htdocs\university-student-marketplace`).
2. Verify database connection credentials in `backend/config/config.php`.
3. Open your browser and navigate to:
   - **Landing Page**: `http://localhost/university-student-marketplace/frontend/pages/index.php`
   - **Login Page**: `http://localhost/university-student-marketplace/frontend/pages/login.php`

---

## Default Test Credentials

| Email | Password | Role |
| :--- | :--- | :--- |
| `rahim@university.edu` | `password123` | Student |
| `karim@university.edu` | `password123` | Student |
| `admin@university.edu` | `password123` | Administrator |

---

## Development Guidelines

- **Code Quality**: Strictly separate HTML view markup from PHP controller logic.
- **Security**: Always use prepared statements (`PDO::prepare()`) for database queries and `htmlspecialchars()` when rendering user input in HTML templates.