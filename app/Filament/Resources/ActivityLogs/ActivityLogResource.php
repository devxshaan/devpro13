<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ManageActivityLogs;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CircleStack;

    protected static ?string $recordTitleAttribute = 'description';
    

    protected static bool $canCreate = false;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('description')
        ->columns([
            TextColumn::make('created_at')
                ->label('When')
                ->dateTime()
                ->sortable(),

            TextColumn::make('log_name')
                ->label('Log')
                ->badge(),

            TextColumn::make('event')
                ->badge()
                ->color(fn (?string $state) => match ($state) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    default => 'gray',
                }),

            TextColumn::make('subject_type')
                ->label('Section')
                ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-'),

            TextColumn::make('causer.name')
                ->label('Done By')
                ->default('System'),

            TextColumn::make('description')
                ->searchable()
                ->wrap(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([
            //
        ])
        ->recordActions([
            Action::make('viewChanges')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->modalHeading('Activity Details')
                ->modalContent(function ($record) {
                    $changes = $record->attribute_changes;

                    // agar string hai to decode kar (just in case cast nahi laga)
                    if (is_string($changes)) {
                        $changes = json_decode($changes, true);
                    }

                    $old = $changes['old'] ?? [];
                    $new = $changes['attributes'] ?? [];

                    if (empty($new)) {
                        return new \Illuminate\Support\HtmlString('<p>No field-level changes recorded.</p>');
                    }

                    $html = '<div class="space-y-2">';
                    foreach ($new as $key => $newValue) {
                        $oldValue = $old[$key] ?? '—';
                        $html .= "<div><strong>{$key}:</strong> <span style='color:#f87171'>{$oldValue}</span> → <span style='color:#34d399'>{$newValue}</span></div>";
                    }
                    $html .= '</div>';

                    return new \Illuminate\Support\HtmlString($html);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),

            DeleteAction::make(),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getPages(): array
    {
        return [
            'index' => ManageActivityLogs::route('/'),
        ];
    }
}