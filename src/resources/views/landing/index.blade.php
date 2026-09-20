@php
    // Admin-controlled chrome with safe fallbacks to translations.
    $siteTitle = $settings->localized('site_title') ?: __('landing.brand');
    $heroTitle = $settings->localized('hero_title') ?: __('landing.hero_title');
    $heroSubtitle = $settings->localized('hero_subtitle') ?: __('landing.hero_subtitle');
    $ctaLabel = $settings->localized('primary_cta_label') ?: __('landing.cta');
    $heroImage = $settings->hero_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->hero_image_path)
        : null;
    $safe = fn (?string $u) => $u && \Illuminate\Support\Str::startsWith($u, ['http://', 'https://']) ? $u : null;
@endphp

<x-layouts.public :title="$siteTitle">
    <div class="mx-auto w-full max-w-5xl px-4">

        {{-- Header --}}
        <header class="flex items-center justify-between py-5">
            <x-brand-logo class="h-9" />
            <a href="{{ route('login') }}" class="rounded-[--radius-sm] border border-border px-4 py-2 text-sm font-semibold text-content">{{ __('landing.sign_in') }}</a>
        </header>

        @foreach ($sections as $section)
            @switch($section->key)

                @case('hero')
                    <section class="overflow-hidden rounded-[--radius-lg] p-8 text-center" style="background: var(--gradient-brand-blue);">
                        @if ($heroImage)
                            <img src="{{ $heroImage }}" alt="{{ $siteTitle }}" class="mx-auto mb-4 max-h-40 w-auto object-contain">
                        @endif
                        <h1 class="text-3xl font-extrabold text-white" dir="auto">{{ $heroTitle }}</h1>
                        <p class="mx-auto mt-3 max-w-2xl text-white/90" dir="auto">{{ $heroSubtitle }}</p>
                        <a href="{{ route('landing.start') }}" class="mt-6 inline-block rounded-[--radius-sm] bg-white px-6 py-3 text-base font-bold text-primary-strong">{{ $ctaLabel }}</a>
                    </section>
                    @break

                @case('categories')
                    <section class="py-10">
                        <h2 class="mb-4 text-xl font-bold text-content" dir="auto">{{ $section->localized('title') ?: __('landing.categories') }}</h2>
                        @if ($categories->isEmpty())
                            <p class="text-muted">{{ __('landing.empty_categories') }}</p>
                        @else
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                @foreach ($categories as $category)
                                    <a href="{{ route('login') }}" class="rounded-[--radius-md] border border-border p-4 text-center text-sm font-semibold text-content" dir="auto">{{ $category->localized('name') }}</a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                    @break

                @case('featured_products')
                    <section class="py-4">
                        <h2 class="mb-4 text-xl font-bold text-content" dir="auto">{{ $section->localized('title') ?: __('landing.offers') }}</h2>
                        @if ($featured->isEmpty())
                            <p class="text-muted">{{ __('landing.empty_offers') }}</p>
                        @else
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                @foreach ($featured as $product)
                                    <a href="{{ route('login') }}" class="overflow-hidden rounded-[--radius-md] border border-border">
                                        <div class="flex aspect-square items-center justify-center bg-surface-muted">
                                            @if ($product->image_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->localized('name') }}" class="h-full w-full object-contain" loading="lazy">
                                            @else
                                                <span class="text-3xl font-bold text-muted" aria-hidden="true">{{ mb_substr($product->localized('name'), 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <p class="truncate p-2 text-sm font-semibold text-content" dir="auto">{{ $product->localized('name') }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>
                    @break

                @case('contact')
                    <section class="py-10">
                        <h2 class="mb-4 text-xl font-bold text-content" dir="auto">{{ $section->localized('title') ?: 'تواصل معنا' }}</h2>
                        <div class="flex flex-wrap gap-4 text-sm text-content">
                            @if ($settings->phone)<span dir="ltr">{{ $settings->phone }}</span>@endif
                            @if ($settings->whatsapp_phone)<span dir="ltr">واتساب: {{ $settings->whatsapp_phone }}</span>@endif
                            @if ($settings->email)<span dir="ltr">{{ $settings->email }}</span>@endif
                        </div>
                        <div class="mt-3 flex flex-wrap gap-3 text-sm">
                            @if ($safe($settings->facebook_url))<a href="{{ $safe($settings->facebook_url) }}" class="text-primary" rel="noopener noreferrer" target="_blank">Facebook</a>@endif
                            @if ($safe($settings->instagram_url))<a href="{{ $safe($settings->instagram_url) }}" class="text-primary" rel="noopener noreferrer" target="_blank">Instagram</a>@endif
                            @if ($safe($settings->tiktok_url))<a href="{{ $safe($settings->tiktok_url) }}" class="text-primary" rel="noopener noreferrer" target="_blank">TikTok</a>@endif
                        </div>
                    </section>
                    @break

                @default
                    {{-- Marketing sections (features/about/how_it_works): rendered from section items. --}}
                    <section class="py-10">
                        @if ($section->localized('title'))
                            <h2 class="mb-4 text-xl font-bold text-content" dir="auto">{{ $section->localized('title') }}</h2>
                        @endif
                        @if ($section->items->isNotEmpty())
                            <div class="grid gap-4 md:grid-cols-3">
                                @foreach ($section->items as $item)
                                    <div class="rounded-[--radius-md] border border-border p-4 text-center">
                                        @if ($item->icon)<div class="mb-2 text-2xl" aria-hidden="true">{{ $item->icon }}</div>@endif
                                        <p class="font-semibold text-content" dir="auto">{{ $item->localized('title') }}</p>
                                        @if ($item->localized('description'))<p class="mt-1 text-sm text-muted" dir="auto">{{ $item->localized('description') }}</p>@endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($section->localized('content'))
                            <p class="text-content" dir="auto">{{ $section->localized('content') }}</p>
                        @endif
                    </section>
            @endswitch
        @endforeach

        <footer class="border-t border-border py-8 text-center text-sm text-muted">
            <p>{{ $siteTitle }}</p>
        </footer>
    </div>
</x-layouts.public>
