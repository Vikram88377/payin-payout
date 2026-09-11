# Laravel Pay-in & Payout Module

A backend module for handling merchant pay-ins and payouts, with wallet balance
management, a cron-based payment processor, and a Backpack admin panel.

## Requirements

- PHP >= 8.2 with common extensions (`pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `ctype`, `fileinfo`)
- Composer
- MySQL  (or MariaDB)
- Node.js + npm (only if your Backpack theme needs asset building — most themes work via CDN/Basset without this)



## Features

- Merchant, Wallet, Payin, Payout management
- API endpoints to initiate pay-ins/payouts
- Cron job that picks up PENDING payments and randomly resolves them to
  SUCCESS / FAILED / PENDING
- Wallet balance updates on successful payments, with row locking and a
  transaction ledger to prevent double-processing
- Event-driven logging of status changes
- Backpack admin panel for Merchants, Payins, Payouts, Wallets, with a custom
  filter bar (status / merchant / date range)

## Setup Instructions

### 1. Clone and install dependencies

```bash
git clone https://github.com/Vikram88377/payin-payout
cd payin-payout
composer install
```

### 2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payin_payout
DB_USERNAME=root
DB_PASSWORD=
```

#    Leave APP_URL as-is: APP_URL=http://127.0.0.1:8000
#    (matches the default `php artisan serve` address used below — changing
#    it will break Backpack's generated asset URLs)

Create the database if it doesn't exist yet:

```bash
mysql -u root -e "CREATE DATABASE payin_payout"
```

### 3. Migrate and seed

```bash
php artisan migrate:fresh --seed
```


- An admin login for the Backpack panel — **email: `admin@gmail.com`, password: `Admin@123`


### 4. Install Backpack assets


# Clear cached config/routes/views and pre-generate Backpack's admin theme

#    runtime — skipping this step is the #1 cause of a broken/unstyled
#    admin panel right after a fresh clone

php artisan optimize:clear
php artisan basset:clear
php artisan storage:link
php artisan storage:link
php artisan basset:cache
```

### 5. Run the app

```bash
php artisan serve
```

- API base URL: ` http://127.0.0.1:8000/api`
- Admin panel: `http://127.0.0.1:8000/admin`

### 6. Run the scheduler (for the cron job)

For local testing, run the scheduler in the foreground:

```bash
php artisan schedule:work
```

Or trigger the cron command manually, any time:

```bash
php artisan payments:process-pending
```

In production, add this to your server's crontab:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Admin Panel

Log in at `http://127.0.0.1:8000/admin/login` with the seeded admin user
(`admin@gmail.com` / `password` — see step 3 above). Once logged in,
you'll find:

- **Merchants** — full CRUD, filter by status
- **Payins** — read-only (created via API/cron), filter by status, merchant, date
- **Payouts** — read-only, filter by status, merchant, date
- **Wallets** — read-only, filter by merchant

## Logs

Payment-specific events (initiated, status changed, credited/debited, errors)
are written to a dedicated log channel, separate from the default Laravel log:

```
storage/logs/payments-YYYY-MM-DD.log
```

## API Documentation

See [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) for endpoint details and
sample requests/responses.

## Project Structure (key folders)

```
app/
  Console/Commands/ProcessPendingPayments.php   cron logic
  Events/PaymentStatusChanged.php
  Listeners/LogPaymentStatusChange.php
  Helpers/TransactionIdGenerator.php
  Http/Controllers/Api/                         API controllers
  Http/Controllers/Admin/                        Backpack CRUD controllers
  Http/Requests/                                 validation
  Models/
  Services/                                      business logic
database/
  migrations/
  seeders/
resources/views/admin/filters/                   custom filter bar UI
routes/api.php
routes/backpack/custom.php
```

## Notes

- Backpack's built-in "Filters" bar is a PRO-only feature. I use
  a small custom filter UI (Backpack Widgets + a plain GET form) instead, so
  everything works on the free/community version.
- Duplicate processing is guarded at three levels: a `wallet_credited` /
  `wallet_debited` boolean flag on the payin/payout row, a row lock
  (`lockForUpdate`) during cron processing, and a unique DB constraint on the
  wallet ledger (`wallet_transactions`) tying each entry to exactly one
  source payin/payout.