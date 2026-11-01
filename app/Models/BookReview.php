<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'central_item_id',
        'rating',
        'comment',
    ];

    /**
     * Get the user who left the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the central item (book) related to this review.
     */
    public function centralItem()
    {
        return $this->belongsTo(CentralItem::class);
    }
}
