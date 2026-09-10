# Shop API

> Modern E-Commerce REST API built with Laravel 13 using Domain-Driven Design principles, microservices-ready architecture with Elasticsearch, RabbitMQ, and Redis integration.

[![Laravel 13.8](https://img.shields.io/badge/Laravel-13.8-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Elasticsearch](https://img.shields.io/badge/Elasticsearch-005571?logo=elasticsearch&logoColor=white)](https://www.elastic.co)
[![RabbitMQ](https://img.shields.io/badge/RabbitMQ-FF6600?logo=rabbitmq&logoColor=white)](https://www.rabbitmq.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Architecture](#-architecture)
- [Quick Start](#-quick-start)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Project Structure](#-project-structure)
- [Development](#-development)
- [Contributing](#-contributing)
- [Support](#-support)
- [License](#-license)

---

## ✨ Features

### Core Functionality
- **Multi-Domain Architecture** — 5 isolated domains (Catalog, Cart, Order, Payment, Customer) with DDD principles
- **Product Catalog Management** — Full-featured product management with categories, reviews, and wishlist
- **Shopping Cart** — Stateful cart with discount codes and automatic total calculations
- **Order Management** — Complete order lifecycle with status tracking and fulfillment history
- **Payment Processing** — Pluggable payment gateway abstraction (Zarinpal, Stripe-ready)
- **User Authentication** — Passwordless OTP-based login via SMS with role-based authorization

### Technical Highlights
- **Versioned API** — v1 and v2 endpoints for backward compatibility
- **Full-Text Search** — Elasticsearch integration for lightning-fast product search
- **Asynchronous Processing** — RabbitMQ job queue for emails, notifications, search indexing
- **Smart Caching** — Redis caching for sessions, query results, and rate limiting
- **Token-Based Auth** — Laravel Sanctum with ability-based fine-grained permissions
- **Event-Driven Architecture** — Domain events decouple business logic across domains
- **Audit Trail** — Soft deletes preserve historical data for compliance

---

## 🛠 Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Backend Framework** | Laravel | 13.8 |
| **Language** | PHP | 8.3+ |
| **Primary Database** | PostgreSQL | 12+ |
| **Search Engine** | Elasticsearch | 9.0 |
| **Message Queue** | RabbitMQ | 3.0+ |
| **Cache Store** | Redis | 6.0+ |
| **Authentication** | Laravel Sanctum | 4.3+ |
| **Search Integration** | Laravel Scout | 11.6+ |
| **Testing** | PHPUnit | 12.5+ |
| **Code Quality** | Laravel Pint | 1.27+ |

---

## 🏗 Architecture

### Domain-Driven Design

The application is organized into **5 independent domains**, each with clear boundaries and responsibilities:

```
┌─────────────────────────────────────────────────────────┐
│                    HTTP API Layer                        │
│            (Controllers, Routes, Middleware)             │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                   Domain Layer                           │
│  ┌──────────┬────────┬────────┬──────────┬──────────┐  │
│  │ Catalog  │ Cart   │ Order  │ Payment  │ Customer │  │
│  │ Domain   │ Domain │ Domain │ Domain   │ Domain   │  │
│  └──────────┴────────┴────────┴──────────┴──────────┘  │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                  Shared Layer                            │
│  (DTOs, Events, Jobs, Exceptions, Traits, Services)    │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│          Infrastructure & External Services             │
│  ┌──────────┬─────────────┬─────────┬──────────────┐   │
│  │PostgreSQL│Elasticsearch│ Redis   │  RabbitMQ    │   │
│  └──────────┴─────────────┴─────────┴──────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### Key Domains

- **🛍️ Catalog Domain** — Products, categories, reviews, favorites
- **🛒 Cart Domain** — Shopping cart, discount codes, calculations
- **📦 Order Domain** — Order creation, fulfillment, tracking
- **💳 Payment Domain** — Payment gateway integration, transactions
- **👤 Customer Domain** — Authentication, OTP, user profiles

See [docs/project-summary.md](docs/project-summary.md) for detailed architecture documentation with diagrams.

---

## 🚀 Quick Start

### Prerequisites

```bash
# Check requirements
php -v          # PHP 8.3+
composer -V     # Composer
node -v         # Node.js 16+
docker -v       # Docker
```

### Installation & Setup (5 minutes)

```bash
# 1. Clone repository
git clone <repository-url>
cd Shop

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Start Docker services
docker-compose up -d

# 5. Initialize database
php artisan migrate --seed

# 6. Build assets
npm run build

# 7. Start development server
php artisan serve

# 8. Start queue worker (in new terminal)
php artisan queue:work rabbitmq
```

**Access the application:**
- 🌐 **API**: http://localhost:8000/api/v1
- 📊 **RabbitMQ**: http://localhost:15672 (guest/guest)
- 🔍 **Elasticsearch**: http://localhost:9200
- 🔧 **Telescope Debug**: http://localhost:8000/telescope

---

## 📦 Installation

### System Requirements

- **PHP** 8.3 or higher with extensions: OpenSSL, PDO, Mbstring, Tokenizer, JSON, cURL, DOM
- **Composer** 2.0 or higher
- **Node.js** 16+ with npm
- **Docker** & Docker Compose for services (optional for production)
- **PostgreSQL** 12+ or compatible database
- **Redis** 6.0+ for caching

### Step-by-Step Installation

#### 1. Clone Repository

```bash
git clone https://github.com/yourusername/shop-api.git
cd shop-api
```

#### 2. Install PHP Dependencies

```bash
composer install --no-dev  # Production
# or
composer install           # Development
```

#### 3. Install Node Dependencies

```bash
npm install --production   # Production
# or
npm install               # Development
```

#### 4. Environment Configuration

```bash
# Copy example environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env with your configuration
nano .env
```

#### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan migrate --seed

# Create search index
php artisan scout:import "App\Domain\Catalog\Models\Item"
```

#### 6. Build Assets

```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

---

## ⚙️ Configuration

### Key Environment Variables

```env
# App Settings
APP_NAME=Shop
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.shop.local

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=shop
DB_USERNAME=postgres
DB_PASSWORD=secret

# Queue
QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest

# Cache
CACHE_DRIVER=redis
REDIS_HOST=localhost
REDIS_PORT=6379

# Search
SCOUT_DRIVER=elastic
ELASTICSEARCH_HOSTS=localhost:9200

# Authentication
SANCTUM_EXPIRATION=525600  # 1 year in minutes

# External Services
PAYMENT_GATEWAY=zarinpal    # or 'fake' for testing
SMS_PROVIDER=kavehnegar     # or 'mock' for testing

# Mail (for notifications)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
```

### Docker Services

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

**Services included in docker-compose.yml:**
- 🐰 **RabbitMQ** — Message queue for async jobs
- 🔍 **Elasticsearch** — Search engine for full-text search
- 📊 **PostgreSQL** (optional) — Primary database
- 📈 **Redis** (optional) — Cache store

---

## 💡 Usage

### Authentication

All authenticated requests require a Sanctum Bearer token:

```bash
# Register new user
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login (request OTP)
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'

# Verify OTP and get token
curl -X POST http://localhost:8000/api/v1/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "code": "123456"}'

# Use token in subsequent requests
curl -X GET http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Product Management

```bash
# List products (with search)
curl 'http://localhost:8000/api/v1/items?search=laptop'

# Get product details
curl 'http://localhost:8000/api/v1/items/{id}'

# Get product reviews
curl 'http://localhost:8000/api/v1/items/{id}/comments'

# Add product review (authenticated)
curl -X POST http://localhost:8000/api/v1/items/{id}/comments \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"text": "Great product!", "rating": 5}'
```

### Shopping Cart

```bash
# View cart
curl -X GET http://localhost:8000/api/v1/basket \
  -H "Authorization: Bearer TOKEN"

# Add item to cart
curl -X POST http://localhost:8000/api/v1/basket/items \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"item_id": "uuid", "quantity": 2}'

# Apply discount code
curl -X POST http://localhost:8000/api/v1/basket/discount \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"code": "SUMMER20"}'

# Checkout
curl -X POST http://localhost:8000/api/v1/checkout \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"address": "123 Main St, City, Country"}'
```

---

## 📚 API Documentation

### Interactive Documentation

Generate API documentation with Scribe:

```bash
php artisan scribe:generate
```

Then visit: http://localhost:8000/docs

### API Endpoints

#### Authentication
- `POST /api/v1/register` — Create account
- `POST /api/v1/login` — Request OTP
- `POST /api/v1/verify-otp` — Complete login
- `POST /api/v1/logout` — Logout
- `GET /api/v1/user` — Get profile

#### Catalog
- `GET /api/v1/items` — List products (searchable, paginated)
- `GET /api/v1/items/{id}` — Get product details
- `GET /api/v1/items/{id}/comments` — List reviews
- `POST /api/v1/items/{id}/comments` — Add review
- `GET /api/v1/categories` — List categories

#### Cart & Checkout
- `GET /api/v1/basket` — View cart
- `POST /api/v1/basket/items` — Add to cart
- `PATCH /api/v1/basket/items/{itemId}` — Update quantity
- `DELETE /api/v1/basket/items/{itemId}` — Remove item
- `POST /api/v1/basket/discount` — Apply discount
- `POST /api/v1/checkout` — Create order

#### Orders
- `GET /api/v1/orders` — List user orders
- `GET /api/v1/orders/{id}` — Get order details

See complete API documentation in [docs/project-summary.md](docs/project-summary.md#5-api-contracts)

---

## 📁 Project Structure

```
Shop/
├── app/
│   ├── Domain/                 # Domain-Driven Design organization
│   │   ├── Catalog/           # Product catalog domain
│   │   ├── Cart/              # Shopping cart domain
│   │   ├── Order/             # Order management domain
│   │   ├── Payment/           # Payment processing domain
│   │   └── Customer/          # User/authentication domain
│   │
│   ├── Shared/                # Cross-cutting concerns
│   │   ├── Actions/           # Reusable action classes
│   │   ├── DTOs/              # Data transfer objects
│   │   ├── Events/            # Domain events
│   │   ├── Jobs/              # Async queue jobs
│   │   └── Services/          # Shared services
│   │
│   └── Http/                  # HTTP layer
│       ├── Controllers/       # API controllers
│       └── Requests/          # Form request validation
│
├── config/                     # Configuration files
├── database/
│   ├── migrations/            # Database schema
│   ├── factories/             # Model factories for testing
│   └── seeders/               # Database seeders
│
├── routes/
│   ├── api.php               # Main API routes
│   └── Api/
│       ├── v1.php            # V1 endpoints
│       └── v2.php            # V2 endpoints
│
├── tests/
│   ├── Feature/              # End-to-end tests
│   └── Unit/                 # Unit tests
│
├── docs/                      # Documentation
│   ├── project-summary.md    # Architecture documentation
│   └── diagrams/             # Architecture diagrams
│
├── docker-compose.yml         # Docker services
├── composer.json             # PHP dependencies
├── package.json              # Node dependencies
└── README.md                 # This file
```

See [docs/project-summary.md](docs/project-summary.md) for detailed documentation.

---

## 🧪 Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage report
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/OrderTest.php

# Run tests in parallel
php artisan test --parallel

# Run tests with output
php artisan test --verbose
```

### Code Quality

```bash
# Format code (PSR-12 standard)
composer run pint

# Static analysis (Pest/PHPStan)
composer run analyse

# Lint PHP files
php -l app/Domain/*/Models/*.php
```

### Database Management

```bash
# Create fresh database
php artisan migrate:fresh

# Refresh with seeds
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Rollback all
php artisan migrate:reset
```

### Queue Management

```bash
# Start queue worker
php artisan queue:work rabbitmq

# Start with timeout and sleep
php artisan queue:work rabbitmq --timeout=300 --sleep=3

# Process single job
php artisan queue:work rabbitmq --once

# Monitor queue
php artisan queue:monitor
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear

# Clear specific cache
php artisan cache:forget key-name

# Flush Redis entirely
redis-cli FLUSHALL
```

### Debugging

```bash
# Interactive shell (Tinker)
php artisan tinker

# Monitor requests in real-time (Telescope)
# Visit: http://localhost:8000/telescope

# View logs
tail -f storage/logs/laravel.log

# Clear logs
php artisan log:clear
```

---

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Getting Started

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Make** your changes
4. **Commit** with clear messages (`git commit -m 'Add amazing feature'`)
5. **Push** to your branch (`git push origin feature/amazing-feature`)
6. **Open** a Pull Request

### Code Standards

- **PHP Coding Standards**: PSR-12 (enforced by Pint)
- **Testing**: Write tests for all new features
- **Documentation**: Update README and comments for significant changes
- **Database**: Create migrations for schema changes

### Pull Request Process

1. Ensure tests pass: `php artisan test`
2. Run code formatter: `composer run pint`
3. Update documentation if needed
4. Provide clear PR description explaining changes
5. Link related issues if applicable

### Reporting Issues

- Check existing issues first
- Provide clear description and steps to reproduce
- Include PHP, Laravel, and package versions
- Attach relevant error logs or screenshots

---

## 🆘 Support

### Getting Help

- 📖 **Documentation**: [docs/project-summary.md](docs/project-summary.md)
- 🐛 **Issues**: [GitHub Issues](issues)
- 💬 **Discussions**: [GitHub Discussions](discussions)
- 📧 **Email**: contact@shop-api.local

### Common Issues

**Q: Queue jobs not processing**
```bash
# Make sure RabbitMQ is running
docker-compose up -d rabbitmq

# Start queue worker in another terminal
php artisan queue:work rabbitmq
```

**Q: Search not working**
```bash
# Ensure Elasticsearch is running
docker-compose up -d elasticsearch

# Re-index products
php artisan scout:flush "App\Domain\Catalog\Models\Item"
php artisan scout:import "App\Domain\Catalog\Models\Item"
```

**Q: Database connection error**
```bash
# Verify .env database credentials
# Create database if needed
createdb shop

# Run migrations
php artisan migrate
```

**Q: Authentication issues**
```bash
# Verify SANCTUM_EXPIRATION in .env
# Default: 525600 (1 year)

# Clear expired tokens
php artisan sanctum:prune-expired
```

See [docs/project-summary.md#12-support--troubleshooting](docs/project-summary.md#12-support--troubleshooting) for more troubleshooting tips.

---

## 📄 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2026 Shop API

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions...
```

---

## 🙌 Acknowledgments

- [Laravel Framework](https://laravel.com) — The PHP framework used
- [Elasticsearch](https://www.elastic.co) — Search engine integration
- [RabbitMQ](https://www.rabbitmq.com) — Message queue system
- [PostgreSQL](https://www.postgresql.org) — Database system
- All contributors and community members

---

## 📞 Contact & Social

- **Author**:
- **Email**:
- **GitHub**: 
- **Website**:

---

**Made with ❤️ using Laravel 13, Elasticsearch, RabbitMQ, and Redis**

*Last updated: September 10, 2026*
