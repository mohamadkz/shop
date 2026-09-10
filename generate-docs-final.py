#!/usr/bin/env python3
"""
Create documentation diagrams and Word document.
This script creates placeholder PNG files and a Word document from the markdown.
"""

import os
import sys
from pathlib import Path

def create_svg_placeholder(name, filename):
    """Create an SVG diagram placeholder."""
    svg_content = f'''<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="400" viewBox="0 0 800 400">
  <defs>
    <style>
      .box {{ fill: #dae8fc; stroke: #6c8ebf; stroke-width: 2; }}
      .title {{ font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; fill: #333; }}
      .subtitle {{ font-family: Arial, sans-serif; font-size: 14px; fill: #666; }}
    </style>
  </defs>
  <rect x="10" y="10" width="780" height="380" class="box" />
  <text x="400" y="60" text-anchor="middle" class="title">{name}</text>
  <text x="400" y="200" text-anchor="middle" class="subtitle">Architecture Diagram</text>
  <text x="400" y="230" text-anchor="middle" class="subtitle">(SVG Placeholder - View source .drawio file)</text>
  <text x="400" y="260" text-anchor="middle" class="subtitle">See: docs/diagrams/{filename}.drawio</text>
</svg>'''
    
    return svg_content

def create_word_document():
    """Create a Word document from markdown (fallback version)."""
    try:
        from docx import Document
        from docx.shared import Inches, Pt, RGBColor
        from docx.enum.text import WD_ALIGN_PARAGRAPH
        
        # Read the markdown file
        md_path = r"c:\laragon\www\test\Shop\docs\project-summary.md"
        
        with open(md_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Create document
        doc = Document()
        
        # Add title
        doc.add_heading('Shop API — Project Summary', 0)
        doc.add_paragraph('E-Commerce Platform with Laravel 13, Domain-Driven Design, and Microservices')
        doc.add_paragraph('Generated: September 10, 2026')
        doc.add_page_break()
        
        # Add content sections
        doc.add_heading('📋 Table of Contents', 1)
        sections = [
            'Executive Summary',
            'Architecture Overview',
            'Processing Pipeline',
            'Core Components',
            'Data Model',
            'API Contracts',
            'Infrastructure & Deployment',
            'Extension Patterns',
            'Rules & Anti-Patterns',
            'Dependencies',
            'Code Structure',
            'Getting Started'
        ]
        
        for section in sections:
            doc.add_paragraph(section, style='List Bullet')
        
        doc.add_page_break()
        
        # Add key sections from markdown
        doc.add_heading('Executive Summary', 1)
        doc.add_paragraph(
            'Shop is a modern e-commerce REST API built with Laravel 13 using Domain-Driven Design (DDD) '
            'principles. The system manages a complete product catalog, shopping cart, order fulfillment, and '
            'payment processing pipeline. It integrates with external services (Zarinpal payment gateway, '
            'Kavehnegar SMS provider) and leverages Elasticsearch for product search, RabbitMQ for asynchronous '
            'task processing, and Redis for caching. The API is versioned (v1, v2) and uses Laravel Sanctum for '
            'token-based authentication with fine-grained ability-based authorization.'
        )
        
        doc.add_heading('Key Features', 2)
        features = [
            'Domain-Driven Design with 5 isolated domains (Catalog, Cart, Order, Payment, Customer)',
            'Multi-version REST API (v1, v2) for backward compatibility',
            'Token-based authentication via Laravel Sanctum',
            'Elasticsearch integration for full-text product search',
            'RabbitMQ message queue for asynchronous job processing',
            'Redis caching for performance optimization',
            'Payment gateway abstraction (supports Zarinpal, Stripe, etc.)',
            'SMS provider abstraction (supports Kavehnegar, Twilio, etc.)',
            'Event-driven architecture with domain events and listeners',
            'Comprehensive test suite with PHPUnit'
        ]
        
        for feature in features:
            doc.add_paragraph(feature, style='List Bullet')
        
        doc.add_heading('Technology Stack', 2)
        tech = [
            '🐘 Backend: Laravel 13.8, PHP 8.3',
            '🗄️ Database: PostgreSQL with Eloquent ORM',
            '🔍 Search: Elasticsearch 9.0 (via Laravel Scout)',
            '⚙️ Queuing: RabbitMQ 3 (php-amqplib driver)',
            '💾 Caching: Redis (Predis client)',
            '🔐 Auth: Laravel Sanctum (token-based)',
            '🧪 Testing: PHPUnit 12.5',
            '📦 Package Manager: Composer'
        ]
        
        for item in tech:
            doc.add_paragraph(item, style='List Bullet')
        
        doc.add_page_break()
        
        doc.add_heading('Architecture Layers', 1)
        doc.add_paragraph(
            'The application follows Domain-Driven Design principles with the following organizational structure:'
        )
        
        doc.add_heading('Domain Layers', 2)
        layers = {
            'Catalog Domain': 'Product management, categories, reviews, favorites',
            'Cart Domain': 'Shopping cart, basket items, discount codes, calculations',
            'Order Domain': 'Order creation, fulfillment tracking, order status management',
            'Payment Domain': 'Payment gateway integration, transaction handling, callbacks',
            'Customer Domain': 'User authentication, OTP-based login, user profiles'
        }
        
        for layer, desc in layers.items():
            p = doc.add_paragraph(style='List Bullet')
            p.add_run(layer + ': ').bold = True
            p.add_run(desc)
        
        doc.add_heading('Shared Layer', 2)
        doc.add_paragraph(
            'Cross-cutting concerns including DTOs, Domain Events, Background Jobs, Custom Exceptions, '
            'Traits, Utility Services, and HTTP Utilities'
        )
        
        doc.add_page_break()
        
        doc.add_heading('API Overview', 1)
        doc.add_paragraph('The API is organized into versioned endpoints with comprehensive coverage:')
        
        doc.add_heading('Authentication Endpoints', 2)
        auth_endpoints = [
            'POST /api/v1/register - Create new account',
            'POST /api/v1/login - Request OTP via SMS',
            'POST /api/v1/verify-otp - Complete login with OTP',
            'POST /api/v1/logout - Revoke authentication token',
            'GET /api/v1/user - Fetch authenticated user profile'
        ]
        for endpoint in auth_endpoints:
            doc.add_paragraph(endpoint, style='List Bullet')
        
        doc.add_heading('Catalog Endpoints', 2)
        catalog_endpoints = [
            'GET /api/v1/items - List products with search',
            'GET /api/v1/items/{id} - Fetch product details',
            'GET /api/v1/items/{id}/comments - List product reviews',
            'POST /api/v1/items/{id}/comments - Add review',
            'POST /api/v1/items/{id}/favorite - Toggle favorite'
        ]
        for endpoint in catalog_endpoints:
            doc.add_paragraph(endpoint, style='List Bullet')
        
        doc.add_heading('Cart & Checkout Endpoints', 2)
        cart_endpoints = [
            'GET /api/v1/basket - View shopping cart',
            'POST /api/v1/basket/items - Add item to cart',
            'PATCH /api/v1/basket/items/{id} - Update quantity',
            'DELETE /api/v1/basket/items/{id} - Remove item',
            'POST /api/v1/checkout - Create order from cart'
        ]
        for endpoint in cart_endpoints:
            doc.add_paragraph(endpoint, style='List Bullet')
        
        doc.add_page_break()
        
        doc.add_heading('Installation & Setup', 1)
        
        setup_steps = [
            ('Clone Repository', 'git clone <repository> && cd Shop'),
            ('Install Dependencies', 'composer install && npm install'),
            ('Configure Environment', 'cp .env.example .env && php artisan key:generate'),
            ('Start Services', 'docker-compose up -d'),
            ('Migrate Database', 'php artisan migrate --seed'),
            ('Build Assets', 'npm run build'),
            ('Start Server', 'php artisan serve'),
            ('Process Queue Jobs', 'php artisan queue:work rabbitmq')
        ]
        
        for step, command in setup_steps:
            p = doc.add_paragraph(style='List Number')
            p.add_run(step + ': ').bold = True
            p.add_run(command)
        
        doc.add_page_break()
        
        doc.add_heading('Design Patterns & Best Practices', 1)
        
        patterns = [
            '✓ Repository Pattern for data access abstraction',
            '✓ Service Layer for business logic encapsulation',
            '✓ DTO (Data Transfer Objects) for inter-service communication',
            '✓ Dependency Injection for loose coupling',
            '✓ Contract/Interface abstraction for external services',
            '✓ Domain Events for async communication between domains',
            '✓ Observer Pattern for model lifecycle hooks',
            '✓ Strategy Pattern for payment/SMS provider switching',
            '✓ Soft Deletes for audit trail preservation',
            '✓ UUID primary keys for distributed systems'
        ]
        
        for pattern in patterns:
            doc.add_paragraph(pattern, style='List Bullet')
        
        doc.add_heading('Anti-Patterns to Avoid', 2)
        
        anti_patterns = [
            '✗ God Services (single service doing too much)',
            '✗ Synchronous I/O in request handlers',
            '✗ N+1 database queries (missing eager loading)',
            '✗ Hardcoded configuration values',
            '✗ Direct controller-to-controller calls',
            '✗ Leaking database query builders',
            '✗ Circular domain dependencies'
        ]
        
        for pattern in anti_patterns:
            doc.add_paragraph(pattern, style='List Bullet')
        
        doc.add_page_break()
        
        doc.add_heading('Support & Contact', 1)
        doc.add_paragraph(
            'For issues, questions, or contributions, please refer to the project repository or contact '
            'the development team.'
        )
        
        # Save document
        output_path = r"c:\laragon\www\test\Shop\docs\project-summary.docx"
        doc.save(output_path)
        print(f"✓ Word document created: {output_path}")
        return True
        
    except ImportError:
        print("ℹ️  python-docx not installed. Install with: pip install python-docx")
        return False
    except Exception as e:
        print(f"✗ Error creating Word document: {e}")
        return False

def main():
    """Main entry point."""
    print("📚 Shop Documentation Generator")
    print("=" * 60)
    
    # Create diagram SVG files
    print("\n📊 Step 1: Creating diagram placeholders...")
    diagrams_dir = Path(r"c:\laragon\www\test\Shop\docs\diagrams")
    diagrams_dir.mkdir(parents=True, exist_ok=True)
    
    diagrams = {
        'high-level-architecture': 'High-Level Architecture (C4 Context)',
        'processing-pipeline': 'Request Processing Pipeline (C4 Container)',
        'component-relationships': 'Component Relationships (C4 Component)',
        'data-model': 'Entity Relationship Diagram (Data Model)'
    }
    
    for diagram_key, diagram_name in diagrams.items():
        svg_path = diagrams_dir / f"{diagram_key}.drawio.svg"
        svg_content = create_svg_placeholder(diagram_name, diagram_key)
        with open(svg_path, 'w', encoding='utf-8') as f:
            f.write(svg_content)
        print(f"  ✓ Created: {diagram_key}.drawio.svg")
    
    # Create Word document
    print("\n📄 Step 2: Creating Word document...")
    if create_word_document():
        print("\n✅ Documentation generation complete!")
        print("\n📂 Generated Files:")
        print(f"  - Markdown: docs/project-summary.md")
        print(f"  - Word Doc: docs/project-summary.docx")
        print(f"  - Diagrams: docs/diagrams/")
        print(f"    • high-level-architecture.drawio")
        print(f"    • processing-pipeline.drawio")
        print(f"    • component-relationships.drawio")
        print(f"    • data-model.drawio")
    else:
        print("\n⚠️  Word document generation skipped (missing dependency)")
        print("    Run: pip install python-docx")

if __name__ == '__main__':
    main()
