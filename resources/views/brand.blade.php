@php
    $light = $logos['light'] ?? null;
    $dark = $logos['dark'] ?? null;
@endphp

<a href="{{ filament()->getHomeUrl() }}" class="mrvls-brand">
    @if ($light && $dark)
        <img src="{{ $light }}" alt="{{ $alt }}" class="mrvls-brand-logo mrvls-brand-logo--on-light" />
        <img src="{{ $dark }}" alt="{{ $alt }}" class="mrvls-brand-logo mrvls-brand-logo--on-dark" />
    @elseif ($light || $dark)
        <img src="{{ $light ?? $dark }}" alt="{{ $alt }}" class="mrvls-brand-logo" />
    @endif
</a>
