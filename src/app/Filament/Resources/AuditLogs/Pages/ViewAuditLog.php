<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    // Read-only: no edit action in the header (prompt 39 §3).
    protected function getHeaderActions(): array
    {
        return [];
    }
}
