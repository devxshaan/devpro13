<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Left Column — Personal Info ────────────────
                Section::make('Personal Information')
                    ->columnSpan(1)
                    ->relationship('profile') // ✅ Yeh zaroori hai
                    ->schema([
                    SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Avatar')
                            ->collection('avatar')
                            ->disk('public')
                            ->directory('avatars')
                            ->avatar()
                            ->image()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('First Name'),

                                TextInput::make('last_name')
                                    ->label('Last Name'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel(),

                                Select::make('gender')
                                    ->label('Gender')
                                    ->options([
                                        'male'              => 'Male',
                                        'female'            => 'Female',
                                        'other'             => 'Other',
                                        'prefer_not_to_say' => 'Prefer not to say',
                                    ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('dob')
                                    ->label('Date of Birth')
                                    ->maxDate(now()->subYears(5)),

                                TextInput::make('city')
                                    ->label('City'),
                            ]),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(2),

                        Textarea::make('bio')
                            ->label('Bio')
                            ->rows(3),
                    ]),

                // ── Right Column — Account Settings ────────────
                Section::make('Account Settings')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('status')
                            ->label('Account Status')
                            ->options([
                                'active'   => '✅ Active',
                                'inactive' => '⏸ Inactive',
                                'banned'   => '🚫 Banned',
                                'pending'  => '⏳ Pending',
                            ])
                            ->default('pending')
                            ->required(),

                        Select::make('roles')
                            ->label('Assign Role')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->options(
                                \Spatie\Permission\Models\Role::where('name', '!=', config('roles.super_admin'))
                                    ->pluck('name', 'id')
                            )
                            ->preload(),

                        // ── Password — Only when filled ────────
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->helperText('Leave blank to keep current password')
                            ->nullable()
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => !empty($state)),
                    ]),
            ]);
    }
}