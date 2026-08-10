<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Case Details
    |--------------------------------------------------------------------------
    |
    | Every language-neutral fact about the case lives here so the page can be
    | updated without touching markup or translations. Translated prose
    | lives in lang/{locale}/site.php alongside this file.
    |
    | Deliberately NOT published: bank details, home addresses, medical
    | history, the family's theory of what happened, and the amounts
    | or channels of any money transferred. See README-privacy.
    |
    */

    'name' => 'Oscar',
    'full_name' => 'Oscar Tristan Huizinga',
    'date_of_birth' => '1970-07-24',
    'nationality_code' => 'nl',

    /*
    | Physical description. Height, build and clothing were not in the police
    | report, so they are null until the family confirms them. Anything
    | left null is simply omitted from the rendered page.
    */

    'height_cm' => null,            // TODO: confirm with family
    'build_key' => 'slim',          // slim | average | heavy — from photos, confirm
    'hair_key' => 'blond_thinning',
    'eyes_key' => 'blue_grey',
    'glasses' => false,
    'facial_hair' => false,

    /*
    | Distinguishing features that a stranger could actually spot. Keys map
    | to translated strings so each one reads naturally per locale.
    */

    'marks' => [
        'gold_chain',               // thin gold neck chain, visible in all photos
        'weathered_face',
    ],

    'languages' => ['nl', 'en', 'ru_basic'],

    /*
    | Disappearance timeline. Last contact was a phone call at 15:18 Dutch
    | time, which is 16:18 in Istanbul. The page always shows Istanbul
    | local time because that is where the tips will come from.
    */

    'last_seen_date' => '2026-08-03',
    'last_contact_time_istanbul' => '16:18',
    'last_seen_city' => 'Istanbul',
    'last_seen_country_code' => 'tr',
    'last_seen_hotel' => 'Emirtimes Hotel, Kartal, Istanbul',
    'last_seen_district' => 'Kartal',

    /*
    | He was travelling to Sabiha Gökçen for a flight onward to Moscow and
    | then Omsk, and never boarded it. The hotel and the airport are the
    | two anchors most likely to jog a local witness's memory.
    */

    'intended_destination' => 'Omsk, Russia (via Moscow)',
    'intended_airport' => 'Istanbul Sabiha Gökçen (SAW)',
    'arrived_istanbul_date' => '2026-07-30',

    /*
    | The itinerary, and the leg that never happened. Ordered, because the
    | order is the whole point: three ordinary days, then a flight he
    | did not board. The last step renders as a broken connection.
    */

    'journey' => [
        ['key' => 'arrived', 'date' => '2026-07-30'],
        ['key' => 'hotel', 'date' => '2026-07-31'],
        ['key' => 'last_contact', 'date' => '2026-08-03'],
        ['key' => 'flight', 'date' => '2026-08-03', 'broken' => true],
    ],

    /*
    | Luggage he is believed to have with him. The laptop is unusual enough
    | to be worth listing: a QWERTY keyboard overlaid with Cyrillic
    | characters is not something a stranger forgets seeing.
    */

    'belongings' => [
        'red_daypack',
        'dark_travel_bag',
        'laptop_cyrillic',
    ],

    'phone_status_key' => 'off_since',

    /*
    | Photos are resolved relative to the site root. The passport photo leads
    | because it is the most recent and the most identifiable; the summer
    | candid matches how he would have looked in Istanbul in August.
    */

    'photos' => [
        ['file' => 'photos/oscar-1-passport.jpg', 'caption_key' => 'passport'],
        ['file' => 'photos/oscar-2-summer.jpg', 'caption_key' => 'summer'],
        ['file' => 'photos/oscar-3-jacket.jpg', 'caption_key' => 'jacket'],
    ],

    'og_image' => 'photos/oscar-1-passport.jpg',

    /*
    | Contact routes, split by urgency because the two need different numbers.
    |
    | 112 is Turkey's unified emergency line and 155 has been folded into it.
    | Both are short codes: they cannot be dialled from outside Turkey, and
    | they are for emergencies, not for "I think I saw him last week".
    |
    | Everything under 'tips' is in full international format so it works
    | from any country the page is read in.
    */

    'contacts' => [

        'emergency' => [
            'number' => '112',
            'country_code' => 'tr',
        ],

        'tips' => [

            // The missing person report was filed in the Netherlands, so the
            // Dutch police hold the case. This is their non-emergency
            // line, reachable from abroad, unlike 0900-8844.
            'police_nl' => [
                'number' => '+31343578844',
                'domestic' => '0900-8844',
            ],

            // Nederland Wereldwijd, the Dutch MFA's 24/7 consular centre,
            // already involved in this case. Also answers on WhatsApp.
            'mfa_nl' => [
                'number' => '+31247247247',
                'whatsapp' => '31247247247',
            ],

            /*
            | Withheld at the family's request: no private numbers on a public
            | page. A published mobile invites cranks and, in a case that
            | already involves extortion, worse. To be replaced by a
            | mailto address once they have one set up.
            |
            | 'family' => [
            |     'name' => 'Dirk & Rita Huizinga',
            |     'number' => '+316...',
            |     'whatsapp' => '316...',
            | ],
            */
        ],

        // TODO: add once the police issue a reference number for the case.
        'case_reference' => null,
    ],

    /*
    | The canonical origin comes from APP_URL, which the publish script sets
    | for the build. Social platforms refuse to render relative preview
    | images, so it has to be the real domain before the first share.
    */

    'locales' => ['en', 'tr', 'nl', 'ru'],
    'default_locale' => 'en',

    'last_updated' => '2026-08-10',

];
