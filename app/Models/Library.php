<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'is_active', 'owner_id', 'description', 'address', 'is_public', 'share_code', 'phone', 'library_type', 'is_public_page', 'color_scheme', 'custom_logo_url', 'subtitle', 'custom_fields', 'call_number_format', 'call_number_counters'])]
class Library extends Model
{
    use HasFactory;

    protected $casts = [
        'is_active' => 'boolean',
        'is_public_page' => 'boolean',
        'custom_fields' => 'array',
        'call_number_format' => 'array',
        'call_number_counters' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($library) {
            if (empty($library->share_code)) {
                $library->share_code = static::generateUniqueShareCode();
            }
        });
    }

    public static function generateUniqueShareCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(6));
            $exists = static::where('share_code', $code)->exists()
                || \App\Models\CentralItem::where('share_code', $code)->exists();
        } while ($exists);

        return $code;
    }

    public static function getColorSchemes(): array
    {
        return [
            'emerald' => [
                'name' => 'Verde Floresta (Padrão)',
                'primary' => '#1E4D2B',
                'primary_hover' => '#2a6438',
                'light_text' => '#A7F3D0',
                'border' => '#276238',
            ],
            'navy_blue' => [
                'name' => 'Azul Marinho Clássico',
                'primary' => '#1B365D',
                'primary_hover' => '#284A78',
                'light_text' => '#93C5FD',
                'border' => '#234475',
            ],
            'royal_burgundy' => [
                'name' => 'Vinho Acadêmico',
                'primary' => '#581845',
                'primary_hover' => '#72225B',
                'light_text' => '#FBCFE8',
                'border' => '#6A1E54',
            ],
            'warm_amber' => [
                'name' => 'Terracota & Âmbar',
                'primary' => '#8B4513',
                'primary_hover' => '#A0522D',
                'light_text' => '#FDE68A',
                'border' => '#9E4E17',
            ],
            'slate_dark' => [
                'name' => 'Grafite & Onix',
                'primary' => '#1A202C',
                'primary_hover' => '#2D3748',
                'light_text' => '#E2E8F0',
                'border' => '#2D3748',
            ],
            'deep_violet' => [
                'name' => 'Violeta Imperial',
                'primary' => '#4A154B',
                'primary_hover' => '#611F69',
                'light_text' => '#DDD6FE',
                'border' => '#561A57',
            ],
        ];
    }

    public function getColorTheme(): array
    {
        $schemes = static::getColorSchemes();
        return $schemes[$this->color_scheme] ?? $schemes['emerald'];
    }

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

    /**
     * Get the configured custom fields array.
     */
    public function getCustomFields(): array
    {
        return is_array($this->custom_fields) ? $this->custom_fields : [];
    }

    /**
     * Get the configured call number format elements.
     */
    public function getCallNumberFormat(): array
    {
        return is_array($this->call_number_format) ? $this->call_number_format : [];
    }

    /**
     * Check if this library has an automated call number format.
     */
    public function hasCallNumberFormat(): bool
    {
        return !empty($this->call_number_format) && is_array($this->call_number_format);
    }

    /**
     * Get the lowest available positive integer for an auto-increment custom field in this library (gap-filling).
     */
    public function getLowestAvailableAutoIncrementValue(string $fieldKey, ?int $ignoreLocalCopyId = null): int
    {
        $query = $this->localCopies();
        if ($ignoreLocalCopyId) {
            $query->where('id', '!=', $ignoreLocalCopyId);
        }

        $copies = $query->get(['id', 'custom_fields_values']);
        $usedNumbers = [];

        foreach ($copies as $copy) {
            $values = is_array($copy->custom_fields_values) ? $copy->custom_fields_values : [];
            if (isset($values[$fieldKey]) && is_numeric($values[$fieldKey])) {
                $val = (int)$values[$fieldKey];
                if ($val > 0) {
                    $usedNumbers[$val] = true;
                }
            }
        }

        $num = 1;
        while (isset($usedNumbers[$num])) {
            $num++;
        }

        return $num;
    }

    /**
     * Get the next auto-increment value for a given field key (lowest available integer).
     */
    public function getNextAutoIncrementValue(string $fieldKey): int
    {
        return $this->getLowestAvailableAutoIncrementValue($fieldKey);
    }

    /**
     * Advance the auto-increment counter for a given field key if the used value is greater.
     */
    public function advanceAutoIncrementCounter(string $fieldKey, int $usedValue): void
    {
        $counters = is_array($this->call_number_counters) ? $this->call_number_counters : [];
        $current = (int)($counters[$fieldKey] ?? 0);
        if ($usedValue > $current) {
            $counters[$fieldKey] = $usedValue;
            $this->call_number_counters = $counters;
            $this->save();
        }
    }
}
