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

**Change this password immediately** by updating the `admins` table (or add a "change password" screen) —
it is a placeholder credential set during initial setup.

## Features
- **Public site**: Home, Journals (Current Issue / Browse by Volume / Browse by Year), a single journal
  detail + download page, Conference Proceedings (separate from journals, filterable by year), About, Contact
  (messages are saved and viewable in the admin portal).
- **Admin portal** (`/admin`): dashboard with stats, upload/edit/delete journal issues (PDF + optional cover
  image, volume/issue/date, "current issue" flag), upload/edit/delete conference proceedings (PDF + optional
  cover image), and a contact-message inbox.
- Uploaded files are stored under `/uploads/journals`, `/uploads/proceedings`, `/uploads/covers` and served
  through `download.php`, which also tracks a per-item download counter. The `/uploads` folder blocks script
  execution via `.htaccess`.

## Project structure
```
TEPA/
├── admin/                 Admin portal (login-protected)
├── assets/                CSS/JS
├── config/database.php    DB connection settings
├── includes/              Shared header/footer/helpers/upload logic
├── uploads/                Uploaded PDFs & cover images
├── sql/schema.sql         Database schema
├── index.php, journals.php, journal-view.php, proceedings.php, about.php, contact.php
└── download.php           Tracked file download endpoint
```

## Notes
- No sample/demo journals or proceedings were left in the database — the archive starts empty and is
  populated entirely through the admin portal.
- Update `SITE_NAME`, `BASE_URL`, and the favicon letter in `config/database.php` / `includes/header.php`
  if you rename or relocate the project folder.
