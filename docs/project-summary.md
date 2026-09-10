---
title: Shop API — Project Summary
date: September 10, 2026
version: 1.0
audience: Engineering Team, Architects, Stakeholders
---

# Shop API — Project Summary

## Executive Summary

**Shop** is a modern e-commerce REST API built with Laravel 13 using Domain-Driven Design (DDD) principles. The system manages a complete product catalog, shopping cart, order fulfillment, and payment processing pipeline. It integrates with external services (Zarinpal payment gateway, Kavehnegar SMS provider) and leverages Elasticsearch for product search, RabbitMQ for asynchronous task processing, and Redis for caching. The API is versioned (v1, v2) and uses Laravel Sanctum for token-based authentication with fine-grained ability-based authorization.

---

## 1. Architecture Overview

### System Context

The Shop API operates within a distributed ecosystem:

- **Client Applications**: Mobile apps, web browsers, and third-party integrations consume the REST API
- **Core System**: Laravel 13 application processing requests and orchestrating business logic
- **External Services**: Payment gateways (Zarinpal), SMS providers (Kavehnegar), and real-time communication
- **Data Layer**: PostgreSQL database, Elasticsearch search engine, Redis cache
- **Async Processing**: RabbitMQ message queue for background jobs (emails, notifications)

![High-Level Architecture](diagrams/high-level-architecture.drawio.png)

**Key design decisions:**

- **Microservice-friendly**: Async job processing via RabbitMQ enables independent scaling of workers
- **Search-optimized**: Elasticsearch integration (via Laravel Scout driver) powers fast product search across large catalogs
- **Cache-first**: Redis caching reduces database load for frequently accessed data (categories, popular products)
- **Multi-version API**: v1 and v2 routes allow backward compatibility during API evolution
- **External abstractions**: Payment gateway and SMS provider contracts use dependency injection, enabling easy switching between implementations (e.g., Zarinpal → Stripe)

---

## 2. Processing Pipeline

Every request flows through a standardized pipeline:

![Processing Pipeline](diagrams/processing-pipeline.drawio.png)

### Request Flow

1. **API Request**: Client sends HTTP request to versioned endpoint (e.g., `POST /api/v1/items`)
2. **Authentication**: Sanctum middleware validates JWT/token; gates deny unauthenticated requests
3. **Request Validation**: Form Request classes (e.g., `StoreItemRequest`) validate input schema and business rules
4. **Domain Logic**: Domain Actions and Services execute business operations (create item, calculate totals, process payment)
5. **Database Operations**: Eloquent ORM persists changes to PostgreSQL; observers trigger cross-cutting concerns
6. **Domain Events**: Business events (OrderCreated, PaymentProcessed) are dispatched; listeners react asynchronously
7. **Async Jobs**: Jobs are queued to RabbitMQ for background workers (send emails, update search index)
8. **JSON Response**: Resource classes format models as JSON; client receives standardized response

**Error handling**: ValidationException → 422; AuthorizationException → 403; ModelNotFoundException → 404

---

## 3. Core Components

### Domain Organization

The application is organized into five domain areas, each self-contained with models, services, and contracts:

![Component Relationships](diagrams/component-relationships.drawio.png)

#### **Catalog Domain** (`app/Domain/Catalog/`)

Manages the product catalog, categories, reviews, and favorites.

| Component | Purpose |
|-----------|---------|
| **Item Model** | Product entity with name, price, stock, description, image, and status (active/inactive). Searchable via Elasticsearch. |
| **Category Model** | Hierarchical grouping of items. Each item belongs to exactly one category. |
| **Comment Model** | User reviews and ratings on items (1-5 stars). Includes comment text. |
| **Favorite Model** | User bookmarks/wishlist. Many-to-many relationship between users and items. |
| **ItemRepository** | Data access layer for item queries; encapsulates query logic. |
| **CatalogService** | Business logic for item operations (listing, filtering by category, search via Elasticsearch). |
| **ItemResource** | API response formatter; transforms Item model to JSON with related data (category, comments count). |

