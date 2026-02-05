<?php

namespace App\Models;

use Cocur\Slugify\Slugify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use HasFactory, softDeletes;

    protected $connection = 'tickets_mysql';

    public function users()
    {
        return $this->belongsToMany(User::class, 'organisation_users', 'organisation_id', 'user_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function findBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    public function getAllOrganisations()
    {
        return $this->with('users')->paginate(14);
    }

    public function getOrganisation($id)
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
