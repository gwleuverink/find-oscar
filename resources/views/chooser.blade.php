@php
    $default = config('oscar.default_locale');
@endphp
<!DOCTYPE html>
<html lang="{{ $default }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $case->fullName() }}</title>
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="{{ $case->url($default) }}">
    @foreach ($case->locales() as $code)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $case->url($code) }}">
    @endforeach
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
               display: grid; place-items: center; min-height: 100vh; background: #f8fafc; color: #0f172a; }
        main { text-align: center; padding: 2rem 1.5rem; max-width: 26rem; }
        img { width: 8rem; height: 8rem; border-radius: 9999px; object-fit: cover; object-position: center top; }
        h1 { font-size: 1.25rem; margin: 1rem 0 0.25rem; }
        p { margin: 0 0 1.5rem; color: #475569; }
        ul { list-style: none; padding: 0; margin: 0; display: grid; gap: 0.5rem; }
        a { display: block; padding: 0.85rem 1rem; border-radius: 0.5rem; background: #0f172a;
            color: #fff; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <main>
        <img src="/{{ $case->get('og_image') }}" alt="{{ $case->fullName() }}">
        <h1>{{ $case->fullName() }}</h1>
        <p>{{ __('site.hero.eyebrow', [], $default) }}</p>
        <ul>
            @foreach ($case->locales() as $code)
                <li><a href="/{{ $code }}">{{ __('site.locale_name', [], $code) }}</a></li>
            @endforeach
        </ul>
    </main>

    <script>
        // Send visitors straight to their own language when we publish one,
        // but leave the list behind for anyone whose browser reports
        // something we do not translate.
        const available = @json($case->locales());
        const preferred = (navigator.languages || [navigator.language || ''])
            .map((tag) => tag.slice(0, 2).toLowerCase())
            .find((tag) => available.includes(tag));

        if (preferred) {
            window.location.replace('/' + preferred + '/');
        }
    </script>
</body>
</html>
