<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'sso-test';

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     dd(Cookie::get('laravel_session'));
    //     // Check if the user is an admin or authenticated via SSO
    //     return $this->isSSOAuthenticated();
    // }

    // /**
    //  * Check if the user is authenticated via SSO.
    //  *
    //  * @return bool
    //  */
    // public function isSSOAuthenticated(): bool
    // {
    //     // Add your logic here to check if the user is authenticated via SSO
    //     // This is just a placeholder. Replace it with the actual implementation.

    //     // For example, you might check for an SSO-specific session variable
    //     return session()->has('sso_authenticated') && session('sso_authenticated') === true;
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
