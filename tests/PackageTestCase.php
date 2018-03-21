<?php

namespace Serabass\Yaroute\Tests;

use Serabass\Yaroute\PackageServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class PackageTestCase extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PackageServiceProvider::class];
    }
}
