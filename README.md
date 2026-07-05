# Event Pilot

A full-stack event registration and ticketing platform built with Laravel, Inertia.js, and Vue 3. Designed for event organizers to manage events, handle registrations with manual payment verification, generate PDF tickets with QR codes, and check in attendees at the venue.

## Features

### Public

- **Landing Page** — Event details with cover photo, packages, and pricing
- **Multi-Step Registration** — 5-step wizard: package, quantity (1-6), seat position, payment instructions, personal details
- **Group Tickets** — Register up to 6 tickets in a single transaction with auto-calculated total
- **PDF Tickets** — QR-code-equipped tickets generated on payment verification and emailed to attendees
- **Ticket Status** — Real-time payment status tracking (pending / verified / rejected)
- **Ticket Resend** — Re-send ticket email by entering email address

### Admin

- **Event Management** — CRUD for events with cover photo upload, inline package creation, capacity settings, and registration date windows
- **Registration Management** — Paginated list with status tabs, verify/reject with reasons
- **Check-In System** — Scan or type ticket code to validate and check in attendees
- **Payment Methods** — Manage payment destinations (bKash, Nagad, Rocket, bank, etc.)
- **Site Settings** — General branding, contact info, and SMTP configuration
- **Role-Based Access** — Admin (full access) and Scanner (check-in only)

### Security

- Two-Factor Authentication (TOTP)
- Passkey / WebAuthn support
- Rate limiting on registration, login, and ticket resend
- Encrypted SMTP credentials

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Vue 3, Inertia.js v3, TypeScript |
| Styling | Tailwind CSS v4, shadcn-vue |
| Build | Vite 8 |
| Database | SQLite (default), MySQL / PostgreSQL |
| Testing | Pest 4, PHPUnit 12 |
| PDF | DomPDF, Endroid QR Code |
| Media | Spatie Media Library |
| Auth | Laravel Fortify |

## Requirements

- PHP 8.3+
- Node.js 22+
- Composer
- SQLite, MySQL, or PostgreSQL

## Quick Start

```bash
# Clone the repository
git clone https://github.com/your-username/event-pilot.git
cd event-pilot

# Install and set up everything
composer setup
```

This runs `composer install`, generates the app key, runs migrations, installs npm dependencies, and builds assets.

### Manual Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed
npm install
npm run build
```

### Development

```bash
composer dev
```

Starts the Laravel dev server, queue worker, and Vite dev server concurrently.

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@event.test | password |
| Scanner | scanner@event.test | password |

## Testing

```bash
# Run all tests
php artisan test --compact

# Run specific test suite
php artisan test --compact --filter=RegistrationTest

# Static analysis
composer types:check

# Code formatting
vendor/bin/pint --format agent
npm run format
```

## Project Structure

```
app/
├── Actions/           # TicketPdf generation
├── Enums/             # StaffRole, PaymentStatus, SeatPosition
├── Events/            # PaymentVerified
├── Http/
│   ├── Controllers/   # Public + Admin controllers
│   └── Requests/      # Form request validation
├── Listeners/         # SendTicketOnPaymentVerified
├── Mail/              # TicketMail
├── Models/            # Event, Package, Registration, PaymentMethod, Setting, User
resources/
├── js/
│   ├── pages/         # Inertia Vue page components
│   │   ├── admin/     # Admin panels
│   │   ├── auth/      # Login, register, password reset
│   │   └── settings/  # Profile, security, appearance
│   └── components/    # Reusable Vue components
├── views/
│   ├── emails/        # Ticket email template
│   └── pdf/           # PDF ticket template
database/
├── factories/         # Model factories for testing
├── migrations/        # Database migrations
└── seeders/           # Sample data seeder
tests/
├── Feature/           # Feature tests (Pest)
└── Unit/              # Unit tests
```

## How It Works

1. **Admin creates an event** with packages, pricing, capacity, and payment methods
2. **Visitor registers** by selecting a package, quantity, seat position, making an off-platform payment, and submitting proof (transaction ID)
3. **Admin verifies** the payment from the registrations dashboard
4. **Ticket is generated** — a PDF with QR code is emailed to the attendee
5. **At the venue** — staff scans the QR code or enters the ticket code to check in

## Environment

Key `.env` settings for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
SESSION_DRIVER=database
CACHE_STORE=database
```

## Deployment

The project supports deployment to shared hosting (cPanel) or any Laravel-compatible server. See `doc.md` for a detailed shared hosting deployment guide.

**Queue setup:** The scheduler runs `queue:work` every minute via cron:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## License

This project is open-sourced under the MIT License.
