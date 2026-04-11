# GEMINI.md - Easy Shopping A.R.S (ARS-e-commerce)

## Project Overview
**Easy Shopping A.R.S** is a single-vendor e-commerce platform designed for a single seller (admin) to manage products and multiple customers to place orders. The project is built using **Vanilla PHP** and **MySQL/MariaDB** with a focus on modern UI/UX using Vanilla CSS and JavaScript.

- **Primary Goal:** Professional product showcase, online ordering, and digital payment management.
- **Key Modules:** Authentication, Product Management, Cart & Checkout, Order Management, Payment (eSewa QR, Bank QR, COD), and Wishlist.
- **Technology Stack:**
    - **Backend:** PHP (Procedural with PDO)
    - **Database:** MySQL / MariaDB (`ars_ecommerce`)
    - **Frontend:** HTML5, CSS3 (Vanilla), JavaScript (Vanilla)
    - **Security:** Session-based auth, role-based access control, PDO prepared statements.

## Project Structure
- `admin/`: Admin panel logic and views (e.g., `dashboard.php`).
- `includes/`: Core configuration and global helper functions.
    - `db.php`: Database connection (PDO) and session initialization.
    - `functions.php`: Global utility functions (`is_admin()`, `format_price()`, etc.).
- `public/`: Assets (CSS, JS, Images).
- `uploads/`: User-uploaded content (product images, payment proofs).
- `fronted guide/`: Comprehensive UI/UX documentation, design systems, and code snippets.
- `db.sql`: Complete database schema and initial data.

## Getting Started

### Prerequisites
- PHP 7.4+ or 8.x
- MySQL 5.7+ or MariaDB 10.x
- A local server environment like XAMPP or Apache.

### Setup Instructions
1.  **Database Setup:**
    - Create a database named `ars_ecommerce`.
    - Import the `db.sql` file.
    - Default admin login:
        - **Mobile:** `9820210361`
        - **Password:** `12345678` (Hash: `$2y$12$h8F1x.7kwgQhM4OGkRcUF.wjKSPoSY1ZV/xy6aTSeuZ9TNBQKDajq`)
2.  **Configuration:**
    - Edit `includes/db.php` to update your database credentials (`$host`, `$db`, `$user`, `$pass`).
3.  **Run:**
    - Serve the project root using Apache.
    - Access the admin panel at `/admin/dashboard.php`.

## Development Conventions

### 1. Database Interactions
- **Always use PDO:** Never use `mysql_*` or `mysqli_*`.
- Use the global `$pdo` object provided by `includes/db.php`.
- **Prepared Statements:** Use prepared statements for all user-provided data to prevent SQL injection.
  ```php
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->execute([$product_id]);
  ```

### 2. Authentication & Security
- **Admin Protection:** Use `protect_admin_page()` at the top of every admin file.
- **Output Sanitization:** Use the `h()` helper function (shorthand for `htmlspecialchars`) for all user-generated content displayed in HTML.
  ```php
  echo h($product['name']);
  ```
- **Role Check:** Use `is_admin()` to check if the current session belongs to an administrator.

### 3. UI/UX Standards
- **Design System:** Follow the specifications in `fronted guide/COMPONENT_SPECIFICATIONS.md`.
- **Color Palette:** Use CSS variables defined in the admin UI prompt for consistency (Primary Blue: `#2563EB`, Success Green: `#10B981`).
- **Responsive Design:** Ensure all pages are responsive (Mobile, Tablet, Desktop) following the breakpoints in the `fronted guide`.
- **Interactivity:** Use Vanilla JavaScript for micro-interactions, modals, and AJAX updates as outlined in `JAVASCRIPT_IMPLEMENTATION_GUIDE.md`.

### 4. Code Style
- Use clean, commented procedural PHP.
- Keep logic and presentation separate where possible by including header/footer components.
- Standardized product image ratio: **1:1 square ratio** (e.g., 1000x1000px).

## Key Commands / TODOs
- [ ] Implement `admin/products.php` (Manage Inventory).
- [ ] Implement `admin/orders.php` (Manage Customer Orders).
- [ ] Implement Frontend Shop pages (Home, Category, Product Detail).
- [ ] Implement Checkout and Payment Proof upload logic.
