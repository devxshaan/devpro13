<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use App\Models\Setting;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['currency'] = Setting::get('default_currency', 'USD');
        return $data;
    }
}
