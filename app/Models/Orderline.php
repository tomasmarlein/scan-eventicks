<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class Orderline extends Model
{
    use HasFactory;

    protected $connection = 'tickets_mysql';

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->withDefault();
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class)->withDefault();
    }

    public function blockOrderline($orderline)
    {
        $orderline->blocked = true;
        $orderline->save();
    }

    public function unblockOrderline($orderline)
    {
        $orderline->blocked = false;
        $orderline->save();
    }
}
