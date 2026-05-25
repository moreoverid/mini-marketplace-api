# Mini Marketplace API

Mini Marketplace API is a portfolio project built with **Laravel 12**, **PHP 8+**, **PostgreSQL**, **Redis**, **RabbitMQ**, **Docker** and **Quasar 2**.

The project demonstrates a practical full-stack marketplace flow with:

- Domain-Driven Design
- simplified CQRS
- Test-Driven Development
- SOLID-oriented responsibility separation
- Laravel events, listeners and queued jobs
- PostgreSQL persistence
- Redis queue worker
- RabbitMQ integration events
- Vue 3 / Quasar 2 frontend
- Docker-based local development

---

## Project Goal

The goal of this project is not to build a large marketplace, but to show a clean and testable backend architecture on a small realistic domain.

Current business flow:

```text
Create product
↓
Create order from product
↓
Pay order
↓
Record OrderPaid domain event
↓
Dispatch listener
↓
Run queued jobs
↓
Write payment audit log
↓
Publish order.paid integration event to RabbitMQ
```

The same flow is available through the Quasar UI.

---

## Tech Stack

### Backend

- PHP 8+
- Laravel 12
- PostgreSQL
- Redis
- RabbitMQ
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
- RabbitMQ Management UI

---

## Main Features

### Products

- Create products
- List products with pagination
- Search products by name
- Store price as integer amount instead of float
- Product domain model with value objects

### Orders

- Create orders from existing products
- Store order items with fixed unit price
- List orders with pagination
- Filter orders by status
- Pay pending orders
- Prevent paying an order twice
- Dispatch `OrderPaid` domain event
- Handle payment side effects through queued jobs
- Store payment audit log idempotently
- Publish `order.paid` integration event to RabbitMQ

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

The project uses a modular monolith structure grouped by business context.

```text
app/
├── Modules/
│   ├── Catalog/
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Http/
│   ├── Ordering/
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Http/
│   └── Shared/
│       ├── Domain/
│       ├── Application/
│       └── Infrastructure/
├── Http/
│   └── Controllers/
├── Models/
└── Providers/
```

The main idea is to keep classes that change for the same business reason close to each other.

For example, Catalog-related domain objects, use cases, HTTP controllers and persistence code are grouped under the `Catalog` module.

---

## Module Structure

Each business module follows the same high-level structure:

```text
Module
├── Domain
├── Application
├── Infrastructure
└── Http
```

### Domain

Contains business objects and rules.

Examples:

```text
Catalog/Domain
├── Entities/Product.php
├── ValueObjects/Money.php
├── ValueObjects/ProductId.php
└── Repositories/ProductRepository.php
```

```text
Ordering/Domain
├── Entities/Order.php
├── Entities/OrderItem.php
├── ValueObjects/OrderId.php
├── ValueObjects/OrderStatus.php
├── Events/OrderPaid.php
└── Repositories/OrderRepository.php
```

The domain layer does not depend on Laravel controllers, requests, resources or Eloquent models.

---

### Application

Contains use cases, commands, handlers, queries, read models and application-level ports.

Examples:

```text
Catalog/Application
├── Commands/CreateProductCommand.php
├── Handlers/CreateProductHandler.php
├── Queries/ListProductsQuery.php
├── ReadModels/ProductListItem.php
└── ReadRepositories/ProductReadRepository.php
```

```text
Ordering/Application
├── Commands/CreateOrderCommand.php
├── Commands/PayOrderCommand.php
├── Handlers/CreateOrderHandler.php
├── Handlers/PayOrderHandler.php
├── Queries/ListOrdersQuery.php
└── ReadRepositories/OrderReadRepository.php
```

```text
Shared/Application
├── Eventing/DomainEventDispatcher.php
└── Messaging/IntegrationEventPublisher.php
```

Application handlers orchestrate use cases, but do not contain low-level infrastructure logic.

---

### Infrastructure

Contains technical implementations.

Examples:

```text
Catalog/Infrastructure
└── Persistence/Eloquent
    ├── Models/ProductModel.php
    └── Repositories/EloquentProductRepository.php
```

