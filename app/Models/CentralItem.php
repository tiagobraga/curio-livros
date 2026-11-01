<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'isbn', 'item_type', 'metadata', 'share_code'])]
class CentralItem extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->share_code)) {
                $item->share_code = static::generateUniqueShareCode();
            }
        });
    }

    public static function generateUniqueShareCode(): string
    {
        do {
            $code = \Illuminate\Support\Str::random(6);
            $exists = static::where('share_code', $code)->exists();
        } while ($exists);

        return $code;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array', // Casts the JSONB column to a PHP array
        ];
    }

    /**
     * Get the local copies of this item at different library units.
     */
    public function localCopies()
    {
        return $this->hasMany(LocalCopy::class);
    }

    /**
     * Get the reviews for this book.
     */
    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }
}
