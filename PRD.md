# E-Commerce Website PRD (Client-Friendly Version)

**Project Name:** ARS-e-commerce (Easy Shopping A.R.S)  
**Prepared For:** Client  
**Prepared By:** Nepal Cyber Firm / iSoftro

## 1) Project Overview
The client needs a single-vendor e-commerce website where only one seller (admin) manages all products and many customers can browse and place orders. This is not a marketplace like Daraz or Amazon with multiple sellers. It is a single business online store.

- **Example:** One shop owner, Many customers, Admin controls everything.

## 2) Business Goal
The main purpose of the website is to:
- Showcase products professionally
- Allow buyers to order online
- Accept digital payments
- Manage orders easily
- Build trust with customers

This website should help the business sell products online 24/7.

## 3) User Roles

### A) Admin (Single Seller)
Only one admin account. This account controls the entire website.
- **Features:** Secure login, Dashboard overview, Add/Edit/Delete products, Upload images, Change prices, Update stock, Add discounts/offers, Manage customer orders, View payment details, Change banners/content.

### B) Buyer / Customer
Multiple users can register.
- **Features:** Sign up, Login, Browse/Search products, View details, Add to cart, Buy now, Place order, Payment options, Track order status, Order history.

## 4) Core Modules

### Module 1: Authentication System
- **Fields:** Full Name, Mobile Number, Address, Email, Password.
- **Methods:** Email/Mobile + Password.
- **Note:** OTP login recommended for the Nepal market as a future upgrade.

### Module 2: Product Management
- **Fields:** Product Name, Category, Description, Price, Discount Price, Images, Stock Status, SKU / Product Code.
- **Example:** Men’s Hoodie (Rs. 1500 -> Rs. 1200), Clothing, 20 in stock.

### Module 3: Product Display
- Homepage, Featured, Latest, Discounted products.
- Category-based listing.

### Module 4: Cart & Checkout
- **Cart:** Add/Remove, Quantity adjustment, Auto calculation.
- **Checkout Fields:** Name, Mobile, Delivery Address, Landmark, Payment Method, Notes.

### Module 5: Payment System
- **Option A (eSewa QR):** Scan QR, Upload screenshot or enter Transaction ID.
- **Option B (Bank QR):** Scan QR, Upload payment proof.
- **Option C (Cash on Delivery):** Highly recommended for the Nepal market.

### Module 6: Order Management
- **Dashboard:** Order ID, Customer details, Products, Payment status, Delivery status.
- **Statuses:** Pending, Paid, Confirmed, Shipped, Delivered, Cancelled.

### Module 7: Product Image Standardization
- Standardized look: White background, 1:1 square ratio, consistent lighting, e.g., 1000x1000px.

## 5) Recommended Pages
### Frontend
- Home, Shop, Product Details, Cart, Checkout, Order Success, Login/Signup, My Orders, Contact Us, About Us, Privacy Policy.

### Admin
- Dashboard, Products, Orders, Customers, Payments, Offers/Discounts, Settings.

## 6) Technical Recommendation
- **Tech Stack:** Vanilla PHP, MySQL/PDO, HTML/CSS/JS (as per UI prompt).
- **Alternative:** Laravel + MySQL (for fast delivery) or WordPress + WooCommerce.

---
*Note: Based on the existing `db.sql` and `fronted guide`, this project is being implemented as a custom Vanilla PHP platform.*