```text
Ordering/Infrastructure
├── Eventing/Listeners/DispatchOrderPaidJobs.php
├── Jobs/RecordOrderPaidAuditLogJob.php
├── Jobs/PublishOrderPaidIntegrationEventJob.php
└── Persistence/Eloquent
    ├── Models/OrderModel.php
    ├── Models/OrderItemModel.php
    ├── Models/OrderPaymentLogModel.php
    └── Repositories/EloquentOrderRepository.php
```

```text
Shared/Infrastructure
├── Eventing/LaravelDomainEventDispatcher.php
└── Messaging/RabbitMq
    ├── RabbitMqConnectionFactory.php
    └── RabbitMqIntegrationEventPublisher.php
```

Infrastructure classes know about Laravel, Eloquent, queues, RabbitMQ and other technical details.

---

### Http

Contains controllers, form requests and resources for a specific module.

Examples:

```text
Catalog/Http
├── Controllers/Api/ProductController.php
├── Requests/Api/StoreProductRequest.php
├── Requests/Api/ListProductsRequest.php
└── Resources/ProductResource.php
```

```text
Ordering/Http
├── Controllers/Api/OrderController.php
├── Requests/Api/StoreOrderRequest.php
├── Requests/Api/ListOrdersRequest.php
└── Resources/OrderResource.php
```

Controllers are intentionally thin: they receive HTTP input, call application handlers and return API resources.

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

The actual dispatching happens in the application layer:

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

The reason for this separation is simple: list/search endpoints need pagination, filters and UI-specific read models, while domain repositories should stay focused on aggregate persistence.

At the moment both command and query sides use PostgreSQL, but the read side can later be moved to Elasticsearch without changing the domain model or command side.

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
DomainEventDispatcher
↓
LaravelDomainEventDispatcher
↓
DispatchOrderPaidJobs listener
├── RecordOrderPaidAuditLogJob
│   ↓
│   order_payment_logs table
└── PublishOrderPaidIntegrationEventJob
    ↓
    RabbitMQ exchange marketplace.events
    ↓
    routing key order.paid
```

`RecordOrderPaidAuditLogJob` is idempotent and uses `updateOrCreate`, so duplicate job execution does not create duplicate audit logs.

RabbitMQ is used for publishing external integration events. Redis is still used for internal Laravel queued jobs.

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

If `composer.lock` is not yet updated after adding RabbitMQ dependency, run:

```bash
docker compose run --rm --user app app composer update php-amqplib/php-amqplib
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

RabbitMQ Management UI:

```text
http://localhost:15672
```

Default RabbitMQ credentials:

```text
marketplace / secret
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

## RabbitMQ Manual Check

Create a temporary debug queue in RabbitMQ UI:

```text
Queue: debug.order.paid
```

Bind it to:

```text
Exchange: marketplace.events
Routing key: order.paid
```

Then pay an order from the UI or API. The `order.paid` message should appear in the debug queue.

---

## Running Tests

Run all tests:

```bash
docker compose exec app php artisan test
```

Run a specific test:

```bash
docker compose exec app php artisan test --filter=OrderControllerTest
```

Current test coverage includes:

- domain model tests;
- application handler tests;
- infrastructure repository tests;
- HTTP feature tests;
- event listener tests;
- queued job tests;
- RabbitMQ integration event publishing job test.

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

### Modules are grouped by business context

The code is grouped by modules such as `Catalog`, `Ordering` and `Shared`.

This makes related domain, application, infrastructure and HTTP code easier to find and change together.

---

### Domain models are separated from Eloquent models

For example:

```text
Catalog Domain Product
Catalog Infrastructure ProductModel
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

### Redis and RabbitMQ have different roles

Redis is used for internal Laravel queued jobs.

RabbitMQ is used for publishing external integration events, for example `order.paid`.

---

### Jobs are idempotent where needed

The `RecordOrderPaidAuditLogJob` uses `updateOrCreate`, making it safe to retry.

This is important because queued jobs may be executed more than once.

---

## Roadmap

Possible next improvements:

- add Elasticsearch for product search;
- implement outbox pattern for reliable RabbitMQ publishing;
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
Redis queued jobs
↓
RabbitMQ integration events
```

This repository is intended as a portfolio project for demonstrating practical backend architecture with Laravel, DDD, CQRS, TDD, RabbitMQ and modular design.