**Key endpoints:**
- `GET /api/v1/items` — List all items with pagination and search
- `GET /api/v1/items/{id}` — Fetch single item details
- `GET /api/v1/items/{id}/comments` — List comments for an item
- `POST /api/v1/items/{id}/comments` — Add comment (authenticated)
- `POST /api/v1/items/{id}/favorite` — Toggle favorite status (authenticated)

#### **Cart Domain** (`app/Domain/Cart/`)

Manages shopping cart state, discount codes, and cart calculations.

| Component | Purpose |
|-----------|---------|
| **Basket Model** | Session cart for user; aggregates items, totals, and applied discounts. Owned by one user. |
| **BasketItem Model** | Individual cart line item; references Item and quantity. Observer recalculates totals on change. |
| **DiscountCode Model** | Promotional codes with fixed/percentage discounts and max discount caps. |
| **BasketService** | Stateless service encapsulating cart logic: add/remove items, apply discounts, calculate totals (including tax). |
| **CartResource** | API response formatter for cart state. |

**BasketItemObserver workflow:**
- When a BasketItem is created/updated/deleted, the observer triggers `Basket::calculateTotals()`
- Totals include: subtotal (sum of items), discount amount (from code), tax, and final total
- Changes are auto-persisted; no explicit save required

**Key endpoints:**
- `GET /api/v1/basket` — View current user's cart
- `POST /api/v1/basket/items` — Add item to cart
- `PATCH /api/v1/basket/items/{itemId}` — Update item quantity
- `DELETE /api/v1/basket/items/{itemId}` — Remove item from cart
- `POST /api/v1/basket/discount` — Apply discount code
- `DELETE /api/v1/basket/discount` — Remove discount

#### **Order Domain** (`app/Domain/Order/`)

Orchestrates order creation, fulfillment, and customer communication.

| Component | Purpose |
|-----------|---------|
| **Order Model** | Purchase record with status (Pending, Paid, Shipped, Delivered). Includes order number, total price, address. Soft-deleted for audit trail. |
| **OrderItem Model** | Snapshot of item at purchase time (name, price, quantity). Immutable after order creation. |
| **Followup Model** | Order status updates and notes; tracks fulfillment progress. |
| **OrderStatus Enum** | State machine: Pending → Paid → Shipped → Delivered (or Cancelled). |
| **OrderService** | Orchestrates checkout: create order from basket, capture payment, enqueue async jobs. |
| **OrderResource** | API response formatter including items and followup history. |

**Checkout flow:**
1. Client calls `POST /api/v1/checkout` with address
2. `CheckoutRequest` validates address and idempotency key (for retry safety)
3. `OrderService::checkout()` atomically: creates Order, creates OrderItems, clears Basket
4. `OrderCreated` event is dispatched; listeners queue emails, SMS notifications
5. Payment flow is triggered separately

**Key endpoints:**
- `POST /api/v1/checkout` — Create order from current basket
- `GET /api/v1/orders` — List user's orders
- `GET /api/v1/orders/{id}` — Fetch order details with items and followups

#### **Payment Domain** (`app/Domain/Payment/`)

Handles payment gateway integration and transaction lifecycle.

| Component | Purpose |
|-----------|---------|
| **Payment Model** | Transaction record with status (Pending, Completed, Failed), reference from gateway, and amount. Immutable audit log. |
| **PaymentGatewayContract** (interface) | Abstraction for payment providers. Forces implementations to define `initiate()` and `verify()` methods. |
| **ZarinpalGateway** | Real Zarinpal gateway integration; calls Zarinpal API for payment initiation and verification. |
| **FakePaymentGateway** | Mock implementation for testing/development; simulates successful/failed payments deterministically. |
| **PaymentService** | Orchestrates payment: calls gateway, handles callbacks, updates Order status. |
| **PaymentCallbackController** | Webhook handler for gateway callbacks; verifies payment and marks Order as Paid. |

**Payment flow:**
1. Client calls `POST /api/v1/initiate-payment` with Order ID
2. `PaymentService::initiate()` contacts the resolved gateway implementation
3. Gateway returns redirect URL and reference number
4. Client redirects to payment provider; user completes payment
5. Payment provider POSTs callback to `/payment/callback`
6. `PaymentCallbackController` verifies callback signature and status
7. If payment succeeded: `PaymentService::verify()` updates Order.status = Paid
8. If payment failed: logs failure and returns error

