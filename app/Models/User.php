<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens,SoftDeletes,HasRoles,LogsActivity;

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
        'email_verified_at',
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

    protected function firstName(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::trim($value),
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::trim($value),
        );
    }
    //TODO @fa 2024-12-04 email
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::trim(Str::lower($value)),
        );
    }
    //TODO @fa 2024-12-04 phone
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Str::trim($value),
        );
    }
    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn() => config('filesystems.default') === 's3'
                ? (!empty($this->attributes['avatar']) && Storage::disk('s3')->exists($this->attributes['avatar']))
                    ? Storage::disk('s3')->url($this->attributes['avatar'])
                    : 'https://ui-avatars.com/api/?name=' . urlencode(Str::slug($this->first_name . ' ' . $this->last_name)) .'&size=256&color=fff&rounded=true&bold=true&background=0D8ABC'
                : (file_exists(public_path($this->attributes['avatar']))
                    ? asset($this->attributes['avatar'])
                    : 'https://ui-avatars.com/api/?name=' . urlencode(Str::slug($this->first_name . ' ' . $this->last_name)) .'&size=256&color=fff&rounded=true&bold=true&background=0D8ABC')
        );
    }
    //TODO @fa 2025-02-24 info
    public function info(): HasOne
    {
        return $this->hasOne(Info::class,'user_id');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logOnly(['first_name','last_name','phone','email'])
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at','password','remember_token','avatar','email_verified_at'])
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user() ? auth()->user()->name : 'Guest';
                $user = $this->name ?? 'User';
                if ($eventName === 'created') {
                    return "{$userName} created a new user: {$user}.";
                } elseif ($eventName === 'updated') {
                    return "{$userName} updated the user: {$user}.";
                } elseif ($eventName === 'deleted') {
                    return "{$userName} deleted the user: {$user}.";
                }
                return "{$userName} performed an action on the user.";
            });
    }
}
