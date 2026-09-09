# TEPAN – Email / Mail Server Setup

The website sends email for: manuscript submission confirmations, reviewer invitations,
editor decisions, contact‑form copies, newsletter double opt‑in, "new issue" broadcasts and
admin password resets.

Every one of these goes through a single function — `send_mail()` in
`includes/functions.php` — which wraps PHP's `mail()`. Password‑reset mail goes through
`password_reset_send_email()` in the same file. So there is **one place** the mail transport
is decided, and two ways to make it real:

| Route | Touches app code? | Best for |
|-------|-------------------|----------|
| **A. XAMPP `sendmail` → SMTP relay** | No | Getting it working fast on this XAMPP box |
| **B. PHPMailer + SMTP inside the app** | Yes (small) | Production / moving to another host |

Do **Route A** now. Consider **Route B** before going public.

---

## Route A — XAMPP sendmail relay (no code changes)

XAMPP ships a small "fake sendmail" (`C:\xampp\sendmail\sendmail.exe`) that forwards mail to a
real SMTP server. You configure it once and `mail()` starts working everywhere.

### A1. Configure the SMTP account

Edit **`C:\xampp\sendmail\sendmail.ini`** and replace the `[sendmail]` section.

**Gmail / Google Workspace**

```ini
[sendmail]
smtp_server=smtp.gmail.com
smtp_port=587
smtp_ssl=tls
auth_username=youraccount@gmail.com
auth_password=xxxxxxxxxxxxxxxx
force_sender=youraccount@gmail.com
error_logfile=C:\xampp\sendmail\error.log
debug_logfile=C:\xampp\sendmail\debug.log
```

- `auth_password` must be a **Google App Password**, not your normal password:
  1. Turn on **2‑Step Verification** for the account (Google Account → Security).
  2. Google Account → Security → **App passwords** → create one for "Mail" / "Windows Computer".
  3. Paste the 16 characters (no spaces) as `auth_password`.
- Gmail ignores `force_sender` for the visible *From* and rewrites it to the authenticated
  address. That is normal.

**A domain mailbox** (cPanel, Zoho, Namecheap Private Email, Microsoft 365, etc.)

```ini
[sendmail]
smtp_server=mail.tepan.org.ng
smtp_port=465
smtp_ssl=ssl
auth_username=no-reply@tepan.org.ng
auth_password=THE-MAILBOX-PASSWORD
force_sender=no-reply@tepan.org.ng
error_logfile=C:\xampp\sendmail\error.log
debug_logfile=C:\xampp\sendmail\debug.log
```

- Get the exact host, port and SSL/TLS mode from your mail provider's "email client / IMAP‑SMTP
  settings" page. Common combos: `587` + `tls`, or `465` + `ssl`.
- With a real domain mailbox the *From* address **is** honoured, so mail arrives as
  `no-reply@tepan.org.ng`.
- For best deliverability, ask whoever manages the `tepan.org.ng` DNS to add an **SPF** record
  that authorises your provider, and **DKIM** if the provider offers it.

### A2. Tell PHP to use sendmail

Edit **`C:\xampp\php\php.ini`**. Find the `[mail function]` section and make it look like this
(comment out the Windows‑only `SMTP` lines, set `sendmail_path`):

```ini
[mail function]
; SMTP = localhost
; smtp_port = 25
; sendmail_from = no-reply@tepan.org.ng
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
mail.add_x_header = Off
```

There may already be a `sendmail_path` line further down that is commented out — either edit that
one or add the line above; just make sure only one is active.

### A3. Restart Apache

XAMPP Control Panel → **Stop** Apache → **Start** Apache. `php.ini` is only read at start‑up.

### A4. Switch the site out of "local mode"

Edit **`C:\xampp\htdocs\TEPA\config\database.php`**:

```php
// --- Outgoing email ---
define('MAIL_FROM', 'no-reply@tepan.org.ng');   // your Gmail address if using Gmail
define('MAIL_ENABLED', true);
define('MAIL_DEV_SHOW_LINKS', false);           // stop printing confirm/reset links on screen

// --- Admin password reset ---
define('PASSWORD_RESET_TTL_MINUTES', 60);
define('PASSWORD_RESET_FROM', 'no-reply@tepan.org.ng');
define('PASSWORD_RESET_DEV_SHOW_LINK', false);
```

Leave `MAIL_DEV_SHOW_LINKS` / `PASSWORD_RESET_DEV_SHOW_LINK` at `true` only while testing locally
without a working relay — with them `true`, anyone who can reach the site can complete a
subscription or seize an admin account, because the secret link is shown on the page.

