<?php

namespace Tests\Feature\Yaml;

use Serabass\Yaroute\Tests\PackageTestCase;
use Serabass\Yaroute\Yaroute;
use Symfony\Component\Yaml\Exception\ParseException;

class DupesTest extends PackageTestCase
{
    /**
     * @var Yaroute
     */
    public $yaml;

    /**
     * DupesTest constructor.
     *
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->yaml = new Yaroute();
    }

    public function testDupes()
    {
        $this->assertException(function () {
            $this->yaml->registerFile(__DIR__ . '/yaml/dupes.yaml');
        }, ParseException::class);
    }
}
