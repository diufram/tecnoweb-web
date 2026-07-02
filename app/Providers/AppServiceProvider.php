<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->forceRootUrlFromRequest();


        if (request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
    }

    private function forceRootUrlFromRequest(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if ($this->forceRootUrlFromAppUrl()) {
            return;
        }

        $request = $this->app->make('request');
        $base = rtrim($request->getBaseUrl(), '/');

        if ($base === '' || $base === '/') {
            $base = $this->detectBaseFromScriptName();
        }

        if ($base !== '' && $base !== '/') {
            $scheme = $request->getScheme();
            URL::forceRootUrl($scheme.'://'.$request->getHost().$base);
            URL::forceScheme($scheme);
        }
    }

    private function forceRootUrlFromAppUrl(): bool
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $path = parse_url($appUrl, PHP_URL_PATH);

        if (! is_string($path) || $path === '' || $path === '/') {
            return false;
        }

        URL::forceRootUrl($appUrl);

        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        if (is_string($scheme) && $scheme !== '') {
            URL::forceScheme($scheme);
        }

        return true;
    }

    private function detectBaseFromScriptName(): string
    {
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');

        if ($scriptName === '') {
            return '';
        }

        $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if (basename($dir) === 'public' && str_contains($dir, '/public')) {
            $dir = substr($dir, 0, strrpos($dir, '/public'));
        }

        if (basename($dir) === 'index.php' || $dir === '.' || $dir === '/') {
            return '';
        }

        return $dir;
    }
}
