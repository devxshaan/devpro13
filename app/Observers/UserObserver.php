<?php

namespace App\Observers;

use App\Events\StatusUpdated;
use App\Models\User;
use App\Services\Notify;


class UserObserver
{
    // ── User Register ─────────────────────────────────────────
    public function created(User $user): void
    {
        
        $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            [] 
        );

        $defaultRole = config('roles.default');
        if ($defaultRole && \Spatie\Permission\Models\Role::where('name', $defaultRole)->exists()) {
            if (!$user->hasRole($defaultRole)) {
                $user->assignRole($defaultRole);
            }
        }
        broadcast(new StatusUpdated($user, $user->id));
        Notify::toAdmins(
            "New user registered: {$user->name} ({$user->email})",
            'info',
            '/admin/users'
        );
    }

    
    public function updated(User $user): void
    {
        
        if ($user->wasChanged('status')) {
            $oldStatus = $user->getOriginal('status');
            $newStatus = $user->status;

            $messages = [
                'active'   => 'Your account has been activated successfully.',
                'inactive' => 'Your account has been deactivated.',
                'banned'   => 'Your account has been suspended. Please contact support.',
                'pending'  => 'Your account is currently under review.',
            ];

            $types = [
                'active'   => 'success',
                'inactive' => 'warning',
                'banned'   => 'danger',
                'pending'  => 'info',
            ];

            Notify::send(
                $user,
                $messages[$newStatus] ?? "Your account status has been updated to: {$newStatus}",
                $types[$newStatus] ?? 'info',
                '/portal/dashboard'
            );
        }
    }
}