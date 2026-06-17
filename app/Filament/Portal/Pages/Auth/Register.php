<?php

namespace App\Filament\Portal\Pages\Auth;

use App\Mail\AppMail;
use App\Models\Profile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Filament\Schemas\Components\Grid;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255),
                    ]),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->telRegex('/^[+]?[0-9\s\-\(\)]{7,20}$/')
                    ->prefix('+91')
                    ->placeholder('9876543210')
                    ->minLength(7)
                    ->maxLength(20)
                    ->nullable(),

                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male'              => 'Male',
                        'female'            => 'Female',
                        'other'             => 'Other',
                        'prefer_not_to_say' => 'Prefer not to say',
                    ])
                    ->nullable(),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $user = $this->getUserModel()::create([
            'name'     => $name,
            'email'    => $data['email'],
            'password' => $data['password'],
            'status'   => 'active',
        ]);

        $user = $user->fresh();

        
        $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name'  => $data['last_name'] ?? null,
                'gender'     => $data['gender'] ?? null,
                'phone'      => $data['phone'] ?? null,
            ]
        );
        
        $user = $user->fresh();
        $user->updateQuietly(['name' => $name]);

        try {
            Mail::to($user->email)->send(new AppMail(
                mailSubject: 'Welcome to ' . config('app.name') . '! 🎉',
                title: 'Welcome aboard, ' . ($user->profile->first_name.' '.$user->profile->last_name ?? $user->name) . '!',
                lines: [
                    'Your account has been created successfully.',
                    'You can now log in and explore your dashboard.',
                ],
                action: ['text' => 'Go to Dashboard', 'url' => url('/portal')],
                type: 'success',
                footer: 'If you did not create this account, please ignore this email.',
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        return $user;
    }
}