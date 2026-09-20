<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @can('landing.manage')
            <div class="mt-6">
                <x-filament::button type="submit">حفظ</x-filament::button>
                <x-filament::button tag="a" href="{{ url('/') }}" target="_blank" color="gray" outlined>
                    معاينة الصفحة
                </x-filament::button>
            </div>
        @endcan
    </form>
</x-filament-panels::page>
