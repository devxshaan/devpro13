<?php

namespace App\Observers;

use App\Models\Profile;

class ProfileObserver
{

    public function saved(Profile $profile): void
    {
        if ($profile->wasChanged(['first_name', 'last_name']) || $profile->wasRecentlyCreated) {
            $firstName = $profile->first_name;
            $lastName  = $profile->last_name;

            $name = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

            if (!empty($name)) {
                $profile->user()->update(['name' => $name]);
            }
        }
    }
    
    public function updated(Profile $profile): void
    {
        
        if ($profile->wasChanged(['first_name', 'last_name'])) {
            $firstName = $profile->first_name;
            $lastName  = $profile->last_name;
            
            if ($firstName && $lastName) {
                $name = $firstName . ' ' . $lastName;
            } elseif ($firstName) {
                $name = $firstName;
            } elseif ($lastName) {
                $name = $lastName;
            } else {
                return; 
            }
            $profile->user()->update(['name' => $name]);
        }
    }
}