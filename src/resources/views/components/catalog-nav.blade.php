@php
    // Approved 5-tab bottom navigation (restaurant-ui §13). Cart/Orders/Account are
    // activated in their own phases (F/J/K); until then they render as disabled tabs
    // so the structure is correct without dead links.
    $tabs = [
        ['label' => __('catalog.nav.home'), 'route' => 'home'],
        ['label' => __('catalog.nav.categories'), 'route' => 'categories.index'],
        ['label' => __('catalog.nav.cart'), 'route' => null],
        ['label' => __('catalog.nav.orders'), 'route' => null],
        ['label' => __('catalog.nav.account'), 'route' => null],
    ];
@endphp

<div class="grid grid-cols-5">
    @foreach ($tabs as $tab)
        @php $active = $tab['route'] && request()->routeIs($tab['route']); @endphp
        @if ($tab['route'])
            <a href="{{ route($tab['route']) }}"
                @if ($active) aria-current="page" @endif
                class="flex flex-col items-center gap-1 py-2 text-xs font-semibold {{ $active ? 'text-primary' : 'text-muted' }}">
                {{ $tab['label'] }}
            </a>
        @else
            <span aria-disabled="true" class="flex flex-col items-center gap-1 py-2 text-xs font-semibold text-muted/50">
                {{ $tab['label'] }}
            </span>
        @endif
    @endforeach
</div>
