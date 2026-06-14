<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['central_item_id', 'library_id', 'call_number', 'shelf_location', 'status', 'quantity'])]
class LocalCopy extends Model
{
    use HasFactory;

    /**
     * Get the central bibliographic item metadata (Work/Instance).
     */
    public function centralItem()
    {
        return $this->belongsTo(CentralItem::class);
    }

    /**
     * Get the library branch this copy belongs to.
     */
    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Get the loans associated with this copy.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Scope to only include copies available for loan.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
