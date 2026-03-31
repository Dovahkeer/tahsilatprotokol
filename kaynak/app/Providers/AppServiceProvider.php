<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone'));

        $locale = (string) config('app.locale', 'tr_TR');
        $carbonLocale = str_contains($locale, '_') ? explode('_', $locale)[0] : $locale;

        Carbon::setLocale($carbonLocale);
        setlocale(LC_TIME, $locale.'.UTF-8', $locale, $carbonLocale);
    }
}
