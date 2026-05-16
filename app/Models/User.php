<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $connection = 'tickets_mysql';

    protected $table = 'users';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'admin',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_users', 'user_id', 'organisation_id');
    }

    public function isScannerAdmin(): bool
    {
        if ((bool) ($this->admin ?? false)) {
            return true;
        }

        try {
            return method_exists($this, 'hasRole') && $this->hasRole('admin');
        } catch (Throwable) {
            return false;
        }
    }

    public function canAccessOrganisation(int $organisationId): bool
    {
        if ($this->isScannerAdmin()) {
            return true;
        }

        return OrganisationUser::query()
            ->where('user_id', $this->getKey())
            ->where('organisation_id', $organisationId)
            ->exists();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'admin' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'scan_count' => 'integer',
        ];
    }
}
