<?php

namespace App\Models;

use App\Modules\Core\Models\Tenant;
use App\Modules\Core\Traits\HasAuditLog;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;
    use HasAuditLog;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'avatar',
        'last_login_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedServiceOrders(): HasMany
    {
        return $this->hasMany(\App\Modules\FieldService\Models\ServiceOrder::class, 'assigned_to');
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn (string $word) => strtoupper($word[0]))
            ->take(2)
            ->implode('');
    }
}
