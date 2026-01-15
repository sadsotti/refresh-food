# 🍎 ReFresh Food
A complete, secure, and scalable **RESTful backend** built with **PHP** and **MySQL**.
Developed as the main project for the ReFresh Food startup, focused on food recovery and environmental impact tracking.

This API manages **products**, **sales orders**, and calculates the **CO₂ saved** through food recovery interactions.

---

## 🔗 Try It Locally!

Follow the instructions below to set up and run the API on your machine.

---

## 🎯 Project Goal

The objective of this project was to build a fully functional **RESTful JSON API**, capable of:

- Managing a catalog of recovered food products.
- Creating and organizing sales orders with multiple products.
- Calculating the total **environmental impact** (CO₂ saved) dynamically.
- Providing analytical endpoints with filters for **date range**, **country**, and **specific product**.
- Ensuring **database security** through PDO prepared statements.
- Implementing clean **REST endpoints** using URL rewriting via the `.htaccess` file.

---

## ✨ Implemented Features

### 📦 Products
- Create, update, and delete product types.
- Fields: **name**, **co2_saved** (kg saved per unit).

### 🛒 Orders
- Create, update, and delete sales orders.
- Automated management of order details (linking products to orders).
- Fields: **order_date**, **destination_country**, **product_list**.

### 📊 Analytics
- `/api/stats` -> Real-time calculation of total CO₂ saved.
- Filter by **date range** (`start_date` and `end_date`).
- Filter by **destination country**.
- Filter by **specific product ID**.

---

## 🔒 Security & Routing

- **All MySQL queries** use **PDO prepared statements** to prevent SQL Injection.
- **Sanitized and validated** input parameters for all endpoints.
- **Clean URLs**: Use of `.htaccess` to map requests (e.g., `/api/products` instead of `/api/products.php`).
- **Folder Protection**: Access to sensitive directories (`config/`, `sql/`) is restricted via server rules.

---

## 🛠️ Technologies Used

- **PHP 8.x**
- **MySQL / MariaDB**
- **Apache** (with `mod_rewrite` enabled)
- **PDO** (PHP Data Objects)
- **JSON** for data exchange
- **cURL** for endpoint testing

---

## 🚀 How to Run the Project Locally

### 1. Clone the repository

```bash
git clone https://github.com/sadsotti/refresh-food.git
```

### 2. Enter the project directory

Move the folder to your local server path (e.g., `/srv/http/` or `htdocs`).

### 3. Create the database

Make sure MySQL is running, then import the migration file:

```sql
CREATE DATABASE refresh_food;
USE refresh_food;
SOURCE migrations.sql;
```
### 4. Configure Database connection
Update your credentials in `config/db.php`:

```php
<?php
$host = 'localhost';
$db   = 'refresh_food';
$user = 'root';
$pass = 'your_password';
```

### 5. Enable URL Rewriting

Ensure Apache has `AllowOverride All` set for the project directory to enable the rules defined in the `.htaccess` file.

## 📂 Project Structure

```text
refresh-food/
├── api/
│   ├── products.php      # CRUD for products
│   ├── orders.php        # CRUD for orders
│   └── stats.php         # Analytics & Dashboard logic
├── config/
│   └── db.php            # PDO Database connection
├── .htaccess             # URL Rewriting & security rules
├── migrations.sql        # Database schema & initial data
└── README.md             # Documentation
```

## 🗄️ Database Schema

The project uses a relational structure to ensure data consistency:
- **`products`**: Contains product definitions and their CO₂ impact per unit.
- **`orders`**: Stores order headers (date, destination).
- **`order_items`**: Junction table linking orders to products with specific quantities.

## 📤 API Responses

All endpoints return a JSON object.

### 🟢 Success Codes
The API strictly adheres to RESTful standards by returning the appropriate HTTP status codes for every successful operation:

* **`200 OK`**: Returned for successful `GET` requests (e.g., fetching Analytics) and `PUT` updates where the resource state is returned.
* **`201 Created`**: Returned after a successful `POST` request (New Product or Order creation) to confirm the resource was generated.
* **`204 No Content`**: Returned after a successful `DELETE` request, indicating the action was completed but no response body is required.

**Standard Success Body:**

```json
{
  "status": "success",
  "data": { ... }
}
```

### 🔴 Error Codes
When a request cannot be fulfilled, the API returns the following HTTP status codes to provide clear feedback to the client:

* **`400 Bad Request`**: Returned when required parameters are missing or validation fails (e.g., providing a negative CO₂ value or an invalid date format).
* **`404 Not Found`**: Returned when the requested Resource ID (Product or Order) does not exist in the database.
* **`405 Method Not Allowed`**: Returned when a request is made using an unsupported HTTP method for a specific endpoint (e.g., trying to `DELETE` the stats endpoint).
* **`500 Internal Server Error`**: Returned in case of server-side issues, such as database connection failures or query execution errors.

**Standard Error Body:**

```json
{
  "status": "error",
  "message": "Detailed description of what went wrong"
}
```

## 🔌 Available Endpoints

### 📦 Products

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| **POST** | `/api/products` | Create a new product |
| **PUT** | `/api/products?id=` | Update an existing product |
| **DELETE** | `/api/products?id=` | Delete a product |

### 🛒 Orders

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| **POST** | `/api/orders` | Create a new sales order |
| **PUT** | `/api/orders?id=` | Update an existing order |
| **DELETE** | `/api/orders?id=` | Delete an order |

#### 📝 Example Order JSON Body

```json
{
  "order_date": "2026-01-15",
  "destination_country": "Italy",
  "products": [
    { "id": 1, "quantity": 10 },
    { "id": 5, "quantity": 2 }
  ]
}
```

### 📊 Stats & Analytics

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| **GET** | `/api/stats?start_date=&end_date=&country=&product_id=` | Get dynamic CO₂ Analytics with filters |

#### 🔍 Query Parameters details:

- `start_date` / `end_date`: Format `YYYY-MM-DD`.
- `country`: Full country name (e.g., "Italy").
- `product_id`: Integer ID from the products table.

### 🧮 Calculation Logic

The total CO₂ saved is calculated dynamically by joining the orders and products tables using the following formula:

$$Total\ CO_2 = \sum_{i=1}^{n} (Quantity_i \times CO_{2\_saved\_per\_unit,i})$$

## 🧪 Testing Examples

**Get Dashboard Stats (Filtered):**

```bash
curl "http://localhost/refresh_food/api/stats?start_date=2026-01-10&end_date=2026-01-20&country=Italy"
```

**Create a New Product:**

```bash
curl -X POST http://localhost/refresh_food/api/products \
-H "Content-Type: application/json" \
-d '{"name": "Ugly but Good Apple", "co2_saved": 0.5}'
```

## 🔗 Useful Links

- https://www.start2impact.it/
- https://linkedin.com/in/lorenzo-sottile

---
