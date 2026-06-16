<?php

namespace App\Services;

use App\Events\NotificationSent;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Collection;

class Notify
{
    public static function send(
        User|Collection|array $target,
        string $message,
        string $type = 'info',
        ?string $url = null
    ): void {
        $notification = new GeneralNotification($message, $type, $url);

        if ($target instanceof User) {
            $target->notify($notification);
            broadcast(new NotificationSent((int) $target->getKey(), $message, $type));
            return;
        }

        collect($target)->each(function ($user) use ($notification, $message, $type) {
            $user->notify($notification);
            broadcast(new NotificationSent((int) $user->getKey(), $message, $type));
        });
    }

    public static function toAdmins(
        string $message,
        string $type = 'info',
        ?string $url = null
    ): void {
        $admins = User::role(config('roles.super_admin'))->get();
        static::send($admins, $message, $type, $url);
    }

    public static function toAll(
        string $message,
        string $type = 'info',
        ?string $url = null
    ): void {
        $users = User::all();
        static::send($users, $message, $type, $url);
    }

    public static function toRole(
        string $role,
        string $message,
        string $type = 'info',
        ?string $url = null
    ): void {
        $users = User::role($role)->get();
        static::send($users, $message, $type, $url);
    }
}