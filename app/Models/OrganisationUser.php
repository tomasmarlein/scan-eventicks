<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationUser extends Model
{
    use HasFactory;

    protected $connection = 'tickets_mysql';

    public $timestamps = false;

    public function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }

    public function organisation()
    {
        return $this->hasOne(Organisation::class, 'organisation_id');
    }
}
