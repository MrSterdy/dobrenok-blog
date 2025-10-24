<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'credentials',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function postPublications(): HasMany
    {
        return $this->hasMany(PostPublication::class);
    }

    /**
     * Получить учетные данные в безопасном виде
     */
    public function getCredential(string $key): ?string
    {
        return $this->credentials['credentials'][$key] ?? null;
    }

    /**
     * Получить настройку
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings['settings'][$key] ?? $default;
    }
}
