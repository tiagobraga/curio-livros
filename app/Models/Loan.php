<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['local_copy_id', 'user_id', 'attendant_id', 'loan_date', 'due_date', 'return_date', 'status'])]
class Loan extends Model
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
            'loan_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
        ];
    }

    /**
     * Get the physical book copy that was loaned.
     */
    public function localCopy()
    {
        return $this->belongsTo(LocalCopy::class)->withTrashed();
    }

    /**
     * Get the user (borrower) who took the loan.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the attendant who processed this loan.
     */
    public function attendant()
    {
        return $this->belongsTo(User::class, 'attendant_id');
    }

    /**
     * Scope to only include active (unreturned) loans.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('return_date');
    }

    /**
     * Scope to only include late (overdue) loans.
     */
    public function scopeLate($query)
    {
        return $query->whereNull('return_date')->where('due_date', '<', now()->toDateString());
    }
}
