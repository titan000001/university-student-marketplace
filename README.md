# UniMarket

UniMarket is a university student marketplace prototype built with PHP, HTML, CSS, and JavaScript. At its current stage, it demonstrates a homepage, mock student login, protected dashboard, and secure logout flow.

## Current scope

- Homepage with marketplace categories, statistics, and a client-side registration demonstration
- Mock login using one development credential
- PHP session-based dashboard protection
- CSRF-protected logout
- Responsive navigation and accessible form labels

This project does not currently use a database or create persistent student accounts.

## Technology

- PHP
- Apache via XAMPP
- HTML5
- CSS3
- JavaScript

## Project structure

```text
university-student-marketplace/
|-- frontend/
|   |-- css/                 # Shared and login-page styles
|   |-- includes/            # Shared session and CSRF helpers
|   |-- js/                  # Client-side homepage behaviour
|   `-- pages/               # PHP pages and request handlers
|-- backend/                 # Reserved for future work
|-- database/                # Reserved for future work
|-- docs/                    # Project documentation
`-- README.md
```

## Run locally with XAMPP

1. Place this project in XAMPP's `htdocs` directory.
2. Start Apache from the XAMPP Control Panel.
3. Open the following page in your browser:

   ```text
   http://localhost/university-student-marketplace/frontend/pages/index.php
   ```

## Mock login

Use these development-only credentials:

```text
Email:    student@university.edu
Password: password123
```

These credentials are intentionally temporary and must be replaced before any real deployment.

## Current authentication flow

```text
Login -> session regenerated -> protected dashboard -> CSRF-protected logout -> login
```

Unauthenticated visitors are redirected from the dashboard to the login page.

## Notes for future development

- Move mock credentials into a real authentication implementation only when the project enters its database phase.
- Keep session and CSRF handling in `frontend/includes/session.php` so page-level authentication code remains consistent.
- Update this README when persistent registration, listings, or database-backed authentication are introduced.
