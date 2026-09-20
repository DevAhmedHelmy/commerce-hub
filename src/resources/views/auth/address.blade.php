<x-layouts.public :title="__('auth.screens.address_title')">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <h1 class="text-2xl font-bold text-content">{{ __('auth.screens.address_title') }}</h1>

        <form method="POST" action="{{ route('onboarding.address.save') }}" class="mt-6 space-y-4">
            @csrf
            @php
                $fields = [
                    ['address_line', true],
                    ['building', false],
                    ['floor', false],
                    ['unit', false],
                    ['landmark', false],
                ];
            @endphp
            @if (($areas ?? collect())->isNotEmpty())
                <div>
                    <label for="delivery_area_id" class="mb-1 block text-sm font-semibold text-content">{{ __('auth.fields.delivery_area') }}</label>
                    <select id="delivery_area_id" name="delivery_area_id"
                        class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-3 text-base text-content focus:border-focus focus:ring-2 focus:ring-focus">
                        <option value="">—</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('delivery_area_id', $address?->delivery_area_id) == $area->id)>{{ $area->localized('name') }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @foreach ($fields as [$name, $required])
                <div>
                    <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-content">{{ __('auth.fields.'.$name) }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $address?->{$name}) }}"
                        @if ($required) required @endif
                        class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-3 text-base text-content focus:border-focus focus:ring-2 focus:ring-focus @error($name) border-danger @enderror">
                    @error($name)
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div>
                <label for="delivery_notes" class="mb-1 block text-sm font-semibold text-content">{{ __('auth.fields.delivery_notes') }}</label>
                <textarea id="delivery_notes" name="delivery_notes" rows="3"
                    class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-3 text-base text-content focus:border-focus focus:ring-2 focus:ring-focus">{{ old('delivery_notes', $address?->delivery_notes) }}</textarea>
            </div>

            <button type="submit"
                class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">
                {{ __('auth.actions.continue') }}
            </button>
        </form>
    </div>
</x-layouts.public>
