<x-layouts.app :title="__('account.edit_address')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('account.edit_address') }}</span></div>
    </x-slot:header>

    <form method="POST" action="{{ route('account.address.update') }}" class="space-y-4">
        @csrf
        <div>
            <label for="delivery_area_id" class="mb-1 block text-sm font-semibold text-content">{{ __('account.area') }}</label>
            <select id="delivery_area_id" name="delivery_area_id" class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content">
                <option value="">—</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected(old('delivery_area_id', $address?->delivery_area_id) == $area->id)>{{ $area->localized('name') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="address_line" class="mb-1 block text-sm font-semibold text-content">{{ __('account.address') }}</label>
            <input type="text" id="address_line" name="address_line" value="{{ old('address_line', $address?->address_line) }}" required
                class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content" dir="auto">
            @error('address_line') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <input type="text" name="building" value="{{ old('building', $address?->building) }}" placeholder="{{ __('auth.fields.building') }}" class="rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content" dir="auto">
            <input type="text" name="floor" value="{{ old('floor', $address?->floor) }}" placeholder="{{ __('auth.fields.floor') }}" class="rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content" dir="auto">
        </div>
        <button type="submit" class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">{{ __('account.save') }}</button>
    </form>

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
