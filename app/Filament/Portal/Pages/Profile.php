<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Concerns\HasPermissionBasedData;
use App\Models\Profile as ProfileModel;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPermissionBasedData;

    protected string $view = 'filament.portal.pages.profile';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?int $navigationSort = 4;
    protected static string|\UnitEnum|null $navigationGroup = 'Management';

    // ✅ Profile ke liye yeh 2 lines
    protected string $viewOwnPermission = 'profile.view';
    protected string $viewAnyPermission = ''; // Sirf apni profile

    public ?ProfileModel $record = null;
    public ?array $data = [];

    // ✅ Override shouldRegisterNavigation — sirf profile.view check karo
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('profile.view');
    }

    public function mount(): void
    {
        // ✅ Permission check
        abort_unless(auth()->user()->can('profile.view'), 403);

        $this->record = auth()->user()->profile()->firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $this->form->fill([
            'first_name' => $this->record->first_name,
            'last_name'  => $this->record->last_name,
            'phone'      => $this->record->phone,
            'gender'     => $this->record->gender,
            'dob'        => $this->record->dob?->format('Y-m-d'),
            'city'       => $this->record->city,
            'address'    => $this->record->address,
            'bio'        => $this->record->bio,
            'email'      => auth()->user()->email,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label('Profile Picture')
                    ->collection('avatar')
                    ->disk('public')
                    ->avatar()
                    ->image()
                    ->columnSpanFull(),

                TextInput::make('first_name')->label('First Name'),
                TextInput::make('last_name')->label('Last Name'),
                TextInput::make('email')->email()->required()->label('Email Address'),
                TextInput::make('phone')->tel()->label('Phone Number'),

                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male'              => 'Male',
                        'female'            => 'Female',
                        'other'             => 'Other',
                        'prefer_not_to_say' => 'Prefer not to say',
                    ]),

                DatePicker::make('dob')->label('Date of Birth'),
                TextInput::make('city')->label('City'),
                Textarea::make('address')->label('Address')->rows(2),
                Textarea::make('bio')->label('Bio')->rows(3),
            ])
            ->columns(2)
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        // ✅ Permission check on save
        abort_unless(auth()->user()->can('profile.edit'), 403);

        $data = $this->form->getState();

        // ✅ Email + profile update
        auth()->user()->update(['email' => $data['email']]);

        unset($data['email']); // users table ka field — profile mein nahi

        $this->record->update($data);

        // ✅ Avatar save — Spatie
        $this->form->saveRelationships();

        Notification::make()
            ->title('Profile updated successfully!')
            ->success()
            ->send();
    }
}