<?php

namespace Helium\Docs\Client\Providers;

use Helium\Docs\Client\DocsClient;
use Illuminate\Support\ServiceProvider;

class DocsClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/docs-client.php', 'docs-client'
        );

        $this->publishes([
            __DIR__ . '/../config/docs-client.php' => config_path('docs-client.php')
        ]);

        if ($key = config('docs-client.api_key')) {
            DocsClient::setApiKey($key);
        }
    }
}