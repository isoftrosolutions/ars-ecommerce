# Easy Shopping A.R.S Admin API

Production-ready REST API for admin panel functionality.

## Base URL
```
/ars/api/
```

## Authentication
All admin endpoints require authentication. Use session-based authentication.

### Login
```http
POST /ars/api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

### Check Session
```http
POST /ars/api/auth/check
```

## Rate Limiting
- Dashboard: 60 requests/hour
- Products: 100 requests/hour
- Categories: 50 requests/hour
- Contact: 100 requests/hour
- Settings: 30 requests/hour

## Endpoints

### Dashboard
```http
GET /ars/api/dashboard/stats
```
Returns dashboard statistics including revenue, orders, customers, etc.

### Products
```http
GET  /ars/api/products/list?page=1&limit=10&search=apple&category_id=1
GET  /ars/api/products/detail?id=123
POST /ars/api/products/create
POST /ars/api/products/update
POST /ars/api/products/delete
POST /ars/api/products/bulk-delete
POST /ars/api/products/toggle-featured
GET  /ars/api/products/categories
```

### Categories
```http
GET  /ars/api/categories/list
GET  /ars/api/categories/detail?id=123
GET  /ars/api/categories/stats
POST /ars/api/categories/create
POST /ars/api/categories/update
POST /ars/api/categories/delete
POST /ars/api/categories/generate-slug
```

### Orders
```http
GET  /ars/api/orders/list?page=1&limit=10&search=order&status=pending
GET  /ars/api/orders/detail?id=123
POST /ars/api/orders/update-status
POST /ars/api/orders/update-payment-status
POST /ars/api/orders/update-location
```

### Customers
```http
GET  /ars/api/customers/list?page=1&limit=10&search=email
GET  /ars/api/customers/detail?id=123
GET  /ars/api/customers/stats
```

### Reviews
```http
GET  /ars/api/reviews/list?page=1&limit=10&status=pending&rating=5
POST /ars/api/reviews/update-status
POST /ars/api/reviews/delete
POST /ars/api/reviews/bulk-update-status
POST /ars/api/reviews/bulk-delete
```

### Coupons
```http
GET  /ars/api/coupons/list?page=1&limit=10&status=active&type=fixed
GET  /ars/api/coupons/detail?id=123
GET  /ars/api/coupons/stats
POST /ars/api/coupons/create
POST /ars/api/coupons/update
POST /ars/api/coupons/delete
POST /ars/api/coupons/toggle-status
```

### Contact
```http
GET  /ars/api/contact/list?page=1&limit=10&search=email&status=new
GET  /ars/api/contact/detail?id=123
GET  /ars/api/contact/stats
POST /ars/api/contact/update-status
POST /ars/api/contact/send-reply
POST /ars/api/contact/delete
POST /ars/api/contact/bulk-update-status
POST /ars/api/contact/bulk-delete
```

### Settings
```http
GET  /ars/api/settings/all
GET  /ars/api/settings/defaults
POST /ars/api/settings/update
POST /ars/api/settings/bulk-update
```

### Uploads
```http
POST /ars/api/uploads/images
POST /ars/api/uploads/files
```

## Response Format
All responses follow this structure:
```json
{
  "success": true,
  "message": "Operation completed",
  "data": { ... },
  "pagination": { ... }
}
```

## Error Handling
```json
{
  "success": false,
  "message": "Error description"
}
```

## Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 429: Rate Limited
- 500: Internal Server Error

## Features
- ✅ Authentication & Authorization
- ✅ Rate Limiting
- ✅ Input Validation & Sanitization
- ✅ Comprehensive Error Handling
- ✅ Database Transactions
- ✅ Audit Logging
- ✅ CORS Support
- ✅ Pagination
- ✅ File Upload Handling
- ✅ CSRF Protection

## Security
- Admin-only access
- Rate limiting per endpoint
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- CSRF token validation
- Secure session management

## Database Tables Used
- users (admin authentication)
- products, product_images
- categories
- orders, order_items
- product_reviews
- coupons
- contact_submissions
- site_settings
- user_sessions
- email_logs, email_queue, email_templates

## Migration Notes
The API replaces the previous backend PHP files that were deleted:
- backend/dashboard.php → api/dashboard/
- backend/products.php → api/products/
- backend/categories.php → api/categories/
- backend/orders.php → api/orders/
- backend/customers.php → api/customers/
- backend/reviews.php → api/reviews/
- backend/coupons.php → api/coupons/
- backend/contact.php → api/contact/
- backend/settings.php → api/settings/

## Frontend Integration
Update your JavaScript fetch calls to use the new API endpoints:
```javascript
// Old way
fetch('/backend/products.php', { ... })

// New way
fetch('/api/products/list', { ... })
```

The API maintains the same request/response format for easy migration.