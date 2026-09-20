<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @can('settings.update')
            <div class="mt-6">
                <x-filament::button type="submit">حفظ</x-filament::button>
            </div>
        @endcan
    </form>
</x-filament-panels::page>
