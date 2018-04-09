<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\Tests\PackageTestCase;

class ImportsTest extends PackageTestCase
{
    public function testNamespaces()
    {
        $this->yaml->registerFile(__DIR__.'/../../examples/root-imports.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $this->assertNotNull($GETRoutes);

        $this->assertArrayHasKey('post/{id}', $GETRoutes);
        $newRoute = $GETRoutes['post/{id}'];

        $this->assertEquals('post.item', $newRoute->action['as']);
        $this->assertEquals('PostController@item', $newRoute->action['controller']);

        $this->assertArrayHasKey('post/good/{id}', $GETRoutes);
        $newRoute = $GETRoutes['post/good/{id}'];

        $this->assertEquals('post.good.item', $newRoute->action['as']);
        $this->assertEquals('GoodController@item', $newRoute->action['controller']);
    }
}
