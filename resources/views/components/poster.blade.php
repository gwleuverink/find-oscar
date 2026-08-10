@props(['case', 'locale'])

@php
    $lead = $case->photos()[0];
    $emergency = $case->emergencyNumber();
@endphp

{{--
    The paper version. A flyer taped to a wall in Istanbul gets about two
    seconds of a passer-by's attention, so it carries the face, one
    sentence and one number, and nothing else competes with them.

    Deliberately not pinned to the exact height of the page box. Browser
    print dialogs add their own headers and margins, and a poster sized
    to the millimetre spills onto a second sheet on someone's printer.
--}}
<div class="hidden break-inside-avoid print:block">

    <div class="flex items-baseline justify-between border-b-4 border-notice-600 pb-2">
        <p class="text-3xl font-extrabold tracking-widest text-notice-600 uppercase">
            {{ __('site.hero.eyebrow') }}
        </p>
        <p class="font-mono text-xs text-slate-700">
            {{ __('site.hero.since_badge', ['date' => $case->lastSeenFormatted($locale)]) }}
        </p>
    </div>

    <h1 class="mt-3 text-4xl leading-none font-extrabold text-balance">{{ __('site.hero.headline') }}</h1>

    <div class="mt-4 flex gap-5">
        {{-- Bounded inline rather than by class. If the stylesheet is ever
             stale or partial, an unconstrained photo goes full bleed and
             pushes the phone numbers onto a second sheet. --}}
        <img src="{{ $lead['url'] }}" alt="{{ $case->fullName() }}"
             style="max-width: 100mm; max-height: 140mm; width: auto; height: auto"
             class="shrink-0 self-start object-contain object-top">

        <div class="min-w-0 flex-1">
            <p class="text-2xl leading-tight font-bold">{{ $case->fullName() }}</p>
            <p class="mt-1.5">
                {{ __('site.hero.summary', [
                    'age' => $case->age(),
                    'date' => $case->lastSeenFormatted($locale),
                ]) }}
            </p>

            @if ($physical = $case->physicalSummary())
                <p class="mt-2 font-semibold">{{ $physical }}</p>
            @endif

            <dl class="mt-3 space-y-1 text-sm">
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-mono text-xs tracking-wider text-slate-600 uppercase">
                        {{ __('site.labels.staying_at') }}
                    </dt>
                    <dd class="font-semibold">{{ $case->get('last_seen_hotel') }}</dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-28 shrink-0 font-mono text-xs tracking-wider text-slate-600 uppercase">
                        {{ __('site.labels.last_contact') }}
                    </dt>
                    <dd class="font-mono font-semibold">
                        {{ $case->lastSeenFormatted($locale) }}, {{ $case->get('last_contact_time_istanbul') }}
                    </dd>
                </div>
            </dl>

            @if ($marks = $case->marks())
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach ($marks as $mark)
                        <li class="flex gap-2">
                            <span aria-hidden="true">&bull;</span>
                            <span>{{ $mark }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($belongings = $case->belongings())
                <p class="mt-3 font-mono text-xs tracking-wider text-slate-600 uppercase">
                    {{ __('site.sections.belongings') }}
                </p>
                <ul class="mt-1 space-y-1 text-sm">
                    @foreach ($belongings as $item)
                        <li class="flex gap-2">
                            <span aria-hidden="true">&bull;</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-5 border-t-2 border-slate-900 pt-3">
        <div class="flex items-end justify-between gap-5">
            <div class="min-w-0 flex-1">
                <p class="font-mono text-xs tracking-wider text-slate-600 uppercase">
                    {{ __('site.contact.tips_title') }}
                </p>

                <div class="mt-1.5 flex flex-wrap gap-x-6 gap-y-1">
                    @foreach ($case->tipContacts() as $contact)
                        <div>
                            <p class="text-xs text-slate-700">{{ $contact['label'] }}</p>
                            <p class="font-mono leading-tight font-bold {{ $contact['number'] ? 'text-lg' : 'text-base' }}">
                                {{ $contact['number'] ?? $contact['email'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <p class="mt-2 text-xs text-slate-700">
                    {{ __('site.contact.emergency_title') }}:
                    <span class="font-mono font-bold">{{ $emergency }}</span>
                    ({{ __('site.contact.tr_only') }})
                </p>

                <p class="mt-2 bg-slate-900 px-3 py-2 text-center font-mono text-base font-bold text-white">
                    {{ $case->url($locale) }}
                </p>
            </div>

            {{-- Scanning beats copying a URL off a wall by hand, which is the
                 difference between a passer-by helping and walking on. --}}
            <div class="w-[28mm] shrink-0 [&>svg]:block [&>svg]:h-auto [&>svg]:w-full">
                {!! $case->qrCodeSvg($locale) !!}
            </div>
        </div>
    </div>
</div>
