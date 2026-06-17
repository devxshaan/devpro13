<?php

namespace App\Notifications;

use Filament\Auth\Notifications\ResetPassword as FilamentResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetAppPassword extends FilamentResetPassword
{
    public function toMail($notifiable): MailMessage
    {

    
        return (new MailMessage)
        ->subject('Reset Your Password')
        ->view('emails.password-reset', [
            'title' => 'Reset Your Password',
            'url'   => $this->url, // Filament base class se aa raha hai
            'type'  => 'info',
            'lines' => ['You requested a password reset for your gym account.'],
        ]);
    }
}