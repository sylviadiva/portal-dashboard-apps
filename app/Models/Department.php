<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    /**
     * Semua user yang berada di departemen ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Semua aplikasi yang di-assign ke departemen ini.
     * Ini salah satu dari 3 sumber akses (via tabel pivot app_department).
     */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'app_department');
    }
}