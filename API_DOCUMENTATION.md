# API Documentation

Base URL: `http://127.0.0.1:8000/api`

All responses are JSON. All amounts are decimal strings/numbers in the
merchant's account currency (default INR).



---

## 1. Initiate a Pay-in

**POST** `/payins`

### Request body

```json
{
  "merchant_id": 1,
  "amount": 500,
  "currency": "INR"
}
```


### Success response — `201 Created`

```json
{
  "success": true,
  "message": "Payin initiated successfully.",
  "data": {
    "transaction_id": "PIN-20260911-A1B2C3D4",
    "status": "PENDING",
    "amount": "500.00"
  }
}
```

### Validation error — `422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "merchant_id": ["Merchant does not exist."]
  }
}
```

---

## 2. Check Pay-in Status

**GET** `/payins/{transaction_id}`

### Success response — `200 OK`

```json
{
  "success": true,
  "data": {
    "transaction_id": "PIN-20260911-A1B2C3D4",
    "status": "SUCCESS",
    "amount": "500.00",
    "created_at": "2026-09-11T10:15:00.000000Z",
    "processed_at": "2026-09-11T10:16:00.000000Z"
  }
}
```

### Not found — `404 Not Found`

```json
{
  "success": false,
  "message": "Payin not found."
}
```

---

## 3. Initiate a Payout

**POST** `/payouts`

### Request body

```json
{
  "merchant_id": 1,
  "amount": 200,
  "currency": "INR"
}
```

Same fields as pay-in, plus one extra validation: the amount cannot exceed
the merchant's current wallet balance.

### Success response — `201 Created`

```json
{
  "success": true,
  "message": "Payout initiated successfully.",
  "data": {
    "transaction_id": "POT-20260911-E5F6G7H8",
    "status": "PENDING",
    "amount": "200.00"
  }
}
```

### Validation error — `422 Unprocessable Entity`

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "amount": ["Insufficient wallet balance for this payout."]
  }
}
```

---

## 4. Check Payout Status

**GET** `/payouts/{transaction_id}`



---

## 5. Check Merchant Wallet Balance

**GET** `/merchants/{id}/wallet`

### Success response — `200 OK`

```json
{
  "success": true,
  "data": {
    "merchant_id": 1,
    "merchant_name": "Acme Traders",
    "balance": "3250.00"
  }
}
```

