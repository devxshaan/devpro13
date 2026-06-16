<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminProfile extends EditProfile
{
    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                // 1. Personal Information Section (Avatar + Profile details)
                Section::make('Personal Information')
                    ->relationship('profile')
                    ->schema([
                        // Avatar ko relationship ke andar rakho!
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label('Avatar')
                            ->avatar()
                            ->collection('avatar')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('first_name')->label('First Name'),
                            TextInput::make('last_name')->label('Last Name'),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('phone')->label('Phone')->tel(),
                            Select::make('gender')->label('Gender')->options([
                                'male'              => 'Male',
                                'female'            => 'Female',
                                'other'             => 'Other',
                                'prefer_not_to_say' => 'Prefer not to say',
                            ]),
                        ]),
                        Grid::make(2)->schema([
                            DatePicker::make('dob')->label('Date of Birth'),
                            TextInput::make('city')->label('City'),
                        ]),
                        Textarea::make('address')->label('Address')->rows(2),
                        Textarea::make('bio')->label('Bio')->rows(3),
                    ]),

                // 2. Account Section (User model)
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')->label('Name')->required(),
                        TextInput::make('email')->label('Email')->email()->required(),
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->nullable()
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => !empty($state)),
                    ]),
            ]);
    }
}