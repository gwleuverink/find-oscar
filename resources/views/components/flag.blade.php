@props(['code'])

{{--
    Inline SVG flags rather than emoji, which Windows refuses to render as
    flags at all and would leave a bare country code in the switcher.
    Drawn at a 3:2 ratio so they line up on a shared baseline.
--}}

@php
    $classes = 'inline-block h-3.5 w-5 shrink-0 rounded-xs shadow-sm ring-1 ring-black/10';
@endphp

@switch($code)

    @case('nl')
        <svg viewBox="0 0 9 6" class="{{ $classes }}" aria-hidden="true">
            <rect width="9" height="6" fill="#21468B"/>
            <rect width="9" height="4" fill="#fff"/>
            <rect width="9" height="2" fill="#AE1C28"/>
        </svg>
        @break

    @case('ru')
        <svg viewBox="0 0 9 6" class="{{ $classes }}" aria-hidden="true">
            <rect width="9" height="6" fill="#D52B1E"/>
            <rect width="9" height="4" fill="#0039A6"/>
            <rect width="9" height="2" fill="#fff"/>
        </svg>
        @break

    @case('tr')
        <svg viewBox="0 0 1200 800" class="{{ $classes }}" aria-hidden="true">
            <rect width="1200" height="800" fill="#E30A17"/>
            <circle cx="425" cy="400" r="200" fill="#fff"/>
            <circle cx="487.5" cy="400" r="160" fill="#E30A17"/>
            <polygon fill="#fff" points="916.5,400 847.4,377.5 847.4,304.9 804.7,363.7 735.6,341.2 778.3,400 735.6,458.8 804.7,436.3 847.4,495.1 847.4,422.5"/>
        </svg>
        @break

    @case('en')
        <svg viewBox="0 0 60 30" class="{{ $classes }}" aria-hidden="true">
            <clipPath id="flag-en-clip">
                <path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/>
            </clipPath>
            <rect width="60" height="30" fill="#012169"/>
            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
            <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#flag-en-clip)" stroke="#C8102E" stroke-width="4"/>
            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
            <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/>
        </svg>
        @break

@endswitch
