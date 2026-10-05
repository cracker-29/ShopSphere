# ShopSphere 🛍️

ShopSphere is an e-commerce web application built for an M.Sc. project.

## Technology Stack

- **Front End:** Angular
- **Back End:** PHP
- **Database:** MySQL
- **Additional Tool:** XAMPP

## Main Features

- Customer registration and login
- Product browsing and categories
- Product search
- Shopping cart
- Admin login
- Admin product management
- Product stock management
- Responsive customer storefront
- Responsive admin dashboard

## Project Structure

```text
ShopSphere/
├── frontend/      # Angular application
├── backend/       # PHP API for XAMPP
├── database/      # MySQL database script
├── .gitignore
└── README.md
```

## Requirements

- Node.js and npm
- Angular CLI / Angular project dependencies
- XAMPP
- Apache
- MySQL
- A modern web browser

## 1. Frontend Setup

Open a terminal in the `frontend` folder:

```bash
npm install
npm start
```

Then open:

```text
http://localhost:4200/
```

Admin page:

```text
http://localhost:4200/admin
```

## 2. Backend Setup with XAMPP

Copy the `backend/shopsphere-api` folder into:

```text
C:\xampp\htdocs\shopsphere-api
```

Start **Apache** and **MySQL** from XAMPP Control Panel.

The Angular application expects the PHP API at:

```text
http://localhost/shopsphere-api/api/
```

## 3. Database Setup

1. Open phpMyAdmin.
2. Create a database named `shopsphere_db`.
3. Import:

```text
database/shopsphere_db.sql
```

The project is configured for the default XAMPP MySQL user:

```text
Username: root
Password: (blank)
```

If your MySQL credentials are different, update the PHP API configuration files before running the application.

## 4. Admin Account

The database script creates a local development admin account.

```text
Email: admin@shopsphere.com
Password: Admin@12345
```

**Security note:** This credential is for local academic/demo use. Change the password before deploying publicly or to production. Do not commit real production passwords or API secrets to GitHub.

## 5. Important Notes

This repository is configured for local development with XAMPP. The API URLs currently point to `localhost`, so the PHP backend must be running locally for authentication, cart, and admin product features to work.

## Academic Project

**Project Name:** ShopSphere  
**Project Type:** E-Commerce Web Application  
**Front End:** Angular  
**Back End:** PHP  
**Database:** MySQL  
**Additional Tool:** XAMPP
