<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCredential extends Model
{
        protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'address',
        'c_number',
        'organization_office',
        'office_address',
        'has_account',
    ];

    public function readSessions()
    {
        return $this->hasMany(ReadSession::class);
    }

    public function clientRemarks()
    {
        return $this->hasMany(ClientRemarks::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'user_credentials_id');
    }

    public function visit()
    {
        return $this->hasMany(Visit::class);
    }
}
