<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')
                ->label('Role Name')
                ->required()
                ->maxLength(255),

            CheckboxList::make('permissions')
                ->label('Permissions')
                ->relationship('permissions', 'name')
                ->options(
                    Permission::query()
                        ->pluck('name', 'id')
                        ->mapWithKeys(fn ($permission, $id) => [
                            $id => str($permission)
                                ->replace('.', ' → ')
                                ->title()
                                ->toString()
                        ])
                )
                ->searchable()
                ->columns(3)
                ->bulkToggleable()
                ->columnSpanFull(),

        ]);
    }
}