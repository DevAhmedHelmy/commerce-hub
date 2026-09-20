<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Audit\AdminAuditService;
use App\Domain\Audit\AuditAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var list<string> */
    private array $rolesBefore = [];

    protected function beforeSave(): void
    {
        $this->rolesBefore = $this->record->roles()->pluck('name')->sort()->values()->all();
    }

    protected function afterSave(): void
    {
        $after = $this->record->roles()->pluck('name')->sort()->values()->all();

        if ($after !== $this->rolesBefore) {
            app(AdminAuditService::class)->record(
                AuditAction::UPDATED,
                $this->record,
                ['roles' => $this->rolesBefore],
                ['roles' => $after],
                ['change' => 'roles'],
            );
        }
    }
}
