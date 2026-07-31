# DP10 Implementation Report

## 1. Files Modified

The following files were changed as part of the DP10 database-authentication implementation.

| File path | Action | Reason |
|---|---|---|
| `backend/config/database.php` | Modified | Loads `config.php` using `__DIR__` so the existing `Database` class works when included from PHP pages. |
| `frontend/pages/login.php` | Modified | Replaced mock credentials with database lookup, password verification, session creation, and login error handling. |
| `frontend/pages/dashboard.php` | Modified | Requires an authenticated `user_id` session and displays the session `full_name`. |
| `frontend/pages/logout.php` | Modified | Clears session data, invalidates the session cookie, destroys the session, and redirects to login. |
| `frontend/pages/index.php` | Modified | Changes the existing Sign In link to open `login.php`. No layout or CSS was changed. |
| `docs/DP10_IMPLEMENTATION_REPORT.md` | Created | Records this implementation and its verification status. |

No files were deleted. Existing unrelated working-tree changes in `database/seed.sql` and the untracked `generate_hash.php` file were not modified by this implementation.

## 2. Authentication Flow

1. A visitor opens `frontend/pages/login.php`.
2. The page starts a PHP session if one is not already active. If `$_SESSION['user_id']` already exists, the visitor is redirected to `dashboard.php`.
3. The visitor submits an email address and password using the existing login form.
4. `login.php` creates an instance of the existing `Database` class and calls `connect()`.
5. A prepared SQL statement looks up one user by the submitted email address.
6. If a user is found, the stored password value is inspected:
   - Stored password hashes are verified with `password_verify()`.
   - A non-hash value is compared as plain text with `hash_equals()` for compatibility with a legacy plain-text database.
7. On a valid password, PHP regenerates the session ID and stores the authenticated user's ID, full name, and role in the session.
8. The browser is redirected to `dashboard.php`.
9. `dashboard.php` requires `$_SESSION['user_id']`; otherwise it redirects to `login.php`.
10. When `logout.php` is opened, it clears session data, expires the session cookie, destroys the server-side session, and redirects to `login.php`.

## 3. Code Changes

### `backend/config/database.php`

- Replaced `require_once 'config.php';` with `require_once __DIR__ . '/config.php';`.
- This preserves the existing `Database` class while ensuring its configuration file resolves from the configuration directory instead of depending on the executing page's working directory.

### `frontend/pages/login.php`

- Removed `$validEmail` and `$validPassword` mock-authentication variables.
- Added the existing database configuration file with `require_once __DIR__ . '/../../backend/config/database.php';`.
- Redirects users with an existing `user_id` session directly to the dashboard.
- Reads submitted email and password safely, trimming only the email address.
- Uses a prepared statement to query the `users` table by email.
- Verifies hashed passwords with `password_verify()` and supports a plain-text comparison only when the stored value is not recognized as a password hash.
- Regenerates the session ID after successful authentication.
- Stores `user_id`, `full_name`, and `role` in `$_SESSION`.
- Shows the existing generic invalid-credentials message for failed authentication or database connection errors.
- Escapes the displayed error message.
- Corrected the page stylesheet path from the missing `style.css` to the existing `styles.css`; no CSS content was changed.

### `frontend/pages/dashboard.php`

- Starts a session only when necessary.
- Replaced the old `$_SESSION['email']` access check with `$_SESSION['user_id']`.
- Escapes and displays `$_SESSION['full_name']` in the existing dashboard content.
- Corrected the stylesheet reference to the existing `styles.css`; no CSS content was changed.

### `frontend/pages/logout.php`

- Starts a session only when necessary.
- Clears all session values with `$_SESSION = []`.
- Expires the PHP session cookie when cookie sessions are enabled.
- Calls `session_destroy()` and redirects to `login.php`.

### `frontend/pages/index.php`

- Changed only the existing Sign In link target from `#` to `login.php` so users can reach the database login page.

## 4. Database Usage

- **Database connection:** The existing `Database` class in `backend/config/database.php` connects using the configuration values in `backend/config/config.php`.
- **Table accessed:** `users`.
- **SQL query used:**

  ```sql
  SELECT user_id, full_name, role, password_hash
  FROM users
  WHERE email = :email
  LIMIT 1
  ```

