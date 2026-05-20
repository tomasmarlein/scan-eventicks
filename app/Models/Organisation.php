<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tickets_mysql';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_users', 'organisation_id', 'user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isScannerAdmin()) {
            return $query;
        }

        return $query->whereHas('users', fn (Builder $userQuery) => $userQuery->where('users.id', $user->id));
    }

    public function findBySlug($slug): ?self
    {
        return $this->where('slug', $slug)->first();
    }

    public function getAllOrganisations()
    {
        return $this->with('users')->paginate(14);
    }

    public function getOrganisation($id): ?self
    {
        return $this->with('users')->find($id);
    }

    public function getOrganisationByUser($userId)
    {
        return $this->with('users')
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->paginate(15);
    }
}
