<x-layouts.app :title="__('catalog.categories_title')">
    <x-slot:header>
        <div class="flex items-center justify-between px-4 py-3">
            <span class="text-lg font-bold text-content">{{ __('catalog.categories_title') }}</span>
            <a href="{{ route('search') }}" class="text-sm font-semibold text-primary hover:text-primary-strong">{{ __('catalog.search_title') }}</a>
        </div>
    </x-slot:header>

    @if ($categories->isEmpty())
        <x-states.empty :message="__('catalog.no_categories')" />
    @else
        <ul class="divide-y divide-border rounded-[--radius-md] border border-border bg-surface">
            @foreach ($categories as $category)
                <li>
                    <a href="{{ route('categories.show', $category) }}" class="flex items-center justify-between px-4 py-3 text-content">
                        <span class="font-semibold" dir="auto">{{ $category->localized('name') }}</span>
                        <span class="text-muted" aria-hidden="true">‹</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
