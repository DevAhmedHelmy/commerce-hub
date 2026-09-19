<x-layouts.app :title="__('cart.title')">
    <x-slot:header>
        <div class="flex items-center gap-3 px-4 py-3">
            <span class="text-lg font-bold text-content">{{ __('cart.title') }}</span>
        </div>
    </x-slot:header>

    @if (session('status'))
        <div class="mb-3 rounded-[--radius-sm] bg-success/10 px-3 py-2 text-sm text-success">{{ session('status') }}</div>
    @endif

    @php $fmt = fn ($m) => \App\Domain\Support\MoneyFormatter::format($m); @endphp

    @if ($cart->isEmpty())
        <div class="py-16 text-center text-muted">{{ __('cart.empty') }}</div>
    @else
        <div class="space-y-3">
            @foreach ($cart->lines as $line)
                <div class="flex items-start gap-3 rounded-[--radius-md] border border-border p-3 {{ $line->available ? '' : 'opacity-70' }}">
                    <div class="flex h-16 w-16 flex-none items-center justify-center overflow-hidden rounded-[--radius-sm] bg-surface-muted">
                        @if ($line->unit->product?->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($line->unit->product->image_path) }}"
                                alt="{{ $line->unit->product->localized('name') }}" class="h-full w-full object-contain" loading="lazy">
                        @else
                            <span class="text-xl font-bold text-muted" aria-hidden="true">{{ mb_substr($line->unit->product?->localized('name') ?? '؟', 0, 1) }}</span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-content" dir="auto">{{ $line->unit->product?->localized('name') }}</p>
                        <p class="text-sm text-muted" dir="auto">{{ $line->unit->label() }}</p>
                        @unless ($line->available)
                            <p class="mt-1 text-sm font-semibold text-danger">{{ __('cart.unavailable') }}</p>
                        @endunless
                        <p class="mt-1 text-sm text-content" dir="ltr">{{ $fmt($line->price->applied) }} × {{ $line->item->quantity }} = <span class="font-bold">{{ $fmt($line->price->lineTotal) }}</span></p>

                        <div class="mt-2 flex items-center gap-2">
                            <form method="POST" action="{{ route('cart.items.update', $line->item) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $line->item->quantity }}" min="1"
                                    class="w-16 rounded-[--radius-sm] border border-border bg-surface px-2 py-1 text-center text-sm text-content" inputmode="numeric">
                                <button type="submit" class="rounded-[--radius-sm] border border-border px-2 py-1 text-xs text-content">تحديث</button>
                            </form>
                            <form method="POST" action="{{ route('cart.items.destroy', $line->item) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-[--radius-sm] px-2 py-1 text-xs font-semibold text-danger">{{ __('cart.remove') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 rounded-[--radius-md] border border-border p-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-muted">{{ __('cart.subtotal') }}</span>
                <span class="text-lg font-bold text-content" dir="ltr">{{ $fmt($cart->productSubtotal) }}</span>
            </div>

            <div class="mt-3">
                @if ($cart->meetsMinimum)
                    <p class="text-sm font-semibold text-success">{{ __('cart.min_met') }}</p>
                @else
                    <p class="text-sm text-warning">{{ __('cart.min_remaining', ['amount' => $fmt($cart->remainingToMinimum)]) }}</p>
                @endif
            </div>

            <p class="mt-2 text-xs text-muted">{{ __('cart.note_no_delivery') }}</p>

            @if (\Illuminate\Support\Facades\Route::has('checkout.delivery'))
                <a href="{{ route('checkout.delivery') }}"
                    @class(['mt-4 block w-full rounded-[--radius-sm] px-4 py-3 text-center text-base font-semibold', 'bg-primary text-inverse hover:bg-primary-strong' => $cart->meetsMinimum, 'pointer-events-none bg-surface-muted text-muted' => ! $cart->meetsMinimum])
                    @if (! $cart->meetsMinimum) aria-disabled="true" tabindex="-1" @endif>
                    {{ __('cart.checkout') }}
                </a>
            @endif
        </div>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
