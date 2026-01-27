<?php

namespace Empinet\EasyEmailApi\Tests;

use Empinet\EasyEmailApi\Providers\EasyEmailApiServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [EasyEmailApiServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('easyemailapi.token', 'test-token');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('easyemailapi.cache.store', 'array');
    }
}
