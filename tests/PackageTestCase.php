<?php

namespace Serabass\Yaroute\Tests;

use Serabass\Yaroute\YarouteServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class PackageTestCase extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [YarouteServiceProvider::class];
    }
}
