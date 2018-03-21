<?php

namespace Vendor\Yaroute\Tests;

use Vendor\Yaroute\PackageServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class PackageTestCase extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PackageServiceProvider::class];
    }
}
