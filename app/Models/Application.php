<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'url', 'category', 'icon', 'color'];

    /**
     * Departemen mana saja yang punya akses ke aplikasi ini.
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'app_department');
    }

    /**
     * Role mana saja yang punya akses ke aplikasi ini.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'app_role');
    }

    /**
     * User spesifik mana saja yang punya akses langsung ke aplikasi ini
     * (di luar akses dari departemen/role).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'app_user');
    }
}