**Contract-based design:**
- At bootstrap, `AppServiceProvider` binds the contract to the active implementation
- Switching from Zarinpal to Stripe requires only changing the binding; no code changes elsewhere
- Testability: `FakePaymentGateway` used in tests to avoid external API calls

**Key endpoints:**
- `POST /api/v1/initiate-payment` — Start payment for an order
- `POST /payment/callback` — Webhook for gateway callbacks

#### **Customer Domain** (`app/Domain/Customer/`)

Manages user authentication, OTP-based login, and user profiles.

| Component | Purpose |
|-----------|---------|
| **User Model** | Authentication principal with email, password, and role (customer, admin). Sanctum tokens stored separately. |
| **Otp Model** | Time-limited one-time password for passwordless login. Linked to email and expires after 10 minutes. |
| **SmsProviderContract** (interface) | Abstraction for SMS delivery. Implementations define `send(phone, message): bool`. |
| **KavenegarProvider** | Real Kavehnegar SMS gateway integration. |
| **MockSmsProvider** | Mock for dev/test; logs SMS to stdout or returns success without sending. |
| **OtpService** | Generates OTP codes, validates expiry, sends via SMS provider. |
| **AuthController** | REST endpoints for register/login/logout; issues Sanctum tokens. |

**Passwordless login flow:**
1. Client calls `POST /api/v1/login` with email
2. `OtpService::generate()` creates random 6-digit code
3. SMS provider sends code to registered phone
4. Client calls `POST /api/v1/verify-otp` with code
5. `OtpService::verify()` checks expiry and correctness
6. On success: `AuthController` issues Sanctum token
7. Client includes token in `Authorization: Bearer` header for subsequent requests

**Service binding:**
- `AppServiceProvider` binds `SmsProviderContract` to active provider (MockSmsProvider in dev, KavenegarProvider in prod)

**Key endpoints:**
- `POST /api/v1/register` — Create new user account
- `POST /api/v1/login` — Initiate OTP login
- `POST /api/v1/verify-otp` — Complete login with OTP code
- `POST /api/v1/logout` — Revoke token
- `GET /api/v1/user` — Fetch authenticated user

### Shared Layer (`app/Shared/`)

Cross-cutting concerns shared across domains:

| Component | Purpose |
|-----------|---------|
| **DTOs** | Lightweight data containers for inter-service communication (no database access). Example: `CheckoutDTO` with items, address, totals. |
| **Domain Events** | `OrderCreated`, `PaymentProcessed`, `CommentAdded`. Decouples domains; listeners in different domains react to events. |
| **Jobs** | Queueable tasks for RabbitMQ: `SendOrderConfirmationEmail`, `IndexItemToElasticsearch`. |
| **Exceptions** | Custom exceptions: `InvalidDiscountCodeException`, `PaymentFailedException`. Caught by exception handler and converted to HTTP responses. |
| **Traits** | `HasUuid` trait auto-generates UUID primary keys; `HasDomainEvents` for event broadcasting. |
| **Services** | Utility services shared across domains (e.g., `EmailService`, `NotificationService`). |
| **Http Utilities** | Middleware, request/response helpers specific to HTTP layer. |

---

## 4. Data Model

### Entity Relationship Diagram

![Data Model](diagrams/data-model.drawio.png)

### Core Entities

