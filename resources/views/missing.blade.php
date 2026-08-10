@php
    $photos = $case->photos();
    $lead = array_shift($photos);
    $emergency = $case->emergencyNumber();
    $family = $case->get('contacts.tips.family');
    $journey = $case->journey($locale);
@endphp

<x-layout :$case :$locale :$title :$description>

<div class="print:hidden">

    {{-- Language switcher. Kept above everything else: a Turkish speaker
         landing on the English page has to find their own language
         before they will read a single word of the case. --}}
    <nav class="border-b border-slate-200 bg-slate-50 print:hidden">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center gap-2 px-4 py-3">
            <span class="sr-only">{{ __('site.footer.switch') }}</span>
            @foreach ($case->locales() as $code)
                <a href="/{{ $code }}"
                   @if ($code === $locale) aria-current="page" @endif
                   class="flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium transition
                          {{ $code === $locale
                              ? 'bg-slate-900 text-white'
                              : 'bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-100' }}">
                    <x-flag :$code />
                    {{ __('site.locale_name', [], $code) }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- A masthead band rather than inline chips. This is a notice, and a
         notice announces itself across the full width before it starts
         explaining anything. --}}
    <div class="border-b-4 border-notice-600 bg-white">
        <div class="mx-auto flex max-w-4xl flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-4 py-3">
            <p class="text-xl font-extrabold tracking-widest text-notice-600 uppercase sm:text-2xl">
                {{ __('site.hero.eyebrow') }}
            </p>
            {{-- Server-renders the last-seen date, which never goes stale on a
                 static host, then upgrades to a live "day N" counter in
                 the browser where a real clock is available. --}}
            <p class="font-mono text-sm text-slate-600"
               data-since="{{ $case->get('last_seen_date') }}"
               data-template="{{ __('site.hero.day_badge', ['count' => ':count']) }}">
                {{ __('site.hero.since_badge', ['date' => $case->lastSeenFormatted($locale)]) }}
            </p>
        </div>
    </div>

    {{-- The face, the name and a way to report a sighting are the entire job
         of this page, so all three sit above the fold together.
         Everything below is detail for someone already hooked. --}}
    <header class="mx-auto max-w-4xl px-4 pt-8 pb-10">
        {{-- Explicit rows keep the actions tucked under the summary, and give
             the narrative a home in the third row. Left to itself the grid
             spreads the tall photo column across every row instead. --}}
        <div class="grid items-start gap-6 sm:grid-cols-[18rem_minmax(0,1fr)] sm:grid-rows-[auto_auto_1fr] sm:gap-x-8 sm:gap-y-5">

            {{-- One portrait, as large as the column allows. The other photos
                 belong with the description, not competing with the face
                 someone is being asked to recognise. --}}
            <figure class="order-2 sm:order-none sm:col-start-1 sm:row-span-3">
                <img src="{{ $lead['url'] }}" alt="{{ $case->fullName() }}" fetchpriority="high"
                     class="aspect-[3/4] w-full rounded-xl border border-slate-200 bg-slate-100 object-cover object-top shadow-sm">
                <figcaption class="mt-2 text-xs text-slate-500">{{ $lead['caption'] }}</figcaption>
            </figure>

            <div class="order-1 sm:order-none sm:col-start-2 sm:row-start-1">
                <h1 class="text-4xl leading-[1.05] font-extrabold tracking-tight text-balance sm:text-5xl">
                    {{ __('site.hero.headline') }}
                </h1>

                <p class="mt-4 text-2xl font-bold text-slate-800">{{ $case->fullName() }}</p>
                <p class="mt-1 text-lg text-pretty text-slate-600">
                    {{ __('site.hero.summary', [
                        'age' => $case->age(),
                        'date' => $case->lastSeenFormatted($locale),
                    ]) }}
                </p>
            </div>

            {{-- Two different situations need two different numbers, so they
                 never share a button. A past sighting goes to the case
                 file; a live one goes to Turkish emergency services. --}}
            <div class="order-3 flex flex-col gap-3 sm:order-none sm:col-start-2 sm:row-start-2 print:hidden">
                <a href="#contact"
                   class="rounded-lg bg-slate-900 px-5 py-4 text-center text-base font-bold text-white shadow-sm transition hover:bg-slate-800">
                    {{ __('site.hero.cta_tip') }}
                </a>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-notice-300 bg-notice-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-bold text-notice-900">{{ __('site.hero.emergency_prompt') }}</p>
                        <p class="text-xs text-notice-800/80">{{ __('site.contact.tr_only') }}</p>
                    </div>
                    <a href="tel:{{ $emergency }}"
                       class="rounded-md bg-notice-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-notice-700">
                        {{ __('site.hero.emergency_action', ['number' => $emergency]) }}
                    </a>
                </div>
            </div>

            {{-- The narrative sits beside the photographs rather than below
                 them, which fills the column the actions leave short and
                 puts the story next to the face it belongs to. --}}
            <div class="order-4 sm:order-none sm:col-start-2 sm:row-start-3">
                <h2 class="font-mono text-xs tracking-wider text-slate-500 uppercase">
                    {{ __('site.sections.lastseen') }}
                </h2>
                <p class="mt-2 leading-relaxed text-slate-700">{{ __('site.lastseen.body') }}</p>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl space-y-12 px-4 pb-12">

        {{-- The itinerary, drawn as a sequence because it genuinely is one.
             Three unremarkable steps and then a severed connector: the
             flight he was booked on and never boarded. --}}
        <section>
            <h2 class="text-xl font-bold">{{ __('site.journey.title') }}</h2>

            <ol class="mt-6 grid gap-6 sm:grid-cols-4 sm:gap-4">
                @foreach ($journey as $step)
                    <li>
                        <div class="flex items-center gap-2" aria-hidden="true">
                            <span class="size-3 shrink-0 rounded-full {{ $step['broken'] ? 'bg-notice-600' : 'bg-slate-900' }}"></span>
                            @unless ($step['last'])
                                {{-- The connector into the final step is the leg that
                                     never happened, so it is drawn severed. --}}
                                <span class="hidden h-px flex-1 sm:block {{ $loop->remaining === 1 ? 'border-t-2 border-dashed border-notice-400' : 'bg-slate-300' }}"></span>
                            @endunless
                        </div>

                        <p class="mt-3 font-mono text-xs tracking-wider uppercase {{ $step['broken'] ? 'text-notice-700' : 'text-slate-500' }}">
                            {{ $step['date'] }}
                        </p>
                        <p class="mt-1 font-bold {{ $step['broken'] ? 'text-notice-700' : 'text-slate-900' }}">
                            {{ $step['label'] }}
                        </p>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $step['detail'] }}</p>
                    </li>
                @endforeach
            </ol>

            <p class="mt-8 max-w-prose border-l-4 border-slate-900 bg-slate-50 px-4 py-3 text-sm leading-relaxed text-slate-700">
                {{ __('site.lastseen.anchor_note') }}
            </p>
        </section>

        <section>
            <h2 class="text-xl font-bold">{{ __('site.sections.description') }}</h2>

            @if ($photos)
                <div class="mt-4 grid grid-cols-2 gap-4 sm:max-w-md">
                    @foreach ($photos as $photo)
                        <figure>
                            <img src="{{ $photo['url'] }}" alt="{{ $case->fullName() }}" loading="lazy"
                                 class="aspect-[4/5] w-full rounded-lg border border-slate-200 bg-slate-100 object-cover object-top">
                            <figcaption class="mt-1.5 text-xs text-slate-500">{{ $photo['caption'] }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif

            <dl class="mt-6 divide-y divide-slate-200 rounded-lg border border-slate-200">
                @foreach ($case->description() as $row)
                    <div class="flex flex-col gap-1 p-4 sm:flex-row sm:gap-6">
                        <dt class="w-48 shrink-0 pt-0.5 font-mono text-xs tracking-wider text-slate-500 uppercase">{{ $row['label'] }}</dt>
                        <dd class="text-slate-900">{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($marks = $case->marks())
                <h3 class="mt-6 font-semibold">{{ __('site.labels.marks') }}</h3>
                <ul class="mt-2 max-w-prose space-y-2">
                    @foreach ($marks as $mark)
                        <li class="flex gap-3 text-slate-700">
                            <span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rounded-full bg-notice-600"></span>
                            <span>{{ $mark }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($phone = $case->phoneStatus())
                <h3 class="mt-6 font-semibold">{{ __('site.labels.phone') }}</h3>
                <p class="mt-2 max-w-prose text-slate-700">{{ $phone }}</p>
            @endif
        </section>

        @if ($belongings = $case->belongings())
            <section>
                <h2 class="text-xl font-bold">{{ __('site.sections.belongings') }}</h2>
                <ul class="mt-4 max-w-prose space-y-3">
                    @foreach ($belongings as $item)
                        <li class="flex gap-3 text-slate-700">
                            <span aria-hidden="true" class="mt-2 size-1.5 shrink-0 rounded-full bg-slate-400"></span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section id="contact" class="scroll-mt-6">
            <h2 class="text-xl font-bold">{{ __('site.sections.contact') }}</h2>

            <div class="mt-4 rounded-lg border border-notice-300 bg-notice-50 p-5">
                <h3 class="font-bold text-notice-900">{{ __('site.contact.emergency_title') }}</h3>
                <p class="mt-2 max-w-prose text-sm leading-relaxed text-notice-900">
                    {{ __('site.contact.emergency_body', ['number' => $emergency]) }}
                </p>
                <a href="tel:{{ $emergency }}"
                   class="mt-4 inline-block rounded-md bg-notice-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-notice-700 print:hidden">
                    {{ __('site.contact.call') }} {{ $emergency }}
                </a>
            </div>

            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-5">
                <h3 class="font-bold text-slate-900">{{ __('site.contact.meet_title') }}</h3>
                <p class="mt-2 max-w-prose text-sm leading-relaxed text-slate-700">{{ __('site.contact.meet_body') }}</p>
            </div>

            <h3 class="mt-8 font-bold">{{ __('site.contact.tips_title') }}</h3>
            <p class="mt-2 max-w-prose leading-relaxed text-slate-700">{{ __('site.contact.tips_body') }}</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($case->tipContacts() as $contact)
                    <div class="flex flex-col rounded-lg border border-slate-200 p-4">
                        <p class="font-semibold text-slate-900">{{ $contact['label'] }}</p>
                        @if ($contact['note'])
                            <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $contact['note'] }}</p>
                        @endif

                        <div class="mt-auto flex flex-wrap gap-2 pt-4">
                            <a href="{{ $contact['href'] }}"
                               class="rounded-md bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">
                                {{ __('site.contact.call') }} <span class="font-mono">{{ $contact['number'] }}</span>
                            </a>
                            @if ($contact['whatsapp'])
                                <a href="{{ $contact['whatsapp'] }}" rel="noopener"
                                   class="rounded-md bg-white px-4 py-2 text-sm font-bold text-slate-900 ring-1 ring-slate-300 transition hover:bg-slate-50">
                                    {{ __('site.contact.whatsapp') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-4 max-w-prose text-sm leading-relaxed text-slate-600">{{ __('site.contact.in_turkey_note') }}</p>

            @if ($reference = $case->get('contacts.case_reference'))
                <p class="mt-3 text-sm text-slate-600">
                    {{ __('site.contact.case_reference') }}: <strong>{{ $reference }}</strong>
                </p>
            @endif
        </section>

        {{-- Deliberately not a third alert colour. Red is reserved for the one
             thing on this page that is genuinely an emergency, and diluting
             it across every warning would cost it all its meaning. --}}
        <section class="max-w-prose border-l-4 border-slate-900 bg-slate-50 py-4 pr-5 pl-5">
            <h2 class="font-bold text-slate-900">{{ __('site.scam.title') }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ __('site.scam.body') }}</p>
        </section>

        <section class="print:hidden">
            <h2 class="text-xl font-bold">{{ __('site.sections.share') }}</h2>
            <p class="mt-3 max-w-prose leading-relaxed text-slate-700">{{ __('site.share.body') }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" data-copy data-copied="{{ __('site.share.copied') }}"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800">
                    {{ __('site.share.copy') }}
                </button>
                <button type="button" data-print
                        class="rounded-md bg-white px-4 py-2 text-sm font-bold text-slate-900 ring-1 ring-slate-300 transition hover:bg-slate-50">
                    {{ __('site.share.print') }}
                </button>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-4xl px-4 py-6 text-sm text-slate-600">
            {{-- Printed flyers leave the browser behind, so the address has
                 to travel with them on paper. --}}
            <p class="hidden font-mono text-base font-bold text-slate-900 print:block">{{ $case->url($locale) }}</p>

            <p class="font-mono text-xs">{{ __('site.footer.updated', ['date' => $case->formatDate($case->get('last_updated'), $locale)]) }}</p>
            <p class="mt-3 print:hidden">
                @foreach ($case->locales() as $code)
                    <a href="/{{ $code }}" class="underline underline-offset-2 hover:text-slate-900">
                        {{ __('site.locale_name', [], $code) }}</a>@if (! $loop->last)<span aria-hidden="true"> &middot; </span>@endif
                @endforeach
            </p>
        </div>
    </footer>

    {{-- Phones are where nearly all of this traffic lands, so a way to report
         something never scrolls out of reach. WhatsApp leads when there is
         a number for it, since it costs a tipper in Istanbul nothing. --}}
    <div class="sticky bottom-0 border-t border-slate-200 bg-white/95 backdrop-blur sm:hidden print:hidden">
        <div class="flex gap-2 px-4 py-3">
            @if ($whatsapp = ($family['whatsapp'] ?? null))
                <a href="https://wa.me/{{ $whatsapp }}" rel="noopener"
                   class="flex-1 rounded-lg bg-slate-900 px-4 py-3 text-center text-sm font-bold text-white">
                    {{ __('site.contact.whatsapp') }}
                </a>
            @endif
            <a href="#contact"
               class="flex-1 rounded-lg px-4 py-3 text-center text-sm font-bold
                      {{ $whatsapp
                          ? 'bg-white text-slate-900 ring-1 ring-slate-300'
                          : 'bg-slate-900 text-white' }}">
                {{ __('site.hero.cta_tip') }}
            </a>
        </div>
    </div>

</div>

    <x-poster :$case :$locale />

</x-layout>
