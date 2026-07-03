<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    /**
     * Semua user yang memiliki role ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Semua aplikasi yang di-assign ke role ini.
     * Sumber akses kedua (via tabel pivot app_role).
     */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'app_role');
    }
}