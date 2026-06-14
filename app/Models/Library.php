<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'is_active'])]
class Library extends Model
{
    use HasFactory;

    /**
     * Get the users associated with this library.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the local copies of books at this library.
     */
    public function localCopies()
    {
        return $this->hasMany(LocalCopy::class);
    }
}
