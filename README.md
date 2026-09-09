# TEPAN Website

Website for the **Technology Education Practitioners Association of Nigeria (TEPAN)** — a PHP + MySQL site
(built for XAMPP) with a public journal/proceedings archive and an admin portal for uploading content.

## Requirements
- XAMPP (Apache + MySQL/MariaDB + PHP 8+) — already satisfied on this machine.
- Site lives in `c:\xampp\htdocs\TEPA`, served at **http://localhost/TEPA/**

## Database
- Database: `tepan_db` (created automatically by `sql/schema.sql`)
- Credentials used: `root` / *(no password)* — the XAMPP default. Change these in
  [config/database.php](config/database.php) if your MySQL user differs.
- To (re)create the schema from scratch:
  ```
  C:\xampp\mysql\bin\mysql.exe -u root < sql\schema.sql
  ```

## Admin Login
URL: **http://localhost/TEPA/admin/login.php**

| Username | Password         |
|----------|------------------|
| `admin`  | `TepanAdmin#2026` |

**Change this password immediately** — sign in and open **My Account** in the admin sidebar.
The password shown above is only a placeholder set during initial setup.

### Password recovery

Three ways to recover / change an admin password:

1. **My Account** (`/admin/account.php`) — while signed in: change your password (needs the current one)
   and set the recovery email used by the forgot-password link.
2. **Forgot password** (`/admin/forgot-password.php`) — linked from the login page. Enter the admin's
   recovery email and a one-time reset link (valid 60 minutes) is generated.
   - Stock XAMPP has **no outgoing mail server**, so the email won't actually send. While
     `PASSWORD_RESET_DEV_SHOW_LINK` is `true` in [config/database.php](config/database.php), the link is
     shown directly on the page so you can still reset locally.
   - **Set `PASSWORD_RESET_DEV_SHOW_LINK` to `false` on any public server** and configure real mail,
     otherwise anyone who knows an admin email can take over the account.
3. **Offline CLI reset** (total lock-out, run from the project root):
   ```
   C:\xampp\php\php.exe bin\reset_admin_password.php admin "NewSecret#2026"
   ```

### Applying the database updates

If you already created `tepan_db` before these features existed, run the migrations once (in order):
```
C:\xampp\mysql\bin\mysql.exe -u root tepan_db < sql\migration_2026_09_authors.sql
C:\xampp\mysql\bin\mysql.exe -u root tepan_db < sql\migration_2026_09_password_reset.sql
C:\xampp\mysql\bin\mysql.exe -u root tepan_db < sql\migration_2026_09_modules.sql
C:\xampp\php\php.exe bin\seed_content.php        REM fills the policy pages with starter text
```
Fresh installs get the schema from `sql\schema.sql`, then run `bin\seed_content.php` for the policy text.

## Features

### Journal
- Public archive: Home, Journals (Current Issue / by Volume / by Year / by Author), single issue page + PDF
  download, ISSN/eISSN shown site-wide.
- Admin: upload/edit/delete issues (PDF + cover, volume/issue/date, editor, author credit, "current" flag).

### Manuscript submission & peer review (tokenless — no author/reviewer accounts)
- `submit.php` — authors submit a manuscript (anonymised file + metadata); they get a reference number.
- `submission-status.php` — authors check progress with reference + email; decision letters appear here.
- Admin → **Submissions**: screen, set status, invite reviewers (a single-use `review.php?token=` link is
  generated — emailed if mail works, otherwise shown to the editor to send manually), read reviews, record
  and email an editor decision. Every step is logged in a per-submission history.
- `review.php?token=…` — reviewer downloads the manuscript and submits a recommendation + comments
  (to author / confidential to editor), or declines.

### Editorial board
- `editorial-board.php` — grouped Editor-in-Chief / Associate Editors / Board Members with photo,
  affiliation, country, bio, ORCID, email. Admin → **Editorial Board**.

### Policy & guideline pages (editable, COPE-aligned starter text)
- `page.php?slug=…` renders each. Seeded: aims-and-scope, author-guidelines, publication-ethics,
  peer-review-policy, open-access-licensing, copyright-agreement, plagiarism-policy, archiving-policy.
- Admin → **Policy Pages** (edit HTML body, publish/hide, reorder, add new).
- Admin → **Author Resources**: upload the Manuscript Template, Copyright/Author Agreement form, etc.;
  they appear on `author-resources.php`.

### Conferences
- `conferences.php` / `conference.php?slug=…` — editions with theme, dates, venue, **Call for Papers**,
  **Registration** (attendee or presenter-with-abstract), and linked proceedings.
- `conference-register.php` — public registration; fees shown as text, admin marks payment
  (unpaid / paid / waived) and can export registrations to CSV. Admin → **Editions & CFP**.
- Proceedings uploads can now be linked to an edition.

### Announcements & mailing list
- `announcements.php` / `announcement.php?slug=…`; latest items also show on the home page.
- Double opt-in subscribe (footer + announcements page) → `subscribe-confirm.php` / `unsubscribe.php`.
- Admin → **Announcements**: write, publish, and **Email subscribers** (sends the announcement to all
  confirmed addresses via `mail()`); **Subscribers** list with CSV export.

### Email
- All outgoing mail goes through `send_mail()` and is **best-effort** — stock XAMPP has no mail server.
  While `MAIL_DEV_SHOW_LINKS` is `true` in `config/database.php`, confirmation/action links are shown on
  screen (or in the admin) so every flow can be completed locally. Set it `false` with real mail in
  production.

### Storage
- Uploads live under `/uploads/{journals,proceedings,covers,manuscripts,supplementary,resources,board,conferences}`
  and are served through `download.php` (journals, proceedings, resources) or token-gated scripts
  (manuscripts). `/uploads` blocks script execution via `.htaccess`.

## Project structure
```
TEPA/
├── admin/                 Login-protected portal: journals, submissions, board, pages, resources,
│                          conferences, registrations, announcements, subscribers, messages, account
├── assets/                CSS / JS / images
├── bin/                   CLI utilities: reset_admin_password.php, seed_content.php
├── config/database.php    DB connection + site settings (ISSN, mail, password-reset flags)
├── includes/              header / footer / functions / upload / subscribe_form
├── uploads/               journals, proceedings, covers, manuscripts, supplementary, resources, board, conferences
├── sql/                   schema.sql + dated migration files + (run bin/seed_content.php for page text)
├── Public pages: index, journals, journal-view, proceedings, about, contact,
│                 submit, submission-status, review, editorial-board, author-resources, page,
│                 conferences, conference, conference-register,
│                 announcements, announcement, subscribe, subscribe-confirm, unsubscribe
└── download.php           Tracked download endpoint (journals / proceedings / resources)
```

## Notes
- No sample/demo journals or proceedings were left in the database — the archive starts empty and is
  populated entirely through the admin portal.
- Update `SITE_NAME`, `BASE_URL`, and the favicon letter in `config/database.php` / `includes/header.php`
  if you rename or relocate the project folder.