| Entity | Key Attributes | Relationships | Purpose |
|--------|---|---|---|
| **User** | id (UUID), email, password, role | 1:N Baskets, 1:N Orders, 1:N Comments, 1:N Favorites, 1:N OTPs | Authentication principal; owner of baskets, orders, reviews |
| **Basket** | id, user_id (FK), total_amount, discount_amount, status | N:1 User, 1:N BasketItems, N:1 DiscountCode | Current shopping session for user |
| **BasketItem** | id, basket_id (FK), item_id (FK), quantity, price | N:1 Basket, N:1 Item | Line item in cart; snapshot of Item price at add time |
| **Category** | id (UUID), name, slug, description | 1:N Items | Hierarchical organization of products |
| **Item** | id (UUID), category_id (FK), name, slug, price, stock, status | N:1 Category, 1:N BasketItems, 1:N OrderItems, 1:N Comments, 1:N Favorites | Product catalog entry; searchable via Elasticsearch |
| **Comment** | id, item_id (FK), user_id (FK), text, rating | N:1 Item, N:1 User | User reviews; immutable after creation |
| **Favorite** | id, item_id (FK), user_id (FK) | N:1 Item, N:1 User | Wishlist/bookmark; unique constraint (user, item) |
| **DiscountCode** | id, code, percent, max_discount, active | 1:N Baskets | Promotional code; applies % discount up to max |
| **Order** | id (UUID), user_id (FK), basket_id (FK), order_number, total_price, status | N:1 User, N:1 Basket, 1:N OrderItems, 1:N Followups, 1:1 Payment | Purchase record; immutable after creation |
| **OrderItem** | id, order_id (FK), item_id (FK), quantity, price | N:1 Order, N:1 Item | Snapshot of item at purchase time; immutable audit trail |
| **Followup** | id, order_id (FK), status, notes, timestamp | N:1 Order | Order fulfillment history; read-only journal |
| **Payment** | id (UUID), user_id (FK), order_id (FK), amount, status, gateway_reference | N:1 User, 1:1 Order | Transaction record; immutable except status updates |
| **Otp** | id, user_id (FK), code, created_at, expires_at | N:1 User | Time-limited passwordless login code |

**Soft Deletes:** User, Item, Order models use soft deletes for audit compliance. Deleted records remain in DB with `deleted_at` timestamp but are excluded from normal queries.

**UUID Primary Keys:** User, Item, Category, Order, Payment use UUID primary keys instead of auto-incrementing integers. Advantages: no leaking sequential ID sequences, suitable for distributed systems.

---

## 5. API Contracts

### Authentication

All authenticated endpoints require a Sanctum Bearer token in the `Authorization` header:

```
Authorization: Bearer {token}
```

**Ability-based authorization** controls fine-grained permissions:

| Token Ability | Grants Permission To |
|---|---|
| `items:write` | Create, update, delete items (admin-only) |
| `items:read` | Read item details (default for all users) |
| `orders:read` | View own orders |
| `orders:admin` | Admin order management |

Example: `POST /api/v1/items` requires `items:write` ability; regular customers lack this ability even if authenticated.

### Request/Response Format

All endpoints follow REST conventions:

**Successful response (200, 201):**
```json
{
  "data": { "id": "uuid", "name": "Item Name", ... },
  "message": "Item created successfully"
}
```

**Validation error (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a positive number."]
  }
}
```

**Authorization error (403):**
```json
{
  "message": "This action is unauthorized."
}
```

**Not found (404):**
```json
{
  "message": "Item not found."
}
```

### Key Endpoints

#### Items
- `GET /api/v1/items` — List items (searchable, paginated)
- `GET /api/v1/items/{id}` — Fetch item with category and comments count
- `POST /api/v1/items` — Create item (requires `items:write`)
- `PUT|PATCH /api/v1/items/{id}` — Update item
- `DELETE /api/v1/items/{id}` — Soft delete item

#### Cart
- `GET /api/v1/basket` — View cart with items and totals
- `POST /api/v1/basket/items` — Add item to cart (creates/updates BasketItem)
- `PATCH /api/v1/basket/items/{itemId}` — Update quantity
- `DELETE /api/v1/basket/items/{itemId}` — Remove item
- `POST /api/v1/basket/discount` — Apply discount code
- `DELETE /api/v1/basket/discount` — Remove discount

#### Checkout & Orders
- `POST /api/v1/checkout` — Create order from basket (atomic: creates Order, OrderItems, clears Basket)
- `GET /api/v1/orders` — List user's orders
- `GET /api/v1/orders/{id}` — Fetch order with items and followup history

#### Authentication
- `POST /api/v1/register` — Create account (email, password)
- `POST /api/v1/login` — Request OTP (email → SMS)
- `POST /api/v1/verify-otp` — Login with OTP code → returns token
- `POST /api/v1/logout` — Revoke token
- `GET /api/v1/user` — Fetch authenticated user profile

#### Reviews & Wishlist
- `GET /api/v1/items/{id}/comments` — List reviews for item
- `POST /api/v1/items/{id}/comments` — Add review (requires auth)
- `POST /api/v1/items/{id}/favorite` — Toggle favorite status (requires auth)

#### Payment (Internal)
- `POST /api/v1/initiate-payment` — Start payment process → redirect to gateway
- `POST /payment/callback` — Webhook for payment gateway callbacks

---

## 6. Infrastructure & Deployment

### Docker Services

The project uses Docker Compose for local development:

```yaml
rabbitmq:3-management
  - Port 5672 (AMQP) for Laravel job queue
  - Port 15672 (Management UI)
  - Default credentials: guest/guest