### A5. Test

1. **Password reset:** open `http://localhost/TEPA/admin/login.php` → *Forgot your password?* →
   enter the admin recovery email → check that inbox (and Spam the first time).
2. **Newsletter:** submit the footer "Subscribe" form with a real address → expect a
   "Confirm your subscription" email.
3. **Submission:** send a test manuscript via `submit.php` → expect a confirmation to the author
   address and a notice to `CONTACT_EMAIL`.

If nothing arrives, see **Troubleshooting** below.

---

## Route B — PHPMailer + SMTP inside the app (recommended for production)

This keeps all mail configuration in the project (portable to any host), gives real error
messages, and does not depend on `C:\xampp\php\php.ini`.

### B1. Add the library

No Composer in this project, so vendor it:

1. Download the latest PHPMailer release ZIP from
   <https://github.com/PHPMailer/PHPMailer/releases>.
2. Copy its `src` folder to `C:\xampp\htdocs\TEPA\lib\PHPMailer\` so you have:
   ```
   TEPA/lib/PHPMailer/PHPMailer.php
   TEPA/lib/PHPMailer/SMTP.php
   TEPA/lib/PHPMailer/Exception.php
   ```

### B2. Add SMTP settings

Keep the password **out of Git**. Create **`config/mail.local.php`** (add this filename to
`.gitignore`):

```php
<?php
return [
    'host'       => 'smtp.gmail.com',   // or mail.tepan.org.ng
    'port'       => 587,                // 587 = STARTTLS, 465 = SMTPS
    'encryption' => 'tls',              // 'tls' or 'ssl'
    'username'   => 'youraccount@gmail.com',
    'password'   => 'your-app-password',
    'from_email' => 'no-reply@tepan.org.ng',
    'from_name'  => 'TEPAN',
];
```

In `config/database.php` add:

```php
define('MAIL_TRANSPORT', 'smtp');   // 'smtp' = use PHPMailer, 'mail' = use PHP mail()
```

### B3. Replace the mail functions

In `includes/functions.php`, replace `send_mail()` with a version that uses PHPMailer when
`MAIL_TRANSPORT === 'smtp'`, and update `password_reset_send_email()` to call `send_mail()`
instead of `mail()` directly. (Ask Claude to make this edit — it is about 30 lines and needs the
`config/mail.local.php` values wired in.)

### B4. Test

Same as **A5**. PHPMailer will throw a descriptive `PHPMailer\PHPMailer\Exception` on failure,
so temporarily enabling `$mail->SMTPDebug = 2;` shows the full SMTP conversation.

---

## Troubleshooting

**Nothing sends, no error**
`mail()` returns `false` silently. Check `C:\xampp\sendmail\error.log` and
`C:\xampp\sendmail\debug.log` (paths set in `sendmail.ini`).

**`sendmail.exe` not found / mail vanishes**
Confirm `sendmail_path` in the *active* `php.ini` (Apache may load a different one — run a page
with `<?php phpinfo();` and check "Loaded Configuration File"). Restart Apache after any change.

**Gmail: "Username and Password not accepted"**
You used the account password. Create and use an **App Password**, and make sure 2‑Step
Verification is on.

**Gmail: "534 5.7.9 Application-specific password required"**
Same fix — App Password.

**Mail sends but lands in Spam**
Expected from a fresh sender. For a domain mailbox, add **SPF** (and DKIM) DNS records for
`tepan.org.ng`. Keep the *From* address on the same domain as the SMTP account. Avoid
spammy subject lines while warming up.

**Timeout on port 25 / 587**
Some networks and hosts block outbound SMTP. Try `465` + `ssl`, or use your provider's
submission port. On a live server, your host may require their own relay.

**Port 465 vs 587**
`587` → `smtp_ssl=tls` (STARTTLS). `465` → `smtp_ssl=ssl` (implicit TLS). Match the port to the
mode or the handshake fails.

---

## Quick reference — files touched

| File | Route A | Route B |
|------|:------:|:------:|
| `C:\xampp\sendmail\sendmail.ini` | ✅ SMTP account | — |
| `C:\xampp\php\php.ini` (`[mail function]`) | ✅ `sendmail_path` | — |
| `config/database.php` | ✅ turn off dev link flags | ✅ `MAIL_TRANSPORT` |
| `config/mail.local.php` (new, git‑ignored) | — | ✅ SMTP creds |
| `lib/PHPMailer/*` (new) | — | ✅ library |
| `includes/functions.php` (`send_mail`, `password_reset_send_email`) | — | ✅ use PHPMailer |
| Restart Apache afterwards | ✅ | not required |
