<?php

namespace App\Models;

use App\Observers\UserObserver;
use App\Traits\GeneratesTokens;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

use App\Notifications\ResetAppPassword;


class User extends Authenticatable implements HasMedia, HasAvatar
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use InteractsWithMedia;
    use GeneratesTokens;

    // ── GeneratesTokens Config ────────────────────────────────
    public function getTokenColumnName(): string { return 'user_token_keyid'; }
    public function getKeyIdColumnName(): string { return 'user_key_id'; }
    public function getKeyIdDigits(): int { return 8; }

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_key_id',
        'user_token_keyid',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'user_token_keyid',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relations ─────────────────────────────
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    // ── Boot ─────────────────────────────
    protected static function booted(): void
    {
        static::observe(UserObserver::class);
    }

    // ── Filament Avatar ─────────────────────────────
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile?->getFirstMediaUrl('avatar') ?: null;
    }

    public function sendPasswordResetNotification($token): void
    {
        dd('USER MODEL METHOD');
    }

   
}