{{-- Light / Dark / System switcher (prompt 48 §6). Persists to localStorage; no DB, no flash
     (the layout head applies data-theme before styles). --}}
<div x-data="{
        theme: (function () { try { return localStorage.getItem('theme') || 'system'; } catch (e) { return 'system'; } })(),
        set(t) {
            this.theme = t;
            try { localStorage.setItem('theme', t); } catch (e) {}
            document.documentElement.setAttribute('data-theme', t);
            window.dispatchEvent(new Event('themechange'));
        }
     }"
     class="inline-flex items-center gap-1 rounded-[--radius-sm] border border-border p-1 text-sm"
     role="group" aria-label="{{ __('theme.title') }}">
    @foreach (['light' => __('theme.light'), 'dark' => __('theme.dark'), 'system' => __('theme.system')] as $value => $label)
        <button type="button" @click="set('{{ $value }}')"
            :class="theme === '{{ $value }}' ? 'bg-primary text-inverse' : 'text-muted hover:text-content'"
            class="rounded-[--radius-sm] px-3 py-1 font-semibold transition">{{ $label }}</button>
    @endforeach
</div>
