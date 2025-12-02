# 🚀 High-Concurrency Stock Reservation System  
A Laravel 12 system implementing **atomic stock holds**, **race-condition safety**, **Redis locks**, **cache-aside**, and **queue-based hold expiry**.

---

## 🏷️ Badges

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![MySQL](https://img.shields.io/badge/MySQL-InnoDB-orange)
![Redis](https://img.shields.io/badge/Redis-Cache-purple)
![Queue](https://img.shields.io/badge/Queue-Worker-green)
![License](https://img.shields.io/badge/License-MIT-lightgrey)

---

# 🧰 Tech Stack

- **Laravel 12**
- **MySQL (InnoDB)**
- **Redis Cache**
- **Redis Locks**
- **Queue Worker for hold expiry**

---

# ⚙️ Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed   # seeds products


🔧 Installing Redis
Windows
wsl --install


Linux
sudo apt update
sudo apt install redis-server -y


Start Redis
sudo service redis-server start

Test Redis
redis-cli ping   # PONG

▶️ Start Services
Run API
php artisan serve


Run Queue Worker
php artisan queue:work


🧩 System Invariants
Stock quantity updates occur in:

1 - Creating a hold

2 - Releasing expired hold

3 - Cancelling a failed payment

# Race-condition safety implemented using:

1 - Redis Caching Locks + MySQL Pessimistic Lock

2 - DB Transactions

#Hold lifetime

1 - Fixed (2 minutes)

#Read performance

1 - Cache-Aside pattern with Redis

2 - Cache invalidated on writes

####################################################################################################
📡 API Endpoints
Method	Endpoint	Description
GET	/api/products/{id}	Fetch product and stock
POST	/api/holds/{product_id}/{quantity}	Create a temporary stock reservation
POST	/api/orders/{hold_id}/{product_id}	Create order based on active hold
POST	/api/payments/webhook	Payment success/failure callback
Example Responses
GET /api/products/{id}
{
  "id": 1,
  "price": 200,
  "stock": 4
}

POST /api/holds/{product_id}/{quantity}
{
  "message": "Holded Successfully",
  "success": true,
  "hold": {
    "id": 2,
    "qty": 2,
    "expires_at": "2025-01-01T12:00:00Z"
  }
}

POST /api/orders/{hold_id}/{product_id}
{
  "message": "order created successfully",
  "success": true,
  "order": {
    "status": "pending_payment",
    "hold_id": 13,
    "updated_at": "2025-12-02T14:47:04.000000Z",
    "created_at": "2025-12-02T14:47:04.000000Z",
    "id": 19
  }
}

POST /api/payments/webhook
{
  "message": "ok"
}