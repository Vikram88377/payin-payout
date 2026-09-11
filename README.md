# Pay-in & Payout Module (Laravel + Backpack)

A backend-focused Pay-in / Payout module built with Laravel 13 and Laravel Backpack. Merchants can
initiate pay-ins and payouts via API; a scheduled cron job then processes PENDING transactions,
randomly resolving each to `SUCCESS`, `FAILED`, or `PENDING` (re-picked on the next run), and updates
merchant wallet balances atomically — with database-level guards against double-crediting/debiting.

---

## 1. Requirements

- PHP >= 8.3 with common extensions (`pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `ctype`, `fileinfo`)
- Composer
- MySQL
- Node.js 18+ & npm (only needed for the Backpack admin theme assets)

---

## 2. Setup — clone & run

```bash
# 1. Clone the repo
git clone https://github.com/Vikram88377/payin-payout
cd payin-payout

# 2. Install PHP dependencies
composer install

# 3. Copy the environment file and generate an app key
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=payin_payout
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Create the database (if it doesn't exist yet)
mysql -u root -e "CREATE DATABASE payin_payout"

# 6. Run migrations + seeders (creates sample merchants, wallets, payins, payouts)
php artisan migrate --seed

# 7. Install JS dependencies & build assets (needed for the Backpack admin UI)
npm install
npm run build

# 8. Serve the app
php artisan serve
# API base URL: http://127.0.0.1:8000/api
# Admin panel:  http://127.0.0.1:8000/admin
```

### Backpack admin login

Backpack's default auth uses the `users` table (`CheckIfAdmin` middleware treats any logged-in user
as an admin — see `app/Http/Middleware/CheckIfAdmin.php`). An admin user is created automatically by
`AdminUserSeeder` when you run `php artisan migrate --seed`:

- Email: `admin@gmail.com`
- Password: `Admin@123`

Log in at `http://127.0.0.1:8000/admin/login`.

---

## 3. Running the payment-processing cron

Pending payins/payouts are picked up by the `payments:process-pending` Artisan command, scheduled to
run **every minute** (see `routes/console.php`).

**Local development** — run the scheduler worker in a separate terminal (auto-runs due tasks every minute):

```bash
php artisan schedule:work
```

**Production** — add a single cron entry that ticks Laravel's scheduler every minute:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Run once manually** (useful for testing without waiting a minute):

```bash
php artisan payments:process-pending
```

---
          4. API Documentation / Sample Requests

          All responses are JSON. Base URL: http://127.0.0.1:8000/api

          A ready-to-import Postman collection is included: Payin-Payout.postman_collection.json (repo root). It has all 5 endpoints below pre-configured with sample bodies and collection variables (base_url, merchant_id, payin_txn_id, payout_txn_id) so you can chain requests — create a payin, copy its transaction_id into the payin_txn_id variable, then check its status. Import it in Postman via Import → File.

          Create a Pay-in

          POST /api/payins

          Body (raw JSON):

          json
          {
            "merchant_id": 1,
            "amount": 500,
            "currency": "INR",
            
          }

          Response 201:

          json
          {
            "success": true,
            "message": "Payin initiated successfully.",
            "data": {
              "transaction_id": "PIN2026091100012AB3",
              "status": "PENDING",
              "amount": "500.00"
            }
          }
          Check Pay-in status

          GET /api/payins/{transaction_id}

          Create a Payout

          POST /api/payouts

          Body (raw JSON):

          json
          {
            "merchant_id": 1,
            "amount": 200,
            "currency": "INR"
          }

          Fails with 422 if the merchant's wallet balance is insufficient.

          Check Payout status

          GET /api/payouts/{transaction_id}

          Check merchant wallet balance

          GET /api/merchants/{id}/wallet

          Validation error example

          422:

          json
          {
            "success": false,
            "message": "Validation failed.",
            "errors": {
              "amount": ["Amount must be at least 1."]
            }
          }
## 5. Project structure

```
app/
  Console/Commands/ProcessPendingPayments.php   # cron: resolves PENDING payins/payouts
  Events/PaymentStatusChanged.php
  Listeners/LogPaymentStatusChange.php
  Helpers/TransactionIdGenerator.php
  Http/Controllers/Api/                         # Payin, Payout, Merchant API controllers
  Http/Controllers/Admin/                       # Backpack CRUD controllers
  Http/Requests/                                # form request validation
  Models/                                        # Merchant, Wallet, Payin, Payout, WalletTransaction
  Services/                                       # PayinService, PayoutService, WalletService
database/
  migrations/                                     # merchants, wallets, payins, payouts, wallet_transactions
  seeders/                                         # AdminUserSeeder, MerchantSeeder, PayinPayoutSeeder
routes/
  api.php                                          # API endpoints
  console.php                                      # scheduler registration
  backpack/custom.php                              # admin CRUD routes
```

---

## 6. Logging

Payment events are written to a dedicated `payments` log channel:
`storage/logs/payments-*.log` (daily rotation, 14 days retention). This includes payment initiation,
status changes, wallet credit/debit events, and processing errors.

---

## 7. Duplicate-processing safety

- Each payin/payout row is row-locked (`lockForUpdate`) inside a DB transaction before its status is
  changed, so two overlapping cron runs can't process the same record twice.
- `wallet_credited` / `wallet_debited` boolean flags on payins/payouts act as an idempotency guard.
- The `wallet_transactions` ledger has a unique constraint on `(reference_type, reference_id)`,
  enforcing at the database level that a given payin/payout can only ever produce one ledger entry.
