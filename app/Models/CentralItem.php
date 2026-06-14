<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['title', 'isbn', 'item_type', 'metadata'])]
class CentralItem extends Model
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
}
