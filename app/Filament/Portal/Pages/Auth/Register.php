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

                Grid::make(3)
                ->schema([
                TextInput::make('country_code')
                ->label('Country Code')
                ->default('91')
                ->prefix('+')
                ->numeric()
                ->minLength(1)
                ->columnSpan(1)
                ->maxLength(4)
                ->required(),

                TextInput::make('phone')
                ->label('Phone Number')
                ->tel()
                ->inputMode('numeric')
                ->placeholder('412345678')
                ->regex('/^[0-9]{6,15}$/')
                ->minLength(6)
                ->maxLength(15)
                ->columnSpan(2)
                ->required(),
                ]),

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

        $cleanCountryCode = ltrim($data['country_code'], '+');
        $cleanPhone = ltrim($data['phone'], '0');
        $fullPhoneNumber = '+' . $cleanCountryCode . $cleanPhone;
        $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name'  => $data['last_name'] ?? null,
                'gender'     => $data['gender'] ?? null,
                'phone'      => $fullPhoneNumber ?? null,
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