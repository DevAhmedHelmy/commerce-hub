<x-layouts.app :title="__('catalog.home_title')">
    <x-slot:header>
        <div class="flex items-center justify-between px-4 py-3">
            <x-brand-logo class="h-8" />
            <a href="{{ route('search') }}" aria-label="{{ __('catalog.search_title') }}"
                class="rounded-full border border-border p-2 text-primary hover:text-primary-strong">
                <span aria-hidden="true">🔍</span>
            </a>
        </div>
    </x-slot:header>

    {{-- Welcome / brand strip --}}
    <section class="mb-5 overflow-hidden rounded-[--radius-lg] p-6 text-white" style="background: var(--gradient-brand-blue);">
        <p class="text-sm text-white/80">{{ __('catalog.home_title') }}</p>
        <h1 class="mt-1 text-2xl font-extrabold">{{ app(\App\Domain\Landing\LandingPageService::class)->companyName() }}</h1>
        <a href="{{ route('search') }}" class="mt-4 inline-block rounded-[--radius-sm] bg-white px-4 py-2 text-sm font-bold text-primary-strong">{{ __('catalog.search_placeholder') }}</a>
    </section>

    {{-- Categories --}}
    <section>
        <h2 class="text-lg font-bold text-content">{{ __('catalog.categories_title') }}</h2>
        @if ($categories->isEmpty())
            <x-states.empty :message="__('catalog.no_categories')" />
        @else
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}"
                        class="rounded-[--radius-md] border border-border bg-surface p-4 text-center shadow-[var(--shadow-card)] transition hover:border-border-strong">
                        <span class="text-sm font-semibold text-content" dir="auto">{{ $category->localized('name') }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Featured offers (active-offer products only) --}}
    @if ($featured->isNotEmpty())
        <section class="mt-6">
            <h2 class="text-lg font-bold text-content">{{ __('catalog.offers') }}</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($featured as $product)
                    <a href="{{ route('products.show', $product) }}"
                        class="overflow-hidden rounded-[--radius-md] border border-border bg-surface shadow-[var(--shadow-card)] transition hover:border-border-strong">
                        <div class="flex aspect-square items-center justify-center bg-surface-muted">
                            @if ($product->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}"
                                    alt="{{ $product->localized('name') }}" class="h-full w-full object-contain" loading="lazy">
                            @else
                                <span class="text-3xl font-bold text-muted" aria-hidden="true">{{ mb_substr($product->localized('name'), 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="p-2">
                            <p class="truncate text-sm font-semibold text-content" dir="auto">{{ $product->localized('name') }}</p>
                            <span class="mt-1 inline-block rounded-[--radius-sm] bg-success/10 px-2 py-0.5 text-xs font-semibold text-success">{{ __('catalog.offer') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
