# 🛒 E-Commerce Project (PHP + MySQL)

A full-stack **E-Commerce web application** built with **PHP (OOP)** and **MySQL**, featuring user authentication, product listing, cart management (cookies + DB), checkout flow, and order management.  
This project simulates a real-world e-commerce workflow for **learning and portfolio purposes**.

---

## 🚀 Features

### 🔐 User Authentication
- Register / Login / Logout  
- Session-based authentication  

### 🛍️ Product Catalog
- Product listing by categories  
- Product images and pricing  

### 🛒 Cart System
- Guest users: Cart stored in **cookies/session**  
- Logged-in users: Cart stored in **database**  
- Increase / Decrease quantity  
- Remove product from cart  

### 💳 Checkout & Orders
- Place order with delivery details  
- Total price calculation using SQL JOIN  
- Cart cleared after successful order  

### 🍪 Cookie Consent
- Cookie accept/reject banner  
- Consent guard for handling cookies properly  

### 🧱 Clean Architecture
- Helpers & Services layer  
- Partial views for reusable components  
- Config-based project structure  

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP (OOP)  
- **Database:** MySQL / MariaDB  
- **Server:** XAMPP / Apache  
- **Version Control:** Git + GitHub  

---

## 📂 Project Structure (Simplified)

```text
TASK/
├── auth/                 # login, register, logout, session
├── config/               # DB & app config
├── helpers/              # helper utilities (storage, etc.)
├── services/             # business logic (cartService, etc.)
├── public/               # public files (cookie consent)
├── uploads/products/     # product images
├── views/
│   ├── navigation/       # navbar & layout
│   ├── products/         # product list, cart, checkout
│   └── partials/         # reusable components (cookie banner)
├── index.php             # entry point
└── README.md
```

## ⚙️ Setup & Installation (Local)

### 1️⃣ Clone the Repository
git clone https://github.com/mohit-code1977/E_Commerce_Project.git
cd E_Commerce_Project

### 2️⃣ Setup XAMPP
- Install XAMPP  
- Start Apache and MySQL  
- Place the project inside:
C:/xampp/htdocs/TASK

### 3️⃣ Create Database
Open phpMyAdmin and create a database:
CREATE DATABASE ecommerce;

Import your SQL schema (tables for users, products, cart, orders, categories, etc.).

### 4️⃣ Configure Database
Edit:
config/db.php

Example:
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ecommerce";

### 5️⃣ Run the Project
Open in browser:
http://localhost/TASK

---

## 🧪 Demo & Testing
- Create a new user via the registration page  
- Insert dummy products in DB for testing  
- Test cart flow as guest and logged-in user  

---

## 🧠 Important Implementation Notes

### Cart Storage
- Guest → Cookies / Session  
- Logged-in user → Database  

### Total Price Calculation
SELECT SUM(c.qty * p.price)
FROM cart c
JOIN products p ON c.product_id = p.id
WHERE c.user_id = ?;

### Post-Order Behavior
- Cart is cleared from DB  
- User is redirected to dashboard  

