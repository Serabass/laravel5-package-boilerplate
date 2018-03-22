<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\IncorrectDataException;
use Serabass\Yaroute\Tests\PackageTestCase;

class NamedGroupsTest extends PackageTestCase
{
    public function testCreateNamedNestedGroups()
    {
        $this->yaml->registerFile(__DIR__ . '/yaml/named-groups.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $this->assertNotNull($GETRoutes);

        $this->assertArrayHasKey('group1', $GETRoutes);
        $this->assertEquals('g1.home', $GETRoutes['group1']->action['as']);

        $this->assertArrayHasKey('group1/info', $GETRoutes);
        $this->assertEquals('g1.info', $GETRoutes['group1/info']->action['as']);

        $this->assertArrayHasKey('group1/sub1/home', $GETRoutes);
        $this->assertEquals('g1.sub1.subhome', $GETRoutes['group1/sub1/home']->action['as']);
    }
}
