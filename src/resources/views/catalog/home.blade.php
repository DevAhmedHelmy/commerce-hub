<x-layouts.app :title="__('catalog.home_title')">
    <x-slot:header>
        <div class="flex items-center justify-between px-4 py-3">
            <span class="text-lg font-bold text-content">{{ __('messages.app_name') }}</span>
            <a href="{{ route('search') }}" class="text-sm font-semibold text-primary hover:text-primary-strong">{{ __('catalog.search_title') }}</a>
        </div>
    </x-slot:header>

    <h1 class="text-xl font-bold text-content">{{ __('catalog.categories_title') }}</h1>

    @if ($categories->isEmpty())
        <x-states.empty :message="__('catalog.no_categories')" />
    @else
        <div class="mt-4 grid grid-cols-2 gap-3">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                    class="rounded-[--radius-md] border border-border bg-surface p-4 text-center shadow-[var(--shadow-card)] transition hover:border-border-strong">
                    <span class="text-sm font-semibold text-content" dir="auto">{{ $category->localized('name') }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
