<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'Quản trị viên',
        self::ROLE_STAFF => 'Nhân viên',
        self::ROLE_CUSTOMER => 'Khách mua hàng',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'province', 'ward', 'address_line', 'google_id', 'facebook_id', 'avatar_path'];

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
    ];

    public function orders() { return $this->hasMany(Order::class); }
    public function addresses() { return $this->hasMany(UserAddress::class); }
    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
    public function isStaff(): bool { return $this->role === self::ROLE_STAFF; }
    public function canAccessAdmin(): bool { return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_STAFF], true); }
    public function roleLabel(): string { return self::ROLE_LABELS[$this->role] ?? 'Không xác định'; }
    public function sendPasswordResetNotification($token): void { $this->notify(new ResetPasswordNotification($token)); }
    public function avatarUrl(): ?string { return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null; }
}
