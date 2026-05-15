# ARS Easy Shopping — Mobile API v1

Customer-facing REST API consumed by the React Native mobile app.

**Base URL:** `https://easyshoppingars.com/api/v1`

---

## Authentication

### JWT Bearer Token

All protected endpoints require an `Authorization` header:

```
Authorization: Bearer <your_jwt_token>
```

**Flow:**
1. `POST /auth/register` — create account (user is `pending`)
2. `POST /auth/verify-otp` — verify phone, user becomes `active`, receive JWT
3. `POST /auth/login` — login with phone + password, receive JWT
4. Include `Authorization: Bearer <token>` on all protected endpoints

Tokens expire after 7 days. Login again to get a new token.

---

## Standard Response Format

### Success
```json
{
    "success": true,
    "data": { ... },
    "message": "Action completed"
}
```

### Error
```json
{
    "success": false,
    "message": "Something went wrong",
    "errors": {
        "email": ["Invalid email format"],
        "phone": ["Phone is required"]
    }
}
```

### Paginated
```json
{
    "success": true,
    "data": [ ... ],
    "pagination": {
        "total": 50,
        "page": 1,
        "last_page": 3,
        "per_page": 20
    }
}
```

---

## Endpoints

### Auth (public)

#### POST /auth/register
```
Body: { "name": "John Doe", "phone": "9812345678", "email": "john@example.com", "password": "secret123" }
Response 201: { "success": true, "data": { "user_id": 5, "otp_sent": true } }
```

#### POST /auth/verify-otp
```
Body: { "phone": "9812345678", "otp": "482916" }
Response: { "success": true, "data": { "token": "eyJ...", "user": { "id": 5, "name": "John Doe", "phone": "9812345678", "email": "john@example.com" } } }
```

#### POST /auth/login
```
Body: { "phone": "9812345678", "password": "secret123" }
Response: { "success": true, "data": { "token": "eyJ...", "user": { ... } } }
```

#### POST /auth/resend-otp
```
Body: { "phone": "9812345678" }
Response: { "success": true, "data": { "otp_sent": true } }
```

### Auth (protected — Bearer token required)

#### POST /auth/logout
```
Headers: Authorization: Bearer <token>
Response: { "success": true, "message": "Logged out successfully" }
```

#### POST /auth/change-password
```
Headers: Authorization: Bearer <token>
Body: { "current_password": "old123", "new_password": "new45678" }
Response: { "success": true, "message": "Password changed successfully" }
```

### Products (public)

#### GET /products
```
Query: ?page=1&limit=20&category=1&search=mouse&sort=newest
Sort options: newest, price_asc, price_desc, popular
```

#### GET /products/{id}
```
Response: { "success": true, "data": { "product": { ...full fields... }, "reviews": [...] } }
```

#### GET /products/featured
#### GET /products/new-arrivals

### Categories (public)

#### GET /categories
```
Response: { "success": true, "data": [{ "id": 1, "name": "Electronics", "slug": "electronics", "product_count": 5 }] }
```

### Banners (public)

#### GET /banners
```
Response: { "success": true, "data": [{ "id": 1, "image": "https://...", "title": "...", "subtitle": "...", "link_type": "product", "link_value": "1" }] }
```

### Orders (protected)

#### POST /orders
```
Headers: Authorization: Bearer <token>
Body: { "items": [{ "product_id": 1, "quantity": 2 }], "address_id": 1, "payment_method": "COD" }
Response 201: { "success": true, "data": { "order_id": 4, "order_number": "ARS-2026-000004", "total": 5000.00 } }
Payment methods: COD, eSewa, BankQR
```

#### GET /orders
```
Headers: Authorization: Bearer <token>
Query: ?page=1&status=Pending
```

#### GET /orders/{id}
```
Headers: Authorization: Bearer <token>
Response: { "success": true, "data": { "order": {...}, "items": [...], "status_history": [...], "address": {...}, "payment": {...} } }
```

### User (protected)

#### GET /user/me
#### PATCH /user/me
```
Body: { "name": "New Name", "email": "new@email.com" }
```

### Addresses (protected)

#### GET /user/addresses
#### POST /user/addresses
```
Body: { "full_name": "John Doe", "phone": "9812345678", "province": "Province 2", "district": "Parsa", "municipality": "Birgunj", "ward": "07", "street": "Main Road", "tag": "Home", "is_default": true }
```
#### PATCH /user/addresses/{id}
#### PATCH /user/addresses/{id}/set-default
#### DELETE /user/addresses/{id}

---

## Testing with curl

```bash
# Register
curl -X POST https://easyshoppingars.com/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","phone":"9812345678","password":"secret123"}'

# Verify OTP
curl -X POST https://easyshoppingars.com/api/v1/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"phone":"9812345678","otp":"123456"}'

# Login
curl -X POST https://easyshoppingars.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone":"9812345678","password":"secret123"}'

# Products (no auth)
curl https://easyshoppingars.com/api/v1/products

# Orders (with auth)
curl -X POST https://easyshoppingars.com/api/v1/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_HERE" \
  -d '{"items":[{"product_id":1,"quantity":1}],"payment_method":"COD"}'

# User profile (with auth)
curl https://easyshoppingars.com/api/v1/user/me \
  -H "Authorization: Bearer TOKEN_HERE"
```
