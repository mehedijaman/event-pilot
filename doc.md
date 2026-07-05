# Shared Hosting (cPanel) Setup Guide

This guide covers deploying the Event Ticket app on shared hosting with cPanel, where background queue servers (like Redis or Supervisor) are not available.

---

## Queue Setup (Database Driver)

The app uses the **database** queue driver by default. Instead of a long-running queue server, we use a **cron job** that runs every minute to process queued jobs (ticket emails, PDF generation, etc.).

### 1. Ensure QUEUE_CONNECTION is set in `.env`

```
QUEUE_CONNECTION=database
```

This is already the default — no change needed unless you modified it.

### 2. Run database migrations

The `jobs`, `failed_jobs`, and `job_batches` tables must exist. Run via SSH or cPanel Terminal:

```bash
php artisan migrate --force
```

If you don't have SSH access, export the migration SQL from a local environment and import it via phpMyAdmin.

### 3. Set up the cron job in cPanel

1. Log in to **cPanel**
2. Open **Cron Jobs** (under "Advanced")
3. Set the cron schedule to **Every minute** (`* * * * *`)
4. Add the following command:

```bash
cd /home/youruser/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

Replace the path with your actual project root directory.

### 4. Add the scheduler entry to `routes/console.php`

The `schedule:run` command needs a scheduled task to process. Add this to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --sleep=3 --tries=3 --timeout=60')->everyMinute();
```

This runs `queue:work` every minute, picking up pending jobs from the `jobs` table. The `--sleep=3` flag reduces CPU usage when idle.

### 5. Alternative: Direct cron (if scheduler doesn't work)

Some shared hosts don't support `schedule:run`. In that case, run the queue worker directly:

```bash
cd /home/youruser/domains/yourdomain.com/public_html && php artisan queue:work --sleep=3 --tries=3 --timeout=60 >> /dev/null 2>&1
```

Set this as a **every minute** cron job in cPanel.

> **Note:** This approach starts a new process every minute. The `--timeout=60` ensures each process terminates within 60 seconds, preventing zombie processes.

---

## Mail Configuration

The app sends ticket emails with PDF attachments via SMTP. Configure in `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Match Day Live"
```

For **Gmail**: Generate an [App Password](https://myaccount.google.com/apppasswords) (requires 2FA enabled).

For **cPanel email**: Use your cPanel SMTP credentials (port 465 with SSL, or 587 with TLS).

---

## File Storage

The app stores uploaded files (logos, cover photos) locally. Ensure the storage directory is writable:

```bash
chmod -R 775 storage bootstrap/cache
chown -R youruser:youruser storage bootstrap/cache
```

If uploads don't work, create a symlink:

```bash
php artisan storage:link
```

---

## Environment Checklist

| Setting | Value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://yourdomain.com` |
| `QUEUE_CONNECTION` | `database` |
| `MAIL_MAILER` | `smtp` |
| `FILESYSTEM_DISK` | `local` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |

---

## Troubleshooting

### Emails not sending

1. Check the `failed_jobs` table for errors: `SELECT * FROM failed_jobs;`
2. Verify SMTP credentials in `.env`
3. Test mail: `php artisan tinker --execute 'Mail::raw("Test", fn($m) => $m->to("you@email.com")->subject("Test"));'`

### Cron job not running

1. Verify the cron entry in cPanel → Cron Jobs → "Current Cron Jobs"
2. Check cron output: replace `>> /dev/null 2>&1` with `>> /home/youruser/cron.log 2>&1` temporarily
3. Ensure PHP path is correct — run `which php` via SSH to find it

### Jobs stuck in "pending"

1. Confirm the cron job is running (`ps aux | grep queue:work`)
2. Run manually: `php artisan queue:work --once`
3. Check `php artisan queue:status`

### Storage/link issues

```bash
php artisan storage:link --force
chmod -R 775 storage/app/public
```
