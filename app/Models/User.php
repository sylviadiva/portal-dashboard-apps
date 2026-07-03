<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'role_id',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Departemen tempat user ini berada.
     * Sumber akses #1 (implisit lewat departemen).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Role/jabatan user ini.
     * Sumber akses #2 (implisit lewat role).
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Aplikasi yang di-assign LANGSUNG ke user ini (specific access).
     * Sumber akses #3 — ini beda dengan "effective access" (gabungan 3 sumber),
     * relasi ini cuma mewakili 1 dari 3 sumber tsb.
     */
    public function specificApplications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'app_user');
    }
}