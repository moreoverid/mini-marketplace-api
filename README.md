# Mini Marketplace API

Mini Marketplace is a portfolio project built with **Laravel 12**, **PHP 8+**, **PostgreSQL**, **Redis**, **Docker** and **Quasar 2**.

The main goal of this project is to demonstrate practical usage of:

- Domain-Driven Design
- Test-Driven Development
- CQRS
- SOLID principles
- Laravel queues, events, listeners and jobs
- PostgreSQL persistence
- Vue 3 / Quasar frontend integration
- Docker-based local development

The project implements a small marketplace flow:

```text
Create product
↓
Create order from product
↓
Pay order
↓
Dispatch domain event
↓
Handle event through listener
↓
Push queued job
↓
Write payment audit log
```

---

## Tech Stack

### Backend

- PHP 8+
- Laravel 12
- PostgreSQL
- Redis
- PHPUnit
- Laravel Queues
- Laravel Events / Listeners / Jobs

### Frontend

- Vue 3
- Quasar 2
- Vue Router
- Axios
- Vite

### Infrastructure

- Docker
- Docker Compose
- Nginx
- PHP-FPM
- Mailpit

---

## Main Features

### Products

- Create product
- List products with pagination
- Search products by name
- Store price as integer amount instead of float
- Product domain model with value objects

### Orders

- Create order from existing products
- Store order items with fixed unit price
- List orders with pagination
- Filter orders by status
- Pay pending order
- Prevent paying an order twice
- Dispatch `OrderPaid` domain event
- Handle payment side effects through queued job

### Frontend

- Products page
- Orders page
- Create product dialog
- Create order dialog
- Pay order action
- Status badges
- Pagination and filtering

---

## Architecture Overview

The project uses a layered architecture inspired by DDD:

```text
Http
↓
Application
↓
Domain
↓
Infrastructure
```

### Domain Layer

Contains business entities, value objects, domain events and repository contracts.

```text
app/Domain
├── Catalog
│   ├── Entities
│   ├── ValueObjects
│   └── Repositories
├── Ordering
│   ├── Entities
│   ├── ValueObjects
│   ├── Events
│   └── Repositories
└── Shared
    └── Events
```

Examples:

- `Product`
- `Money`
- `ProductId`
- `Order`
- `OrderItem`
- `OrderStatus`
- `OrderPaid`

The domain layer does not depend on Eloquent models, controllers or HTTP requests.

---

### Application Layer

Contains use cases, commands, handlers, queries and read models.

```text
app/Application
├── Catalog
│   ├── Commands
│   ├── Handlers
│   ├── Queries
│   ├── ReadModels
│   └── ReadRepositories
├── Ordering
│   ├── Commands
│   ├── Handlers
│   ├── Queries
│   ├── ReadModels
│   └── ReadRepositories
└── Shared
    └── Eventing
```

Examples:

- `CreateProductHandler`
- `ListProductsHandler`
- `CreateOrderHandler`
- `PayOrderHandler`
- `ListOrdersHandler`

Application handlers orchestrate use cases but do not contain low-level infrastructure logic.

---

### Infrastructure Layer

Contains technical implementations:

```text
app/Infrastructure
├── Eventing
│   └── Listeners
└── Persistence
    └── Eloquent
        ├── Models
        └── Repositories
```

Examples:

- `EloquentProductRepository`
- `EloquentProductReadRepository`
- `EloquentOrderRepository`
- `EloquentOrderReadRepository`
- `LaravelDomainEventDispatcher`
- `DispatchOrderPaidJobs`

---

### HTTP Layer

Contains Laravel controllers, form requests and API resources.

```text
app/Http
├── Controllers
├── Requests
└── Resources
```

Controllers are intentionally thin.  
They receive HTTP input, pass data to application handlers and return API resources.

---

## DDD Notes

The project separates business logic from framework-specific code.

For example, order payment is handled inside the domain model:

```php
$order->pay();
```

The `Order` aggregate protects its own invariants:

- only pending orders can be paid;
- paid orders cannot be paid again;
- empty orders cannot be paid;
- order items cannot be changed after payment.

The domain event is recorded inside the aggregate:

```php
$this->recordThat(new OrderPaid($this->id));
```

But the actual dispatching happens in the application layer:

```php
$this->events->dispatch(...$order->releaseEvents());
```

This keeps the domain model independent from Laravel's event dispatcher.

---

## CQRS Notes

The project uses a simplified CQRS approach.

### Command Side

Commands change application state:

```text
CreateProductCommand
CreateOrderCommand
PayOrderCommand
```

Handlers use domain models and domain repositories:

