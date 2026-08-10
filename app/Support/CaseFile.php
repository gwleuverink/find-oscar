<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

class CaseFile
{
    /**
     * Resolve the case details straight from configuration so the family can
     * correct a fact in one place and have every language pick it up.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config)
    {
        //
    }

    public static function fromConfig(): self
    {
        return new self(config('oscar'));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    public function name(): string
    {
        return $this->get('name');
    }

    public function fullName(): string
    {
        return $this->get('full_name');
    }

    public function age(): int
    {
        return CarbonImmutable::parse($this->get('date_of_birth'))
            ->diffInYears($this->lastSeen());
    }

    public function lastSeen(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->get('last_seen_date'));
    }

    public function formatDate(string $date, string $locale): string
    {
        return CarbonImmutable::parse($date)->locale($locale)->isoFormat('D MMMM YYYY');
    }

    public function lastSeenFormatted(string $locale): string
    {
        return $this->formatDate($this->get('last_seen_date'), $locale);
    }

    public function dateOfBirthFormatted(string $locale): string
    {
        return $this->formatDate($this->get('date_of_birth'), $locale);
    }

    /**
     * Photos, served from the site root. The export copies the public folder
     * in wholesale, so these paths are identical in development and in
     * the built site.
     *
     * @return list<array{url: string, caption: string, lead: bool}>
     */
    public function photos(): array
    {
        return collect($this->get('photos', []))
            ->values()
            ->map(fn (array $photo, int $index): array => [
                'url' => '/'.$photo['file'],
                'caption' => __('site.photos.'.$photo['caption_key']),
                'lead' => $index === 0,
            ])
            ->all();
    }

    /**
     * The physical description, with anything still unconfirmed dropped
     * rather than rendered as an empty row.
     *
     * @return list<array{label: string, value: string}>
     */
    public function description(): array
    {
        $rows = [
            'full_name' => $this->fullName(),
            'age' => __('site.labels.years', ['count' => $this->age()]),
            'nationality' => __('site.values.nationality.'.$this->get('nationality_code')),
            'height' => $this->get('height_cm') ? $this->get('height_cm').' cm' : null,
            'build' => $this->translatedKey('build', 'build_key'),
            'hair' => $this->translatedKey('hair', 'hair_key'),
            'eyes' => $this->translatedKey('eyes', 'eyes_key'),
            'languages' => $this->languages(),
        ];

        return collect($rows)
            ->filter()
            ->map(fn (string $value, string $key): array => [
                'label' => __('site.labels.'.$key),
                'value' => $value,
            ])
            ->values()
            ->all();
    }

    protected function translatedKey(string $group, string $configKey): ?string
    {
        $key = $this->get($configKey);

        return $key ? __("site.values.{$group}.{$key}") : null;
    }

    public function languages(): string
    {
        return collect($this->get('languages', []))
            ->map(fn (string $code): string => __('site.values.languages.'.$code))
            ->join(', ');
    }

    /** @return list<string> */
    public function marks(): array
    {
        return collect($this->get('marks', []))
            ->map(fn (string $key): string => __('site.marks.'.$key))
            ->all();
    }

    /** @return list<string> */
    public function belongings(): array
    {
        return collect($this->get('belongings', []))
            ->map(fn (string $key): string => __('site.belongings.'.$key))
            ->all();
    }

    /**
     * The itinerary as an ordered list of steps, each carrying whether the
     * leg leading into it is the one that broke. The view uses that to
     * draw the connector as a severed line rather than a solid one.
     *
     * @return list<array{date: string, label: string, detail: string, broken: bool, last: bool}>
     */
    public function journey(string $locale): array
    {
        $steps = $this->get('journey', []);
        $lastIndex = count($steps) - 1;

        return collect($steps)
            ->map(fn (array $step, int $index): array => [
                'date' => $this->formatDate($step['date'], $locale),
                'label' => __("site.journey.{$step['key']}.label"),
                'detail' => __("site.journey.{$step['key']}.detail"),
                'broken' => (bool) ($step['broken'] ?? false),
                'last' => $index === $lastIndex,
            ])
            ->all();
    }

    public function phoneStatus(): ?string
    {
        return $this->translatedKey('phone', 'phone_status_key');
    }

    public function emergencyNumber(): string
    {
        return $this->get('contacts.emergency.number');
    }

    /**
     * Routes for someone who has information but is not watching an emergency
     * unfold. All are full international numbers, because the short codes
     * that reach Turkish services do not work from another country.
     *
     * @return list<array{label: string, note: ?string, number: string, href: string, whatsapp: ?string}>
     */
    public function tipContacts(): array
    {
        $contacts = [
            $this->contact(
                label: __('site.contact.police_nl'),
                number: $this->get('contacts.tips.police_nl.number'),
                note: __('site.contact.police_nl_note', [
                    'domestic' => $this->get('contacts.tips.police_nl.domestic'),
                ]),
            ),
            $this->contact(
                label: __('site.contact.mfa_nl'),
                number: $this->get('contacts.tips.mfa_nl.number'),
                note: __('site.contact.mfa_nl_note'),
                whatsapp: $this->get('contacts.tips.mfa_nl.whatsapp'),
            ),
            $this->contact(
                label: __('site.contact.family'),
                number: $this->get('contacts.tips.family.number'),
                note: __('site.contact.family_note'),
                whatsapp: $this->get('contacts.tips.family.whatsapp'),
            ),
        ];

        return array_values(array_filter($contacts));
    }

    /** @return array{label: string, note: ?string, number: string, href: string, whatsapp: ?string}|null */
    protected function contact(string $label, ?string $number, ?string $note = null, ?string $whatsapp = null): ?array
    {
        if (! $number) {
            return null;
        }

        return [
            'label' => $label,
            'note' => $note,
            'number' => $number,
            'href' => 'tel:'.preg_replace('/[^0-9+]/', '', $number),
            'whatsapp' => $whatsapp ? 'https://wa.me/'.$whatsapp : null,
        ];
    }

    /**
     * Absolute URLs, taken from APP_URL. The export bakes these into canonical
     * tags and preview images, so the publish script sets APP_URL for the
     * build rather than trusting whatever the local .env happens to say.
     */
    public function url(string $locale): string
    {
        return $this->origin().'/'.$locale.'/';
    }

    public function ogImageUrl(): string
    {
        return $this->origin().'/'.$this->get('og_image');
    }

    public function origin(): string
    {
        return rtrim(config('app.url'), '/');
    }

    /**
     * An inline SVG QR code for this locale's page, generated at build time so
     * the printed flyer carries no dependency on anything at runtime.
     *
     * Nobody types a URL off a wall, so the poster leans on this instead.
     * Error correction is raised to M because a taped-up sheet gets
     * rained on, torn and scribbled over before anyone scans it.
     */
    public function qrCodeSvg(string $locale): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle(size: 240, margin: 2),
            new SvgImageBackEnd,
        ));

        $svg = $writer->writeString($this->url($locale), ecLevel: ErrorCorrectionLevel::M());

        // The prolog is only valid at the top of a standalone document, and
        // this markup gets inlined straight into the page.
        return preg_replace('/^<\?xml[^?]*\?>\s*/', '', $svg);
    }

    /** @return list<string> */
    public function locales(): array
    {
        return $this->get('locales', ['en']);
    }
}
