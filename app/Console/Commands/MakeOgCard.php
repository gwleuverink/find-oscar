<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeOgCard extends Command
{
    protected $signature = 'og:card';

    protected $description = 'Render the landscape preview image used when the site is shared';

    /**
     * Link previews are cropped to roughly 1.91:1. Feeding them the portrait
     * passport photo leaves platforms to guess, and they crop to a band
     * across his chin, which is worth nothing to someone scrolling.
     */
    protected const WIDTH = 1200;

    protected const HEIGHT = 630;

    public function handle(): int
    {
        $source = public_path(config('oscar.og_source'));
        $target = public_path(config('oscar.og_image'));

        if (! File::exists($source)) {
            $this->error("Source photo not found: {$source}");

            return self::FAILURE;
        }

        $photo = imagecreatefromjpeg($source);
        $sourceWidth = imagesx($photo);
        $sourceHeight = imagesy($photo);

        $card = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagefill($card, 0, 0, $this->backdropColour($card, $photo, $sourceWidth, $sourceHeight));

        // The portrait itself goes on whole and full height. A landscape band
        // cropped from a passport photo is physically too short to hold a
        // face, and half a face identifies nobody.
        $portraitWidth = (int) round($sourceWidth * (self::HEIGHT / $sourceHeight));

        imagecopyresampled(
            $card, $photo,
            (int) round((self::WIDTH - $portraitWidth) / 2), 0,
            0, 0,
            $portraitWidth, self::HEIGHT,
            $sourceWidth, $sourceHeight,
        );

        // The same red the page uses, so a shared link and the page it opens
        // read as one thing.
        imagefilledrectangle($card, 0, self::HEIGHT - 8, self::WIDTH, self::HEIGHT, imagecolorallocate($card, 200, 16, 46));

        File::ensureDirectoryExists(dirname($target));
        imagejpeg($card, $target, 88);

        imagedestroy($card);
        imagedestroy($photo);

        $this->info('Preview card written to '.config('oscar.og_image'));

        return self::SUCCESS;
    }

    /**
     * Fills the space either side of the portrait, sampled from the corners of
     * the photo itself. A passport shot has a flat backdrop, so the card
     * ends up looking like one document rather than a collage.
     */
    protected function backdropColour(\GdImage $card, \GdImage $photo, int $sourceWidth, int $sourceHeight): int
    {
        $corners = [
            [2, 2],
            [$sourceWidth - 3, 2],
            [2, (int) round($sourceHeight * 0.08)],
            [$sourceWidth - 3, (int) round($sourceHeight * 0.08)],
        ];

        $channels = ['red' => 0, 'green' => 0, 'blue' => 0];

        foreach ($corners as [$x, $y]) {
            $pixel = imagecolorsforindex($photo, imagecolorat($photo, $x, $y));

            foreach ($channels as $channel => $total) {
                $channels[$channel] = $total + $pixel[$channel];
            }
        }

        return imagecolorallocate(
            $card,
            (int) round($channels['red'] / count($corners)),
            (int) round($channels['green'] / count($corners)),
            (int) round($channels['blue'] / count($corners)),
        );
    }
}
