<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $connection = 'tickets_mysql';

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->withDefault();
    }

    public function orderlines()
    {
        return $this->hasMany(Orderline::class);
    }

    public function reservedtickets()
    {
        return $this->hasMany(ReservedTicket::class);
    }
}
