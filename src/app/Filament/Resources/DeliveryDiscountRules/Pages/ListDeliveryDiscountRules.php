<?php

namespace App\Filament\Resources\DeliveryDiscountRules\Pages;

use App\Filament\Resources\DeliveryDiscountRules\DeliveryDiscountRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryDiscountRules extends ListRecords
{
    protected static string $resource = DeliveryDiscountRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
