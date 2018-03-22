<?php

namespace Tests\Feature\Yaml;

use Serabass\Yaroute\Tests\PackageTestCase;
use Symfony\Component\Yaml\Exception\ParseException;

class DupesTest extends PackageTestCase
{
    public function testDupes()
    {
        $this->assertException(function () {
            $this->yaml->registerFile(__DIR__ . '/yaml/dupes.yaml');
        }, ParseException::class);
    }
}
