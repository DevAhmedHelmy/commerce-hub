{{-- Read-only inventory adjustment history (prompt 32 §16 / BR-016). Rendered inside a
     Filament modal, so it uses Filament's own utility classes. --}}
<div class="space-y-2 text-sm">
    @forelse ($adjustments as $adj)
        <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-2 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <span class="font-semibold">{{ __($adj->type->labelKey()) }}</span>
                <span class="tabular-nums text-gray-500" dir="ltr">{{ $adj->quantity_before }} → {{ $adj->quantity_after }}</span>
                <span class="tabular-nums {{ $adj->quantity_delta >= 0 ? 'text-success-600' : 'text-danger-600' }}" dir="ltr">
                    ({{ $adj->quantity_delta > 0 ? '+' : '' }}{{ $adj->quantity_delta }})
                </span>
            </div>
            <div class="text-xs text-gray-500">
                <span dir="ltr">{{ \App\Domain\Support\DateTimeFormatter::dateTime($adj->created_at) }}</span>
                @if ($adj->reason) · {{ $adj->reason }} @endif
                @if ($adj->performedBy) · {{ $adj->performedBy->name }} @endif
            </div>
        </div>
    @empty
        <p class="text-gray-500">{{ __('messages.states.empty_body') }}</p>
    @endforelse
</div>
