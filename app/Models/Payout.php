<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class Payout extends Model
{
    use HasFactory;

    protected $connection = 'tickets_mysql';

    public function event()
    {
        return $this->belongsTo(Event::class)->withDefault();
    }
}
