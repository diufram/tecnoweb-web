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
    }

    private function forceRootUrlFromRequest(): void
    {
        if ($this->app->runningInConsole()) {
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
