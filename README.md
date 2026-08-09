# Cashier and Inventory Management System

A web-based cashier and inventory management system designed to help manage customers, products, stock, sales transactions, transaction history, and printable receipts.

## 📌 Topic

**Web-Based Cashier and Inventory Management System**

## 🛠️ Technologies

This project is built using:

- **PHP** — Backend programming language
- **MySQL / MariaDB** — Database management
- **HTML5** — Website structure
- **CSS3** — Website styling
- **Bootstrap 5** — Responsive user interface
- **JavaScript** — Client-side functionality
- **XAMPP** — Local development environment

## ✨ Features

### 🔐 Admin Login
- Admin authentication
- Session-based login system
- Secure password verification

### 👥 Customer Management
- Add customer data
- Edit customer data
- Delete customer data
- View customer information

### 📦 Product & Stock Management
- Add products
- Edit product information
- Delete products
- Manage product prices
- Manage product stock
- Display low-stock products

### 🛒 Sales Transactions
- Select customers
- Select products
- Enter purchase quantity
- Automatically calculate subtotal
- Automatically calculate total price
- Automatically update product stock

### 📋 Transaction History
- View completed transactions
- Search transactions
- View customer and product details
- Delete transactions
- Automatically restore stock when a transaction is deleted

### 🧾 Printable Receipt
- Display transaction details
- Display customer information
- Display purchased products
- Display quantity and price
- Display total payment
- Print transaction receipt

### 📊 Dashboard
The dashboard provides an overview of:

- Total customers
- Total products
- Low-stock products
- Total transactions
- Total sales
- Recent transactions

## 🗄️ Database

The system uses a MySQL/MariaDB database named:

```text
kasir_db
````

Main tables:

```text
admin
pelanggan
barang
penjualan
```

### Database Relationships

```text
pelanggan
    │
    │
    └──── penjualan ──── barang
```

The `penjualan` table connects customers and products through:

* `id_pelanggan`
* `id_barang`

## 📁 Project Structure

```text
Cashier-and-Inventory-System/
│
├── index.php
├── login.php
├── logout.php
├── database.php
│
├── navbar.php
│
├── pelanggan.php
├── stok.php
├── transaksi.php
├── histori.php
├── cetak.php
│
├── database/
│   └── kasir_db.sql
│
├── bootstrap-5.3.8-dist/
│
└── README.md
```

> The exact file structure may vary depending on the project version.

## 🚀 Installation

### 1. Install XAMPP

Download and install XAMPP with:

* Apache
* MySQL

### 2. Clone or Copy the Project

Place the project inside the XAMPP `htdocs` directory:

```text
C:\xampp\htdocs\Cashier-and-Inventory-System
```

### 3. Start XAMPP

Run:

```text
Apache
MySQL
```

from the XAMPP Control Panel.

### 4. Create the Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create or import the database:

```text
kasir_db
```

Then import the provided SQL file.

### 5. Configure Database Connection

Make sure `database.php` contains the correct database configuration:

```php
<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "kasir_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
```

### 6. Open the Application

Open your browser and visit:

```text
http://localhost/Cashier-and-Inventory-System/
```

## 🔑 Default Admin Account

```text
Username: admin
Password: admin123
```

> For security purposes, change the default password before using the system in a production environment.

## 🔄 System Workflow

```text
Admin Login
     │
     ▼
Dashboard
     │
     ├── Customer Management
     │
     ├── Product & Stock Management
     │
     ├── New Transaction
     │        │
     │        ▼
     │    Stock Updated
     │
     ├── Transaction History
     │
     └── Print Receipt
```

## 📱 Responsive Design

The interface is designed to work on:

* 💻 Desktop
* 💻 Laptop
* 📱 Mobile devices
* 📱 Tablets

Bootstrap is used to provide a responsive layout across different screen sizes.

## 🎯 Project Purpose

This project was created as a web-based cashier management application to simplify basic retail operations, including customer management, inventory management, sales transactions, transaction history, and receipt printing.

## 👩‍💻 Development

**Programming Language:** PHP

**Database:** MySQL / MariaDB

**Frontend:** HTML, CSS, Bootstrap, JavaScript

**Development Environment:** XAMPP

## 📄 License

This project is intended for educational and development purposes.
