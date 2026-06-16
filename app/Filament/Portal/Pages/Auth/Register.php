<?php

namespace App\Filament\Portal\Pages\Auth;

use App\Models\Profile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Grid;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── Row 1: First + Last Name ──────────────────
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

                // ── Phone ─────────────────────────────────────
                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()                        
                    ->telRegex('/^[+]?[0-9\s\-\(\)]{7,20}$/')  
                    ->prefix('+91')                 
                    ->placeholder('9876543210')
                    ->minLength(7)
                    ->maxLength(20)
                    ->nullable(),                    

                // ── Email ─────────────────────────────────────
                $this->getEmailFormComponent(),

                // ── Password ──────────────────────────────────
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

                // ── Gender ────────────────────────────────────
                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male'             => 'Male',
                        'female'           => 'Female',
                        'other'            => 'Other',
                        'prefer_not_to_say'=> 'Prefer not to say',
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
            'status'   => 'active'
        ]);

        $user = $user->fresh();
                
        $user->profile()->update([
            'first_name' => $data['first_name'] ?? null,
            'last_name'  => $data['last_name'] ?? null,
            'gender'     => $data['gender'] ?? null,
            'phone'      => $data['phone'] ?? null,
        ]);

        $user->updateQuietly(['name' => $name]);

        return $user;
    }
}