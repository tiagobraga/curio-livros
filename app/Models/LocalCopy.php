<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['central_item_id', 'library_id', 'call_number', 'shelf_location', 'status', 'quantity', 'custom_fields_values'])]
class LocalCopy extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'custom_fields_values' => 'array',
    ];

    /**
     * Get the central bibliographic item metadata (Work/Instance).
     */
    public function centralItem()
    {
        return $this->belongsTo(CentralItem::class)->withTrashed();
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

    /**
     * Synchronize the copy status based on active loans and institutional availability.
     */
    public function syncStatus(): void
    {
        if ($this->status === 'unavailable') {
            return;
        }

        $activeLoansCount = $this->loans()->active()->count();
        if ($activeLoansCount >= $this->quantity) {
            $this->update(['status' => 'borrowed']);
        } else {
            $this->update(['status' => 'available']);
        }
    }
}
