<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'library_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the library this user belongs to.
     */
    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Get loans taken by this user (as borrower).
     */
    public function loans()
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    /**
     * Get loans processed by this user (as attendant).
     */
    public function issuedLoans()
    {
        return $this->hasMany(Loan::class, 'attendant_id');
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCurator(): bool
    {
        return $this->role === 'curator' || $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->role === 'admin';
    }

    public function isAttendant(): bool
    {
        return $this->role === 'attendant' || $this->role === 'manager' || $this->role === 'admin';
    }

    public function isLibraryUser(): bool
    {
        return $this->role === 'user';
    }
}
