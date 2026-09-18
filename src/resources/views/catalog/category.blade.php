<x-layouts.app :title="$category->localized('name')">
    <x-slot:header>
        <div class="flex items-center gap-3 px-4 py-3">
            <a href="{{ route('categories.index') }}" class="text-muted" aria-label="{{ __('messages.actions.back') }}">›</a>
            <span class="text-lg font-bold text-content" dir="auto">{{ $category->localized('name') }}</span>
        </div>
    </x-slot:header>

    @if ($products->isEmpty())
        <x-states.empty :message="__('catalog.no_products')" />
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
