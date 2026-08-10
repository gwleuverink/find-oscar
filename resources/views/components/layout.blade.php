@props([
    'case',
    'locale',
    'title',
    'description',
])
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $case->ogImageUrl() }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ $case->url($locale) }}">
    <meta property="og:locale" content="{{ $locale }}">
    {{-- X falls back to the og: tags, but naming the image explicitly takes
         one variable out of a card that has already failed once. --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $case->ogImageUrl() }}">
    <meta name="twitter:image:alt" content="{{ $case->fullName() }}">

    @foreach ($case->locales() as $alternate)
        <link rel="alternate" hreflang="{{ $alternate }}" href="{{ $case->url($alternate) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $case->url(config('oscar.default_locale')) }}">
    <link rel="canonical" href="{{ $case->url($locale) }}">

    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-slate-900 antialiased">
    {{ $slot }}

    <script>
        // Keep the "day N" counter honest on a statically exported page,
        // which would otherwise freeze at whatever the build date
        // happened to be. Falls back to the rendered value.
        document.querySelectorAll('[data-since]').forEach((el) => {
            const start = Date.parse(el.dataset.since + 'T00:00:00Z');
            const today = Date.parse(new Date().toISOString().slice(0, 10) + 'T00:00:00Z');
            const days = Math.max(0, Math.round((today - start) / 86400000));

            el.textContent = el.dataset.template.replace(':count', days);
        });

        document.querySelectorAll('[data-copy]').forEach((el) => {
            el.addEventListener('click', async () => {
                await navigator.clipboard.writeText(window.location.href);
                const original = el.textContent;
                el.textContent = el.dataset.copied;
                setTimeout(() => (el.textContent = original), 2000);
            });
        });

        document.querySelectorAll('[data-print]').forEach((el) => {
            el.addEventListener('click', () => window.print());
        });

        // Guard the emergency number behind a confirmation. If the browser
        // has no dialog support the listener never binds, and the link
        // dials exactly as it would have done anyway.
        const emergency = document.getElementById('emergency-confirm');

        if (emergency?.showModal) {
            document.querySelectorAll('[data-confirm-emergency]').forEach((el) => {
                el.addEventListener('click', (event) => {
                    event.preventDefault();
                    emergency.showModal();
                });
            });

            emergency.querySelector('[data-confirm-cancel]')
                ?.addEventListener('click', () => emergency.close());

            // Clicking the backdrop falls on the dialog itself, never a child.
            emergency.addEventListener('click', (event) => {
                if (event.target === emergency) {
                    emergency.close();
                }
            });
        }
    </script>
</body>
</html>
