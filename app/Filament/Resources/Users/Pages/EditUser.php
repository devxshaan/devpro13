<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. Profile update karo
        if (isset($data['profile'])) {
            $record->profile()->updateOrCreate(
                ['user_id' => $record->id], 
                $data['profile']
            );
        }
        
        // 2. User ka main record update karo (except profile)
        // 'avatar' ko yahan se hatane ki jarurat nahi hai, kyunki spatie-plugin khud handle karega
        $record->update(collect($data)->except(['profile'])->toArray());

        return $record;
    }
}