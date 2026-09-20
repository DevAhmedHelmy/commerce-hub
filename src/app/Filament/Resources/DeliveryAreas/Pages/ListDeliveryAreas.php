<?php

namespace App\Filament\Resources\DeliveryAreas\Pages;

use App\Filament\Resources\DeliveryAreas\DeliveryAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryAreas extends ListRecords
{
    protected static string $resource = DeliveryAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
