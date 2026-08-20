<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Customer\Models\User;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'item_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
