<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\Tests\PackageTestCase;

class NamespacesTest extends PackageTestCase
{
    public function testNamespaces()
    {
        $this->yaml->registerFile(__DIR__.'/../../examples/namespaces.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $this->assertNotNull($GETRoutes);

        $this->assertArrayHasKey('new', $GETRoutes);
        $newRoute = $GETRoutes['new'];

        $this->assertEquals('api-group.new', $newRoute->action['as']);
        $this->assertEquals('Api', $newRoute->action['namespace']);
        $this->assertEquals('Api\MyController@newEntry', $newRoute->action['controller']);
    }
}
