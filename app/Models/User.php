<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

// use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'user_credential_id',
        'employee_id_no',
        'username',
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'password',
        'status',
        'home_address',
        'role',
        'c_number',
    ];

        protected $hidden = [
        'password',
        'remember_token',
    ];

        protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    public function announcement()
    {
        return $this->hasMany(Announcement::class);
    }


    public function membership()
    {
        return $this->hasMany(Membership::class);
    }

    public function log()
    {
        return $this->hasMany(Log::class);
    }

    public function userCredential()
    {
        return $this->belongsTo(UserCredential::class, 'user_credential_id');
    }

    // public function ticket()
    // {
    //     return $this->hasMany(Ticket::class);
    // }


        // public function book()
    // {
    //     return $this->hasMany(Book::class);
    // }

        // public function readWalkIn()
    // {
    //     return $this->hasMany(ReadWalkIn::class);
    // }

    // public function client()
    // {
    //     return $this->hasMany(Client::class);
    // }

    // Start -  Check if user is online by looking for their presence in the cache
        public function isOnline(): bool
        {
            return \Illuminate\Support\Facades\Cache::has('user-is-online-' . $this->id);
        }
    // End -  Check if user is online by looking for their presence in the cache
}
