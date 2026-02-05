<?php

namespace App\Models;

use Cocur\Slugify\Slugify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, softDeletes;

    protected $connection = 'tickets_mysql';

    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    public function getEvent($slug)
    {
        return $this->with(['tickets' => function ($tickets) {
                    $tickets->with([
                           'reservedtickets' => function ($reservedtickets) {
                               $reservedtickets->where('expire_time', '>=', now());
                           }
                           ])->where('published', '=', 1)
                             ->where('store', '=', 1);
                    }])
                    ->where('slug', $slug)
                    ->first();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class)->withDefault();
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
