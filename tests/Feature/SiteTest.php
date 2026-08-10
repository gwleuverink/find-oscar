<?php

use App\Support\CaseFile;

// Spelled out rather than read from config, because Pest resolves datasets
// before the container boots. The test below keeps the two in step.
dataset('locales', ['en', 'tr', 'nl', 'ru']);

it('publishes exactly the locales it is configured for', function () {
    expect(config('oscar.locales'))->toEqualCanonicalizing(['en', 'tr', 'nl', 'ru']);
});

it('renders every locale', function (string $locale) {
    $this->get("/{$locale}")
        ->assertOk()
        ->assertSee(config('oscar.full_name'))
        ->assertSee('lang="'.$locale.'"', escape: false);
})->with('locales');

it('translates every string it renders', function (string $locale) {
    // A missing key falls through to its own dotted path, which would ship
    // "site.contact.call" to a stranger holding the one detail that
    // finds him. Catch it here rather than in production.
    expect($this->get("/{$locale}")->getContent())
        ->not->toMatch('/site\.[a-z_]+\.[a-z_]+/');
})->with('locales');

/*
 * These guards are written as patterns rather than as a list of the actual
 * details. The repository has to be public for Pages to serve it, so a
 * test that names what it protects would publish it on line one.
 */

it('publishes no bank details or home addresses', function (string $locale) {
    $content = $this->get("/{$locale}")->getContent();

    expect($content)->not->toMatch('/\bNL\d{2}[A-Z]{4}\d{10}\b/');   // an IBAN

    // The police station is a public building and is published on purpose.
    // Any other Dutch postcode on the page is somebody's home.
    preg_match_all('/\b\d{4}\s?[A-Z]{2}\b/', $content, $found);

    $publicAddress = config('oscar.contacts.tips.police_nl.address');

    foreach (array_unique($found[0]) as $postcode) {
        expect($publicAddress)->toContain($postcode);
    }
})->with('locales');

it('dials nothing the case configuration does not publish', function (string $locale) {
    // The family asked for no private numbers on the page. Rather than naming
    // the one to keep out, this pins every dialled number to the handful
    // configuration declares, so a stray one cannot slip through.
    $case = CaseFile::fromConfig();

    $published = collect($case->tipContacts())
        ->pluck('number')
        ->filter()
        ->map(fn (string $number): string => preg_replace('/[^0-9+]/', '', $number))
        ->push($case->emergencyNumber())
        ->all();

    $content = $this->get("/{$locale}")->getContent();

    preg_match_all('/(?:tel:|wa\.me\/)([+0-9]+)/', $content, $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach (array_unique($matches[1]) as $dialled) {
        expect(ltrim($dialled, '+'))->toBeIn(
            array_map(fn (string $number): string => ltrim($number, '+'), $published)
        );
    }
})->with('locales');

it('only offers numbers that can be dialled from abroad as tip lines', function () {
    // Short codes such as 112 reach the caller's own country, not Turkey, so
    // every tip route that is a phone number carries a full international
    // prefix. Email-only routes travel just fine on their own.
    foreach (CaseFile::fromConfig()->tipContacts() as $contact) {
        if ($contact['number']) {
            expect($contact['number'])->toStartWith('+');
        } else {
            expect($contact['email'])->toContain('@');
        }
    }
});

it('reaches the family by email and never by a private number', function (string $locale) {
    $family = collect(CaseFile::fromConfig()->tipContacts())
        ->firstWhere('label', __('site.contact.family'));

    expect($family)->not->toBeNull()
        ->and($family['number'])->toBeNull()
        ->and($family['whatsapp'])->toBeNull();

    expect($this->get("/{$locale}")->getContent())->toContain($family['mailto']);
})->with('locales');

it('sends the emergency number somewhere different from the tip lines', function () {
    $case = CaseFile::fromConfig();

    expect(collect($case->tipContacts())->pluck('number'))
        ->not->toContain($case->emergencyNumber());
});

it('lists every locale as an export path', function () {
    // The crawler should reach all four from the chooser, but a locale that
    // is not also listed here would vanish from the build the moment a
    // link to it is moved or removed.
    expect(config('export.paths'))->toContain(...config('oscar.locales'));
});

it('serves photos from the site root', function (string $locale) {
    // The export copies public/ to the root of the build, so absolute paths
    // resolve identically in development and in the published site.
    expect($this->get("/{$locale}")->getContent())
        ->toContain('src="/photos/')
        ->not->toContain('src="photos/');
})->with('locales');

it('gives the shared root a preview card', function () {
    // The bare domain is what gets pasted into WhatsApp. Without these it
    // posts as a naked link with no face and no name attached.
    $this->get('/')
        ->assertOk()
        ->assertSee('property="og:title"', escape: false)
        ->assertSee('property="og:description"', escape: false)
        ->assertSee('property="og:image"', escape: false)
        ->assertSee('name="twitter:card"', escape: false)
        ->assertSee('name="twitter:image"', escape: false)
        ->assertDontSee('noindex');
});

it('previews at the aspect ratio link cards actually crop to', function () {
    $card = public_path(config('oscar.og_image'));

    expect($card)->toBeFile();

    [$width, $height] = getimagesize($card);

    expect($width)->toBe(1200)
        ->and($height)->toBe(630);
});

it('guards the emergency number behind a confirmation', function (string $locale) {
    $content = $this->get("/{$locale}")->getContent();

    expect($content)
        ->toContain('id="emergency-confirm"')
        ->toContain(__('site.contact.confirm_title'));

    // Both routes to 112 go through the dialog, not just the one in the hero.
    // Matched on the anchor itself, since the script names the hook too.
    $emergency = CaseFile::fromConfig()->emergencyNumber();

    expect(substr_count($content, "\"tel:{$emergency}\" data-confirm-emergency"))->toBe(2);
})->with('locales');

it('renders a print-only poster on every locale', function (string $locale) {
    expect($this->get("/{$locale}")->getContent())->toContain('break-inside-avoid print:block');
})->with('locales');

it('embeds a QR code for this locale in the poster', function (string $locale) {
    $svg = CaseFile::fromConfig()->qrCodeSvg($locale);

    // An XML prolog partway down a page is invalid, and the browser would
    // render it as text across the poster instead of dropping it.
    expect($svg)->toStartWith('<svg')->not->toContain('<?xml');

    expect($this->get("/{$locale}")->getContent())->toContain($svg);
})->with('locales');
