<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Akaunting\Money\Currency;
use Illuminate\Support\Facades\Cache;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Cog;
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'Application Settings';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.pages.settings';
    protected static string|\UnitEnum|null $navigationGroup = 'Management';

    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Schema $form): Schema
    {
        $groups = Setting::all()->groupBy('group');

        $tabs = $groups->map(function ($settings, $group) {
            return Tab::make(ucfirst($group))
                ->schema(
                    $settings->map(fn ($setting) => $this->resolveField($setting))->filter()->values()->toArray()
                );
        })->values()->toArray();

        return $form
            ->schema([
                Tabs::make('Settings')->tabs($tabs)->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function resolveField(Setting $setting): mixed
    {
        // Currency field — special handling
        if ($setting->key === 'default_currency') {
            $currencyOptions = collect(Currency::getCurrencies())
                ->mapWithKeys(fn($currency, $code) => [$code => "$code - {$currency['name']}"])
                ->toArray();

            return Select::make('default_currency')
                ->label($setting->label)
                ->helperText($setting->description)
                ->options($currencyOptions)
                ->searchable()
                ->live() // reactive
                ->afterStateUpdated(function ($state) {
                if ($state) {
                    try {
                        $symbol = (new Currency($state))->getSymbol();
                        $this->data['currency_symbol'] = $symbol;
                    } catch (\Exception $e) {
                        // invalid currency, ignore
                    }
                }
            });
        }

        $field = match ($setting->type) {
            'textarea' => Textarea::make($setting->key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->rows(3)
                ->columnSpanFull(),

            'select' => Select::make($setting->key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->options(is_array($setting->options)
                    ? $setting->options
                    : json_decode($setting->options, true) ?? []),

            'boolean' => Toggle::make($setting->key)
                ->label($setting->label)
                ->helperText($setting->description),

            'file' => FileUpload::make($setting->key)
                ->label($setting->label)
                ->helperText($setting->description)
                ->image()
                ->disk('public')
                ->directory('settings')
                ->columnSpanFull(),

            'color' => ColorPicker::make($setting->key)
                ->label($setting->label)
                ->helperText($setting->description),

            default => TextInput::make($setting->key)
                ->label($setting->label)
                ->helperText($setting->description),
        };

        return $field;
    }

    public function save(): void
    {
        $data = $this->form->getState();

        
        if (!empty($data['default_currency'])) {
            try {
                $symbol = (new Currency($data['default_currency']))->getSymbol();
                $data['currency_symbol'] = $symbol;
            } catch (\Exception $e) {
                // ignore
            }
        }

        foreach ($data as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        Cache::flush();

        Notification::make()
            ->title('Settings saved!')
            ->success()
            ->send();
    }
}