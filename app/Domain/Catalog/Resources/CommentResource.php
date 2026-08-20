<?php

namespace App\Domain\Catalog\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Catalog\Models\Comment */
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            // strip_tags defends against stored XSS if this comment is ever
            // rendered into HTML by a client that forgets to escape it.
            'comment'    => strip_tags($this->comment),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
