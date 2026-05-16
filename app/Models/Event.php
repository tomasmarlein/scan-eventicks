<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tickets_mysql';

    public function scopeForOrganisation(Builder $query, Organisation|int $organisation): Builder
    {
        $organisationId = $organisation instanceof Organisation ? $organisation->id : $organisation;

        return $query->where('organisation_id', $organisationId);
    }

    public function findBySlug($slug): ?self
    {
        return $this->where('slug', $slug)->first();
    }

    public function getEvent($slug): ?self
    {
        return $this->with(['tickets' => function ($tickets) {
            $tickets->with([
                'reservedtickets' => function ($reservedtickets) {
                    $reservedtickets->where('expire_time', '>=', now());
                },
            ])->where('published', '=', 1)
                ->where('store', '=', 1);
        }])
            ->where('slug', $slug)
            ->first();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class)->withDefault();
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }
}
