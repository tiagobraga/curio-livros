<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['central_item_id', 'user_id', 'metadata', 'status'])]
class SuggestedEdit extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the central item (book) this suggestion is for.
     */
    public function centralItem()
    {
        return $this->belongsTo(CentralItem::class);
    }

    /**
     * Get the user who suggested this edit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending suggestions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