elasticsearch:9.0.0
  - Port 9200 (HTTP API)
  - Used by Scout driver for product search
  - Single-node cluster for dev

PostgreSQL (implicit, configured via .env)
  - Primary database
  - Stores all domain models
  - Connection via DATABASE_URL environment variable

Redis (Predis client)
  - Cache store for session, query results, rate limiting
  - Connection configured in config/cache.php
```

**Start services:**
```bash
docker-compose up -d
php artisan migrate
npm run build
php artisan serve
```

### Queue Processing

RabbitMQ jobs are processed by background workers:

```bash
# Start queue worker (listens on 'default' queue)
php artisan queue:work rabbitmq

# Start multiple workers (for load distribution)
php artisan queue:work rabbitmq --processes=4
```

Jobs in the queue:
- `SendOrderConfirmationEmail` — Email customer after order creation
- `IndexItemToElasticsearch` — Update search index when item changes
- `NotifyPaymentFailure` — Alert user and admin on payment failure

### Search Indexing (Elasticsearch via Scout)

Items are indexed automatically when created/updated:

```php
// app/Domain/Catalog/Models/Item.php
class Item extends Model {
    use Searchable;
    
    public function toSearchableArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name,
        ];
    }
}
```

Search queries:
```bash
# Full-text search via Elasticsearch
GET /api/v1/items?search=laptop
```

Scout driver syncs changes via `IndexItemToElasticsearch` job queued to RabbitMQ.

### Environment Configuration

Key `.env` variables:

```
APP_ENV=production
DATABASE_URL=postgresql://user:pass@localhost:5432/shop

QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672

CACHE_DRIVER=redis
REDIS_HOST=localhost
REDIS_PORT=6379

SCOUT_DRIVER=elastic
ELASTICSEARCH_HOSTS=localhost:9200

