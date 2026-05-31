<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_super_admin', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_super_admin'    => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->is_super_admin) return true;
        if (!$this->is_active) return false;

        return $this->roles
            ->flatMap(fn($role) => $role->permissions)
            ->contains('slug', $slug);
    }

    public function allPermissions(): array
    {
        if ($this->is_super_admin) {
            return array_merge(...array_values(Permission::$all));
        }
        return $this->roles
            ->flatMap(fn($r) => $r->permissions)
            ->pluck('label', 'slug')
            ->toArray();
    }
}
