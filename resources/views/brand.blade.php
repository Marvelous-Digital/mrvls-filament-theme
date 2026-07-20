@php
    $light = $logos['light'] ?? null;
    $dark = $logos['dark'] ?? null;
@endphp

<a href="{{ filament()->getHomeUrl() }}" class="fms-brand">
    @if ($light && $dark)
        <img src="{{ $light }}" alt="{{ $alt }}" class="fms-brand-logo fms-brand-logo--on-light" />
        <img src="{{ $dark }}" alt="{{ $alt }}" class="fms-brand-logo fms-brand-logo--on-dark" />
    @elseif ($light || $dark)
        <img src="{{ $light ?? $dark }}" alt="{{ $alt }}" class="fms-brand-logo" />
    @endif
</a>