- **Columns read:** `user_id`, `full_name`, `role`, and `password_hash`. The `email` column is used in the `WHERE` condition.
- **Password comparison:** `password_verify()` is used when `password_hash` contains a recognized PHP password hash. Otherwise, the submitted password is compared against the stored value as plain text using `hash_equals()`.

## 5. Session Management

On successful login, `frontend/pages/login.php` creates these session values:

| Session variable | Value |
|---|---|
| `$_SESSION['user_id']` | Authenticated user's numeric `user_id` |
| `$_SESSION['full_name']` | Authenticated user's `full_name` |
| `$_SESSION['role']` | Authenticated user's `role` |

- These values are created only after a successful password check.
- The session ID is regenerated before those values are stored.
- `frontend/pages/dashboard.php` requires `$_SESSION['user_id']`; unauthenticated visitors are redirected to login.
- `frontend/pages/logout.php` clears the session array, expires the session cookie, and destroys the session.

### Existing Database Password Update

The existing database records were updated without recreating the database. The following SQL was executed after generating the bcrypt hash for `password123`:

```sql
UPDATE users
SET password_hash = '$2y$10$/keEAc.Cwuu5NRqvpBUvmuHVMZern7d4FhBEv4y4Jc8KdmfhZZ3ye'
WHERE email IN (
    'rahim@university.edu',
    'karim@university.edu',
    'admin@university.edu'
);
```

The command updated three records. `database/seed.sql` now uses the same hash for all three sample users, so future seed imports use the same known password.

## 6. Manual Testing Performed

| Test | Result | Details |
|---|---|---|
| PHP syntax validation | Completed | `php -l` reported no syntax errors in all configuration and page PHP files. |
| Database connectivity | Completed | The PHP `pdo_mysql` extension is enabled, and a read-only `SELECT 1` query against `university_student_marketplace` succeeded. |
| Users table availability | Completed | A read-only query returned the three seeded users: Rahim Islam, Karim Ahmed, and Admin User. |
| Failed login | Completed | Submitting `rahim@university.edu` with an incorrect password displayed the generic `Invalid email or password.` message. |
| Direct dashboard access without login | Completed | An anonymous request to `dashboard.php` redirected to `login.php`. |
| Logout redirect | Completed | A request to `logout.php` redirected to `login.php`. |
| Successful login | Completed | All three seeded users successfully logged in with `password123` and received a redirect to `dashboard.php`. |
| Authenticated dashboard access | Completed | The dashboard rendered the correct session full name for Rahim Islam, Karim Ahmed, and Admin User. |
| Logout after authenticated login | Completed | Each authenticated account was redirected to `login.php` after logout. |
| Session persistence after successful login | Completed | A second dashboard request using the same session cookie remained authenticated for each seeded user. |

The following tested credentials now work:

| Email | Password |
|---|---|
| `rahim@university.edu` | `password123` |
| `karim@university.edu` | `password123` |
| `admin@university.edu` | `password123` |

## 7. Remaining Manual Steps

1. Confirm that MySQL is running in XAMPP before using the application.
2. Ensure the `university_student_marketplace` database and its `users` table are present. Import the appropriate schema and seed data only if the database has not already been created.
3. If a different database is used, run the SQL update in this report or import the updated `database/seed.sql` so its user passwords match the documented test credentials.
4. Optionally repeat the verified login, dashboard, logout, and persistence checks in a browser through Apache/XAMPP.

## 8. Potential Issues

- `login.php` supports legacy plain-text stored passwords only for compatibility. Production data should use PHP password hashes exclusively.
- Database credentials are currently defined directly in `backend/config/config.php`, which is suitable for local XAMPP development but should be protected and environment-specific before deployment.
- The implementation assumes the existing `users` table has the columns defined in the supplied schema: `user_id`, `full_name`, `email`, `password_hash`, and `role`.

## 9. Acceptance Criteria Checklist

- ✅ Database-backed login implemented
- ✅ Hardcoded credentials removed
- ✅ Dashboard protected
- ✅ Logout implemented correctly
- ✅ Sessions working
- ✅ Existing UI preserved
- ✅ Existing project structure preserved
- ✅ No unnecessary files added
- ✅ Project builds without syntax errors
