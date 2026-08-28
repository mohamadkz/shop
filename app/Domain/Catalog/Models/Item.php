<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\ItemStatus;
use App\Shared\Traits\HasUuid;
use App\Domain\Cart\Models\BasketItem;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;


class Item extends Model
{
    use HasFactory, SoftDeletes, HasUuid, Searchable;
    
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'status',
    ];

    protected $casts = [
        'status' => ItemStatus::class,
        'price'  => 'decimal:2',
        'stock'  => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function basketItems()
    {
        return $this->hasMany(BasketItem::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => number_format((float) $this->price));
    }

    public function scopeActive($query)
    {
        return $query->where('status', ItemStatus::Active);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function hasSufficientStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }

    public function searchableAs(): string
    {
        return 'catalog_items';
    }

    /**
     * The public catalog fields persisted in Elasticsearch.
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'status' => $this->status->value,
        ];
    }

    /**
     * Draft and archived items must not appear in the public search index.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === ItemStatus::Active;
    }

    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }
}
