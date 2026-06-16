<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Profile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'dob',
        'phone',
        'gender',
        'bio',
        'city',
        'address',
        'is_phone_private',
        'is_dob_private',
        'is_address_private',
    ];

    protected $casts = [
        'is_phone_private'   => 'boolean',
        'is_dob_private'     => 'boolean',
        'is_address_private' => 'boolean',
        'dob'                => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(function (Profile $profile) {

            if ($profile->user) {
                $profile->user->update([
                    'name' => trim($profile->first_name . ' ' . $profile->last_name)
                ]);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->useDisk('public')
            ->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | FIXED METHOD
    |--------------------------------------------------------------------------
    | Bug: $this->profile does not exist (wrong relation usage)
    |--------------------------------------------------------------------------
    */

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: null;
    }
}