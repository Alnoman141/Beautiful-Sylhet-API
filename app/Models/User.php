<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected $appends = ['name'];
    protected function Name(): Attribute
    {
        return Attribute::make(
            get: fn() => Str::trim($this->first_name . ' ' . $this->last_name),
        );
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn($value) => file_exists($this->attributes['avatar']) ? app('url')->to('/') . '/' .$this->attributes['avatar'] :
                'https://ui-avatars.com/api/?name=' . Str::slug($this->first_name . ' ' . $this->last_name) .
                '&size=256&color=fff&rounded=true&bold=true&background=0D8ABC',
        );
    }
}
