# University Student Marketplace (UniMarket)

## Database Design (ERD)

This document describes the database design for the University Student Marketplace project.

Week: 5

Development Package: DP06

Owner: MD. Tawhidul Islam

## Entities

The database will contain the following entities:

1. Users
2. Categories
3. Products
4. Transactions

---

## Entity: Users

| Column | Data Type | Description |
|---------|-----------|-------------|
| user_id | INT | Primary Key |
| full_name | VARCHAR(100) | Student's full name |
| email | VARCHAR(100) | Unique email address |
| student_id | VARCHAR(20) | Unique student ID |
| department | VARCHAR(100) | Student's department |
| password_hash | VARCHAR(255) | Hashed password |
| role | ENUM('student','admin') | User role |
| created_at | TIMESTAMP | Account creation date |

---

## Entity: Categories

| Column | Data Type | Description |
|---------|-----------|-------------|
| category_id | INT | Primary Key |
| category_name | VARCHAR(50) | Unique category name |
| description | TEXT | Category description |


---

## Entity: Products

| Column | Data Type | Description |
|---------|-----------|-------------|
| product_id | INT | Primary Key |
| seller_id | INT | Foreign Key → Users.user_id |
| category_id | INT | Foreign Key → Categories.category_id |
| title | VARCHAR(150) | Product title |
| description | TEXT | Product description |
| price | DECIMAL(10,2) | Selling price |
| product_condition | ENUM('New','Like New','Good','Fair') | Product condition |
| status | ENUM('Available','Reserved','Sold') | Product status |
| created_at | TIMESTAMP | Date listed |


---

## Entity: Transactions

| Column | Data Type | Description |
|---------|-----------|-------------|
| transaction_id | INT | Primary Key |
| product_id | INT | Foreign Key → Products.product_id |
| buyer_id | INT | Foreign Key → Users.user_id |
| seller_id | INT | Foreign Key → Users.user_id |
| amount | DECIMAL(10,2) | Transaction amount |
| status | ENUM('Pending','Completed','Cancelled') | Transaction status |
| transaction_date | TIMESTAMP | Date of transaction |

---

# Relationships

## Users → Products

One user can create many products.

Relationship:

Users (1) → Products (Many)

Foreign Key:

Products.seller_id → Users.user_id

---

## Categories → Products

One category can contain many products.

Relationship:

Categories (1) → Products (Many)

Foreign Key:

Products.category_id → Categories.category_id

---

## Products → Transactions

One product can have one completed transaction.

Relationship:

Products (1) → Transactions (1)

Foreign Key:

Transactions.product_id → Products.product_id

---

## Users → Transactions

One user can buy many products.

One user can sell many products.

Foreign Keys:

Transactions.buyer_id → Users.user_id

Transactions.seller_id → Users.user_id

---

# Normalization

## First Normal Form (1NF)

- Every column contains a single value.
- There are no repeating groups.

Status: ✅ Satisfied

## Second Normal Form (2NF)

- Every non-key attribute depends on the primary key.
- Each table stores information about only one entity.

Status: ✅ Satisfied

## Third Normal Form (3NF)

- No unnecessary duplicate data.
- Categories are stored only in the Categories table.
- User information is stored only in the Users table.
- Product information is stored only in the Products table.

Status: ✅ Satisfied

---

# Design Notes

- All primary keys will use AUTO_INCREMENT.
- Passwords will never be stored as plain text. PHP's `password_hash()` and `password_verify()` will be used.
- Foreign key constraints will enforce referential integrity.
- The database will be implemented in MySQL using the InnoDB storage engine.
- All timestamps will default to the current date and time where appropriate.