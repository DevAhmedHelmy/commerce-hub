<x-layouts.app :title="__('catalog.search_title')">
    <x-slot:header>
        <form method="GET" action="{{ route('search') }}" class="flex items-center gap-2 px-4 py-3">
            <input name="q" type="search" inputmode="search" value="{{ $query }}"
                placeholder="{{ __('catalog.search_placeholder') }}" autofocus
                class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-2 text-content focus:border-focus focus:ring-2 focus:ring-focus">
            <button type="submit" class="text-sm font-semibold text-primary hover:text-primary-strong">{{ __('messages.actions.search') }}</button>
        </form>
    </x-slot:header>

    @if ($results === null)
        <x-states.empty :title="__('catalog.search_title')" :message="__('catalog.search_prompt')" />
    @elseif ($results->isEmpty())
        <x-states.empty :message="__('catalog.no_results')" />
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach ($results as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-6">{{ $results->links() }}</div>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
