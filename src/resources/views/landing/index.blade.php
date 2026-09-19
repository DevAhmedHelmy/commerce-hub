<x-layouts.public :title="__('landing.title')">
    <div class="mx-auto w-full max-w-5xl px-4">

        {{-- Header --}}
        <header class="flex items-center justify-between py-5">
            <span class="text-xl font-extrabold text-content">{{ $business['name'] ?: __('landing.brand') }}</span>
            <a href="{{ route('login') }}" class="rounded-[--radius-sm] bg-primary px-4 py-2 text-sm font-semibold text-inverse hover:bg-primary-strong">{{ __('landing.sign_in') }}</a>
        </header>

        {{-- Hero --}}
        <section class="rounded-[--radius-lg] p-8 text-center" style="background: var(--gradient-brand-blue);">
            <h1 class="text-3xl font-extrabold text-white">{{ __('landing.hero_title') }}</h1>
            <p class="mx-auto mt-3 max-w-2xl text-white/90">{{ __('landing.hero_subtitle') }}</p>
            <a href="{{ route('login') }}" class="mt-6 inline-block rounded-[--radius-sm] bg-white px-6 py-3 text-base font-bold text-primary-strong">{{ __('landing.cta') }}</a>
        </section>

        {{-- Categories --}}
        <section class="py-10">
            <h2 class="mb-4 text-xl font-bold text-content">{{ __('landing.categories') }}</h2>
            @if ($categories->isEmpty())
                <p class="text-muted">{{ __('landing.empty_categories') }}</p>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($categories as $category)
                        <div class="rounded-[--radius-md] border border-border p-4 text-center text-sm font-semibold text-content" dir="auto">
                            {{ $category->localized('name') }}
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Featured offers --}}
        <section class="py-4">
            <h2 class="mb-4 text-xl font-bold text-content">{{ __('landing.offers') }}</h2>
            @if ($featured->isEmpty())
                <p class="text-muted">{{ __('landing.empty_offers') }}</p>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($featured as $product)
                        <div class="overflow-hidden rounded-[--radius-md] border border-border">
                            <div class="flex aspect-square items-center justify-center bg-surface-muted">
                                @if ($product->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->localized('name') }}" class="h-full w-full object-contain" loading="lazy">
                                @else
                                    <span class="text-3xl font-bold text-muted" aria-hidden="true">{{ mb_substr($product->localized('name'), 0, 1) }}</span>
                                @endif
                            </div>
                            <p class="truncate p-2 text-sm font-semibold text-content" dir="auto">{{ $product->localized('name') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Benefits + steps --}}
        <section class="grid gap-4 py-10 md:grid-cols-3">
            @foreach (__('landing.benefits') as $benefit)
                <div class="rounded-[--radius-md] border border-border p-4 text-center text-sm text-content">{{ $benefit }}</div>
            @endforeach
        </section>

        {{-- Footer --}}
        <footer class="border-t border-border py-8 text-center text-sm text-muted">
            @if ($business['phone'])
                <p dir="ltr">{{ $business['phone'] }}</p>
            @endif
            <p class="mt-2">{{ $business['name'] ?: __('landing.brand') }}</p>
        </footer>
    </div>
</x-layouts.public>
