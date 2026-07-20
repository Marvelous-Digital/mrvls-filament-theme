@php
    $total = (int) ($pill['total'] ?? 0);
    $done = (int) ($pill['done'] ?? 0);
    $circumference = 2 * pi() * 8;
    $ratio = $total > 0 ? min(1, $done / $total) : 0;
    $dashOffset = $circumference * (1 - $ratio);
@endphp

<div class="mrvls-topbar-pill-slot">
    <a
        href="{{ $pill['href'] ?? '#' }}"
        class="mrvls-topbar-pill"
        @isset($pill['tooltip']) title="{{ $pill['tooltip'] }}" @endisset
        @if (! empty($pill['external'])) target="_blank" rel="noopener" @endif
    >
        @isset($pill['status'])
            <span class="mrvls-topbar-pill-status is-{{ $pill['status'] }}" aria-hidden="true"></span>
        @endisset

        <span class="mrvls-topbar-pill-title">{{ $pill['title'] ?? '' }}</span>

        @if (! empty($pill['badge']))
            <span class="mrvls-topbar-pill-badge">{{ $pill['badge'] }}</span>
        @endif

        @if ($total > 0)
            <span class="mrvls-topbar-pill-progress" role="img" aria-label="{{ $pill['tooltip'] ?? "{$done}/{$total}" }}">
                <svg viewBox="0 0 20 20" aria-hidden="true">
                    <circle class="mrvls-topbar-pill-ring-track" cx="10" cy="10" r="8" />
                    <circle
                        class="mrvls-topbar-pill-ring"
                        cx="10"
                        cy="10"
                        r="8"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $dashOffset }}"
                    />
                </svg>
                <span class="mrvls-topbar-pill-count">{{ $done }}/{{ $total }}</span>
            </span>
        @endif
    </a>
</div>
