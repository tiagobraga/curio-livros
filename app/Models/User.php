<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'library_id'])]
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
     * Get the libraries this user belongs to.
     */
    public function libraries()
    {
        return $this->belongsToMany(Library::class, 'library_user')->withPivot('role')->withTimestamps();
    }

    /**
     * Helper to get the first library for backward compatibility.
     */
    public function getLibraryAttribute()
    {
        return $this->libraries()->first();
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
        return in_array($this->role, ['admin', 'manager']) || $this->libraries()->where('library_user.role', 'manager')->exists();
    }

    public function isAttendant(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'attendant']) || $this->libraries()->whereIn('library_user.role', ['manager', 'attendant'])->exists();
    }

    public function isLibraryUser(): bool
    {
        return !$this->isCurator() && !$this->isAttendant();
    }

    /**
     * Check if the current user can view/edit a target user based on role hierarchy.
     */
    public function canEditUser(User $target): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->role === 'curator') {
            return true;
        }
        if ($this->role === 'manager' || $this->isManager()) {
            if (!in_array($target->role, ['user', 'attendant'])) {
                return false;
            }
            $myLibraryIds = $this->getManagedLibraryIds();
            $targetLibraryIds = $target->libraries()->pluck('libraries.id')->toArray();
            return !empty(array_intersect($myLibraryIds, $targetLibraryIds));
        }
        if ($this->role === 'attendant' || $this->isAttendant()) {
            if ($target->role !== 'user') {
                return false;
            }
            $myLibraryIds = $this->getStaffLibraryIds();
            $targetLibraryIds = $target->libraries()->pluck('libraries.id')->toArray();
            return !empty(array_intersect($myLibraryIds, $targetLibraryIds));
        }
        return false;
    }

    /**
     * Get the libraries where this user acts specifically as manager (or all active if admin/curator).
     */
    public function getManagedLibraries()
    {
        if ($this->isAdmin() || $this->isCurator()) {
            return Library::where('is_active', true)->orderBy('name', 'asc')->get();
        }

        return $this->libraries()
            ->where('library_user.role', 'manager')
            ->where('libraries.is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get IDs of the libraries where this user acts specifically as manager (or all active if admin/curator).
     */
    public function getManagedLibraryIds(): array
    {
        if ($this->isAdmin() || $this->isCurator()) {
            return Library::where('is_active', true)->pluck('id')->toArray();
        }

        return $this->libraries()
            ->where('library_user.role', 'manager')
            ->where('libraries.is_active', true)
            ->pluck('libraries.id')
            ->toArray();
    }

    /**
     * Get the libraries where this user acts as manager or attendant (or all active if admin/curator).
     */
    public function getStaffLibraries()
    {
        if ($this->isAdmin() || $this->isCurator()) {
            return Library::where('is_active', true)->orderBy('name', 'asc')->get();
        }

        $userGlobalRole = $this->role;

        return $this->libraries()
            ->where(function ($q) use ($userGlobalRole) {
                $q->whereIn('library_user.role', ['manager', 'attendant']);
                if (in_array($userGlobalRole, ['manager', 'attendant'])) {
                    $q->orWhereNull('library_user.role');
                }
            })
            ->where('libraries.is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get IDs of the libraries where this user acts as manager or attendant (or all active if admin/curator).
     */
    public function getStaffLibraryIds(): array
    {
        if ($this->isAdmin() || $this->isCurator()) {
            return Library::where('is_active', true)->pluck('id')->toArray();
        }

        $userGlobalRole = $this->role;

        return $this->libraries()
            ->where(function ($q) use ($userGlobalRole) {
                $q->whereIn('library_user.role', ['manager', 'attendant']);
                if (in_array($userGlobalRole, ['manager', 'attendant'])) {
                    $q->orWhereNull('library_user.role');
                }
            })
            ->where('libraries.is_active', true)
            ->pluck('libraries.id')
            ->toArray();
    }

    // Library-specific role checks
    public function isManagerOf($libraryId): bool
    {
        if ($this->isAdmin() || $this->isCurator()) {
            return true;
        }
        $lib = $this->relationLoaded('libraries') 
            ? $this->libraries->firstWhere('id', $libraryId)
            : $this->libraries()->where('libraries.id', $libraryId)->first();

        if (!$lib) {
            return false;
        }
        return ($lib->pivot->role ?? null) === 'manager';
    }

    public function isAttendantOf($libraryId): bool
    {
        if ($this->isAdmin() || $this->isCurator()) {
            return true;
        }
        $lib = $this->relationLoaded('libraries') 
            ? $this->libraries->firstWhere('id', $libraryId)
            : $this->libraries()->where('libraries.id', $libraryId)->first();

        if (!$lib) {
            return false;
        }
        return in_array($lib->pivot->role ?? null, ['manager', 'attendant']);
    }

    public function isLibraryUserOf($libraryId): bool
    {
        if ($this->isCurator()) {
            return false;
        }
        $lib = $this->relationLoaded('libraries') 
            ? $this->libraries->firstWhere('id', $libraryId)
            : $this->libraries()->where('libraries.id', $libraryId)->first();

        if (!$lib) {
            return false;
        }
        return ($lib->pivot->role ?? null) === 'user';
    }

    public function getRoleInLibrary($libraryId): ?string
    {
        if ($this->isAdmin()) return 'admin';
        if ($this->isCurator()) return 'curator';
        $lib = $this->relationLoaded('libraries') 
            ? $this->libraries->firstWhere('id', $libraryId)
            : $this->libraries()->where('libraries.id', $libraryId)->first();

        if ($lib) {
            return $lib->pivot->role ?: $this->role;
        }
        return null;
    }

    /**
     * Get the reviews submitted by this user.
     */
    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }
}
