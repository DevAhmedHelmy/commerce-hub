<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Domain\Ordering\InvalidStatusTransitionException;
use App\Domain\Ordering\OrderService;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

/**
 * Admin order view with one-click lifecycle actions (A04). All transitions/cancellation go through
 * OrderService (validated, audited, stock-restoring); the page holds no business logic.
 */
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        $order = $this->record;

        foreach ($order->status->forwardTransitions() as $next) {
            $actions[] = Action::make('to_'.$next->value)
                ->label(__($next->labelKey()))
                ->visible(fn (): bool => (bool) auth()->user()?->can('orders.update_status'))
                ->requiresConfirmation()
                ->action(function () use ($next): void {
                    app(OrderService::class)->transition($this->record, $next, 'admin');
                    Notification::make()->title('تم تحديث حالة الطلب')->success()->send();
                });
        }

        if ($order->status->isAdminCancellable()) {
            $actions[] = Action::make('cancel')
                ->label('إلغاء الطلب')->color('danger')
                ->visible(fn (): bool => (bool) auth()->user()?->can('orders.cancel'))
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        app(OrderService::class)->cancel($this->record, 'admin');
                        Notification::make()->title('تم إلغاء الطلب واسترجاع المخزون')->success()->send();
                    } catch (InvalidStatusTransitionException) {
                        Notification::make()->title('لا يمكن إلغاء هذا الطلب')->danger()->send();
                    }
                });
        }

        return $actions;
    }
}
