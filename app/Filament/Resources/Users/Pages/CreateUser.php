<?php
namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
    
        if (empty($data['name'])) {
            $data['name'] = ($data['profile']['first_name'] ?? 'User') . ' ' . ($data['profile']['last_name'] ?? '');
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        
        $this->record->profile()->create($this->data['profile'] ?? []);
    }
}