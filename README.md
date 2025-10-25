# 📚 Bookstore API (Laravel)

A simple Laravel-based API project for managing **Books**, **Authors**, and **Ratings**. This project allows users to explore books, view top authors, and give ratings — complete with an auto-guest registration system.

---

## 🚀 Features

✅ CRUD API for **Books** with filtering, searching & sorting  
✅ List & ranking system for **Authors**  
✅ Book **Rating System** with validation rules  
✅ Auto guest registration using **LocalStorage**  
✅ JSON-based API responses  
✅ Built with **Laravel 12**

---

## 🛠️ Requirements

Before running this project, make sure you have these installed:

-   **PHP** >= 8.2
-   **Composer**
-   **MySQL**
-   **Node.js** & **NPM**
-   **Git**

---

## ⚙️ Installation Guide

# 1. Clone repo

git clone https://github.com/tyayaaa/bookstore.git
cd bookstore

# 2. Install dependencies

composer install

# 3. Copy environment

cp .env.example .env

# 4. Update .env file (edit manually)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookstore_db
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migration & seeder

php artisan migrate --seed

# 6. Start server

php artisan serve

Then open your browser:
http://127.0.0.1:8000

---

## API documentation

Run : php artisan l5-swagger:generate

Then open the documentation at : http://127.0.0.1:8000/api/documentation
