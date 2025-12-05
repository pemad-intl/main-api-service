<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pemad\MainApi\MainApiServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [MainApiServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // set test config
        $app['config']->set('mainapi.base_url', 'https://example.test');
        $app['config']->set('mainapi.app_code', 'test');
        $app['config']->set('mainapi.secret', 'secret');
        $app['config']->set('mainapi.apikey', 'apikey');
    }
}