```text
CreateProductHandler
CreateOrderHandler
PayOrderHandler
```

### Query Side

Queries read data using separate read repositories and read models:

```text
ListProductsQuery
ListOrdersQuery
ProductReadRepository
OrderReadRepository
ProductListItem
OrderListItem
```

At the moment both command and query sides use PostgreSQL, but the read side can later be moved to Elasticsearch without changing the domain model.

---

## Queue / Event Flow

When an order is paid:

```text
PATCH /api/orders/{id}/pay
↓
PayOrderHandler
↓
Order::pay()
↓
OrderPaid domain event
↓
LaravelDomainEventDispatcher
↓
DispatchOrderPaidJobs listener
↓
RecordOrderPaidAuditLogJob
↓
order_payment_logs table
```

The job is idempotent and uses `updateOrCreate`, so duplicate job execution does not create duplicate audit logs.

---

## API Endpoints

### Products

```http
GET /api/products
POST /api/products
GET /api/products/{id}
```

Example product payload:

```json
{
  "name": "iPhone 15",
  "price_amount": 99900,
  "currency": "USD",
  "stock": 10
}
```

---

### Orders

```http
GET /api/orders
POST /api/orders
GET /api/orders/{id}
PATCH /api/orders/{id}/pay
```

Example order payload:

```json
{
  "items": [
    {
      "product_id": "PRODUCT_UUID",
      "quantity": 2
    }
  ]
}
```

---

## Local Development

### 1. Clone repository

```bash
git clone git@github.com:YOUR_USERNAME/mini-marketplace-api.git
cd mini-marketplace-api
```

### 2. Copy environment file

```bash
cp .env.example .env
```

### 3. Build Docker containers

```bash
docker compose build
```

### 4. Install backend dependencies

```bash
docker compose run --rm --user app app composer install
```

### 5. Install frontend dependencies

```bash
docker compose run --rm --user app app npm install
```

### 6. Generate application key

```bash
docker compose run --rm --user app app php artisan key:generate
```

### 7. Start containers

```bash
docker compose up -d
```

### 8. Run migrations

```bash
docker compose exec app php artisan migrate
```

### 9. Start Vite dev server

```bash
docker compose exec app npm run dev -- --host 0.0.0.0
```

Application:

```text
http://localhost:8080
```

Mailpit:

```text
http://localhost:8025
```

---

## Queue Worker

Start queue worker:

```bash
docker compose --profile workers up -d
```

The worker listens to:

```text
orders,default
```

To watch logs:

```bash
docker compose logs -f worker
```

---

## Running Tests

Run all tests:

```bash
docker compose exec app php artisan test
```

Run specific test:

```bash
docker compose exec app php artisan test --filter=OrderControllerTest
```

Current test coverage includes:

- domain model tests;
- application handler tests;
- infrastructure repository tests;
- HTTP feature tests;
- queued job tests;
- event listener tests.

---

## Frontend Build

Development:

```bash
docker compose exec app npm run dev -- --host 0.0.0.0
```

Production build:

```bash
docker compose exec app npm run build
```

---

## Important Design Decisions

### Domain models are separated from Eloquent models

For example:

```text
Domain Product
Infrastructure ProductModel
```

The domain model contains business rules.  
The Eloquent model only represents database persistence.

---

### Money is stored as integer

The project stores money as integer amount:

```text
99900
```

instead of float:

```text
999.00
```

This avoids floating-point precision issues.

---

### Read side is separated from write side

Product and order lists use read repositories and read models instead of domain repositories.

This avoids mixing aggregate persistence with table/list-specific queries.

---

### Domain events are dispatched outside the aggregate

The aggregate records events, but does not dispatch them directly.

This keeps the domain layer independent from Laravel.

---

### Jobs are idempotent

The `RecordOrderPaidAuditLogJob` uses `updateOrCreate`, making it safe to retry.

This is important because queued jobs may be executed more than once.

---

## Roadmap

Possible next improvements:

- add Elasticsearch for product search;
- add Kafka or Redpanda for publishing integration events;
- implement outbox pattern;
- add order cancellation flow;
- add stock reservation/decrease logic;
- add authentication;
- add Laravel Pint / PHPStan;
- add GitHub Actions CI;
- add screenshots to README.

---

## Status

The project currently demonstrates a working vertical slice:

```text
Quasar UI
↓
Laravel API
↓
Application command/query handlers
↓
Domain models and value objects
↓
Eloquent repositories
↓
PostgreSQL
↓
Domain events
↓
Queued jobs
```

This repository is intended as a portfolio project for demonstrating practical backend architecture with Laravel, DDD, CQRS and TDD.