SMS_PROVIDER=kavehnegar  # or mock
PAYMENT_GATEWAY=zarinpal  # or fake
```

---

## 7. Extension Patterns

### Adding a New Domain

To add a new domain (e.g., **Inventory** for stock tracking):

1. **Create directory structure:**
   ```
   app/Domain/Inventory/
   ├── Models/
   │   ├── StockLevel.php
   │   └── StockMovement.php
   ├── Services/
   │   └── InventoryService.php
   ├── Events/
   │   └── StockLevelChanged.php
   ├── Enums/
   │   └── MovementType.php
   └── Repositories/
       └── StockRepository.php
   ```

2. **Define Eloquent models** with relationships to existing domains:
   ```php
   class StockLevel extends Model {
       public function item() { return $this->belongsTo(Item::class); }
       public function movements() { return $this->hasMany(StockMovement::class); }
   }
   ```

3. **Create service for business logic:**
   ```php
   class InventoryService {
       public function decrementStock(Item $item, int $quantity): void {
           // Atomic decrement with lock
           DB::transaction(function () use ($item, $quantity) {
               $stock = StockLevel::lockForUpdate()->find($item->id);
               $stock->quantity -= $quantity;
               $stock->save();
               StockMovement::create(['stock_level_id' => $stock->id, ...]);
           });
           
           // Dispatch event for downstream listeners
           StockLevelChanged::dispatch($item, $stock->quantity);
       }
   }
   ```

4. **Integrate with existing domains:**
   - Listen to `OrderCreated` event → call `InventoryService::decrementStock()`
   - Listen to `OrderCancelled` event → call `InventoryService::incrementStock()`

5. **Add API endpoints:**
   ```php
   Route::middleware('auth:sanctum')->prefix('inventory')->group(function () {
       Route::get('/stock/{item}', [InventoryController::class, 'show']);
       Route::get('/movements', [MovementController::class, 'index']);
   });
   ```

### Adding a Payment Provider

To integrate a new payment gateway (e.g., Stripe):

1. **Implement the contract:**
   ```php
   namespace App\Domain\Payment\Services\Gateways;
   
   class StripeGateway implements PaymentGatewayContract {
       public function initiate(InitiatePaymentRequest $request): array {
           $session = \Stripe\Checkout\Session::create([
               'payment_method_types' => ['card'],
               'line_items' => [[
                   'price_data' => ['currency' => 'usd', 'unit_amount' => $request->amount * 100],
                   'quantity' => 1,
               ]],
               'mode' => 'payment',
               'success_url' => route('payment.success'),
           ]);
           return ['redirect_url' => $session->url, 'reference' => $session->id];
       }
       
       public function verify(PaymentCallbackRequest $request): bool {
           $event = \Stripe\Webhook::constructEvent($request->getContent(), ...);
           return $event->type === 'checkout.session.completed';
       }
   }
   ```

2. **Update service provider binding:**
   ```php
   // app/Providers/AppServiceProvider.php
   $this->app->singleton(
       PaymentGatewayContract::class,
       env('PAYMENT_GATEWAY') === 'stripe' ? StripeGateway::class : ZarinpalGateway::class
   );
   ```

3. **No controller/route changes required** — contract abstraction handles routing.

### Adding a New SMS Provider

To add a new SMS provider (e.g., Twilio):

1. **Implement the contract:**
   ```php
   class TwilioProvider implements SmsProviderContract {
       public function send(string $phone, string $message): bool {
           try {
               $this->client->messages->create($phone, [
                   'from' => config('sms.twilio_number'),
                   'body' => $message,
               ]);
               return true;
           } catch (\Exception $e) {
               Log::error('Twilio SMS failed', ['error' => $e->getMessage()]);
               return false;
           }
       }
   }
   ```

2. **Update service provider:**
   ```php
   $this->app->bind(
       SmsProviderContract::class,
       env('SMS_PROVIDER') === 'twilio' ? TwilioProvider::class : KavenegarProvider::class
   );
   ```

### Adding Custom Validation Rules

Form Requests inherit from `FormRequest` and define rules:

```php
class StoreItemRequest extends FormRequest {
    public function rules(): array {
        return [
            'name' => 'required|string|max:255|unique:items',
            'price' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }
    
    public function messages(): array {
        return [
            'price.min' => 'The price must be greater than zero.',
        ];
    }
}
```

Custom rule:
```php
'discount_code' => 'required|exists:discount_codes,code|validate_discount_code:active,today',
```

---

## 8. Rules & Anti-Patterns

### Do's ✓

- **Use dependency injection**: Inject services via constructor; avoid `app()` or `resolve()` in domain logic
- **Dispatch domain events**: When state changes, fire events; let listeners handle side effects (emails, logs)
- **Validate in Form Requests**: Move validation logic out of controllers; keep controllers thin
- **Use DTOs for inter-service communication**: Avoid passing Eloquent models between services; use typed data containers
- **Keep repositories thin**: Encapsulate queries; don't leak query builder chains to controllers
- **Use transactions for atomic operations**: Wrap multi-step operations in `DB::transaction()` to prevent partial updates
- **Implement soft deletes**: Mark records as deleted without removing; preserves audit trail
- **Use enum for status fields**: ItemStatus, OrderStatus, PaymentStatus enums prevent invalid state transitions
- **Test contracts and abstractions**: Write tests against interfaces (PaymentGatewayContract, SmsProviderContract) to verify implementations

### Anti-Patterns ✗

- **God Service**: Never create a single service that handles all domain logic. Split by responsibility (OrderService, PaymentService, BasketService)
- **Eloquent in Form Requests**: Don't query the database inside validation rules. Use custom validators or lazy assertions in controller
- **Direct controller-to-controller calls**: Controllers shouldn't call other controllers. Refactor shared logic into services
- **Leaking query builders**: Don't expose `query()` or `->get()` results from repositories. Return typed collections or models
- **Hardcoded configuration**: Never hardcode API keys, URLs, or environment-specific values in code. Use `config()` or `env()`
- **Synchronous long operations**: Never do HTTP calls or I/O (emails, API calls) synchronously in request handlers. Queue them as jobs
- **N+1 queries**: Always use eager loading (`with()`) when fetching related models
- **Ignoring soft deletes**: Remember that soft-deleted records are hidden by default; use `withTrashed()` only when intentional
- **Circular dependencies**: Domain A shouldn't depend on Domain B while Domain B depends on Domain A. Use events to decouple

---

## 9. Dependencies

### Core Framework

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^13.8 | HTTP routing, ORM, middleware, service container |
| `laravel/sanctum` | ^4.3 | Token-based API authentication |
| `laravel/scout` | >=11.6 <12.0 | Fulltext search abstraction |

### Search & Indexing

| Package | Version | Purpose |
|---------|---------|---------|
| `babenkoivan/elastic-scout-driver` | >=6.0 <7.0 | Elasticsearch integration for Scout |
| `elasticsearch/elasticsearch` | (transitive) | Elasticsearch PHP client |

### Caching & Queuing

| Package | Version | Purpose |
|---------|---------|---------|
| `predis/predis` | ^3.5 | Redis client for cache and sessions |
| `vladimir-yuldashev/laravel-queue-rabbitmq` | ^15.0 | RabbitMQ queue driver |
| `php-amqplib/php-amqplib` | (transitive) | AMQP protocol implementation |

### Development & Testing

| Package | Version | Purpose |
|---------|---------|---------|
| `phpunit/phpunit` | ^12.5.12 | Unit and feature test runner |
| `mockery/mockery` | ^1.6 | Mocking library for tests |
| `fakerphp/faker` | ^1.23 | Fake data generator for seeders |
| `knuckleswtf/scribe` | ^5.11 | API documentation generator |
| `laravel/pint` | ^1.27 | PHP code style fixer (PSR-12) |
| `laravel/telescope` | ^5.22 | Debug toolbar; inspect requests, jobs, events |

### Utilities

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/tinker` | ^3.0 | Interactive shell for debugging |
| `nunomaduro/collision` | ^8.6 | Enhanced error output formatting |

---

## 10. Code Structure

```
Shop/
├── app/                              # Application code
│   ├── Domain/                       # Domain-Driven Design organization
│   │   ├── Catalog/
│   │   │   ├── Models/               # Eloquent models
│   │   │   ├── Services/             # Business logic
│   │   │   ├── Resources/            # JSON response formatters
│   │   │   ├── Repositories/         # Data access layer
│   │   │   ├── Requests/             # Form request validation
│   │   │   ├── Events/               # Domain events
│   │   │   ├── Listeners/            # Event handlers
│   │   │   ├── Enums/                # Status enumerations
│   │   │   └── Actions/              # Action classes for specific workflows
│   │   ├── Cart/
│   │   ├── Order/
│   │   ├── Payment/
│   │   └── Customer/
│   │
│   ├── Shared/                       # Cross-cutting concerns
│   │   ├── Actions/                  # Shared action classes
│   │   ├── DTOs/                     # Data transfer objects
│   │   ├── Exceptions/               # Custom exceptions
│   │   ├── Http/                     # Shared HTTP utilities
│   │   ├── Jobs/                     # Queueable jobs for RabbitMQ
│   │   ├── Services/                 # Shared utility services
│   │   └── Traits/                   # Reusable traits (HasUuid, etc)
│   │
│   ├── Http/                         # HTTP layer (framework specific)
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/               # V1 API endpoints
│   │   │   │   └── V2/               # V2 API endpoints
│   │   │   ├── Auth/                 # Authentication controllers
│   │   │   └── Payment/              # Payment webhook handlers
│   │   ├── Middleware/               # HTTP middleware
│   │   ├── Requests/                 # Global form requests
│   │   └── Resources/                # Global response formatters
│   │
│   ├── Enums/                        # Global enumerations
│   ├── Helpers/                      # Helper functions
│   ├── Mail/                         # Mailable classes
│   ├── Models/                       # Global (non-domain) models
│   ├── Observers/                    # Eloquent model observers
│   ├── Policies/                     # Authorization policies
│   ├── Providers/                    # Service providers
│   ├── Repositories/                 # Global repositories
│   ├── Services/                     # Global services
│   ├── Traits/                       # Global traits
│   └── View/                         # View components (if using Blade)
│
├── bootstrap/                        # Framework bootstrap files
├── config/                           # Configuration files
│   ├── app.php                       # Application config
│   ├── database.php                  # Database driver config
│   ├── elastic.client.php            # Elasticsearch config
│   ├── elastic.scout_driver.php      # Scout-Elasticsearch config
│   ├── queue.php                     # Queue driver config (RabbitMQ)
│   └── ...
│
├── database/
│   ├── migrations/                   # Schema migrations (up/down)
│   ├── factories/                    # Model factories for testing
│   └── seeders/                      # Database seeders
│
├── routes/
│   ├── api.php                       # API route registration
│   ├── web.php                       # Web routes (if any)
│   ├── auth.php                      # Authentication routes
│   ├── Api/
│   │   ├── v1.php                    # V1 endpoint definitions
│   │   └── v2.php                    # V2 endpoint definitions
│   └── console.php                   # Artisan command routes
│
├── storage/                          # Application storage (logs, uploads)
├── tests/                            # Test suite
│   ├── Feature/                      # End-to-end tests
│   ├── Unit/                         # Unit tests
│   └── TestCase.php                  # Base test class
│
├── resources/
│   ├── views/                        # Blade templates (if using)
│   ├── css/                          # Frontend styles
│   └── js/                           # Frontend scripts
│
├── public/                           # Web-accessible directory
├── docker-compose.yml                # Docker services for dev
├── Dockerfile                        # Container image (if provided)
├── composer.json                     # PHP dependencies
├── package.json                      # Node dependencies
└── README.md                         # Project overview
```

---

## 11. Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js & npm
- Docker (for RabbitMQ, Elasticsearch, PostgreSQL)

### Setup Instructions

1. **Clone and install dependencies:**
   ```bash
   git clone <repository>
   cd Shop
   composer install
   npm install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Start services:**
   ```bash
   docker-compose up -d
   ```

4. **Migrate database:**
   ```bash
   php artisan migrate --seed
   ```

5. **Build assets:**
   ```bash
   npm run build
   ```

6. **Start development server:**
   ```bash
   php artisan serve
   ```

7. **Process queued jobs (in another terminal):**
   ```bash
   php artisan queue:work rabbitmq
   ```

**Access:**
- API: http://localhost:8000/api/v1
- Queue Management: http://localhost:15672 (RabbitMQ)
- Elasticsearch: http://localhost:9200
- Telescope (Debug): http://localhost:8000/telescope

### Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/OrderTest.php

# Run in parallel
php artisan test --parallel
```

### API Documentation

Interactive API docs (via Scribe):

```bash
php artisan scribe:generate
```

Generates OpenAPI spec and renders at `/docs`.

---

## 12. Support & Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| "Queue connection error" | RabbitMQ not running | `docker-compose up -d rabbitmq` |
| "Search not working" | Elasticsearch not indexed | `php artisan scout:sync-index-settings` then reindex items |
| "Authentication failed" | Token expired | Token TTL default is 1 year; check `SANCTUM_EXPIRATION` in .env |
| "Payment gateway failing" | FakePaymentGateway not active | Set `PAYMENT_GATEWAY=fake` in .env for testing |
| "N+1 query warnings" | Missing eager loading | Use `with(['category', 'comments'])` when fetching items |
| "Cache not clearing" | Redis not flushed | `redis-cli flushall` or `php artisan cache:clear` |

---

**Document Version:** 1.0  
**Last Updated:** September 10, 2026  
**Audience:** Engineering Team, Architects, Stakeholders
