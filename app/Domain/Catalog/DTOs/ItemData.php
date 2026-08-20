<?php

namespace App\Domain\Catalog\DTOs;

use App\Domain\Catalog\Enums\ItemStatus;

/**
 * Immutable input DTO for creating/updating an Item. Hydrated from a
 * FormRequest's validated() array — never from raw request input — so
 * mass assignment is impossible even if the Model's $fillable changes.
 */
final class ItemData
{
    public function __construct(
        public readonly int $categoryId,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly float $price,
        public readonly int $stock,
        public readonly ?string $image,
        public readonly ItemStatus $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: (int) $data['category_id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            price: (float) $data['price'],
            stock: (int) $data['stock'],
            image: $data['image'] ?? null,
            status: ItemStatus::from($data['status'] ?? 'draft'),
        );
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'image'       => $this->image,
            'status'      => $this->status,
        ];
    }
}
