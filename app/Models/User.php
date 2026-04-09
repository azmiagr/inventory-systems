<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Constants\RoleConstants;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function createdTransaction()
    {
        return $this->hasMany(StockTransaction::class, 'created_by');
    }

    public function approvedTransaction()
    {
        return $this->hasMany(StockTransaction::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->role_id === RoleConstants::ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role_id === RoleConstants::STAFF;
    }

    public function isViewer(): bool
    {
        return $this->role_id === RoleConstants::VIEWER;
    }

    public function hasRole(string $role): bool
    {
        return $this->role_id === $role;
    }
}
