<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservedTicket extends Model
{
    use HasFactory;

    protected $connection = 'tickets_mysql';

    public function ticket()
    {
        return $this->belongsTo(Ticket::class)->withDefault();
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->withDefault();
    }
}
