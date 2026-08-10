<?php

use App\Support\CaseFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    App::setLocale(config('oscar.default_locale'));

    $case = CaseFile::fromConfig();

    return view('chooser', [
        'case' => $case,
        'title' => __('site.meta.title', ['age' => $case->age()]),
        'description' => __('site.meta.description', ['age' => $case->age()]),
    ]);
});

Route::get('/{locale}', function (string $locale) {
    App::setLocale($locale);

    $case = CaseFile::fromConfig();

    return view('missing', [
        'case' => $case,
        'locale' => $locale,
        'title' => __('site.meta.title', ['age' => $case->age()]),
        'description' => __('site.meta.description', ['age' => $case->age()]),
    ]);
})->whereIn('locale', config('oscar.locales'))->name('missing');
