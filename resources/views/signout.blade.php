@auth
    <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="mrvls-signout-ctn">
        @csrf
        <button type="submit" class="mrvls-signout">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10 17l-5-5 5-5M4 12h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>{{ $label }}</span>
        </button>
    </form>
@endauth
