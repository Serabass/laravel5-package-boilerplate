<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\Tests\PackageTestCase;

class MultipleGroupsTest extends PackageTestCase
{
    public function testMultipleGroups()
    {
        $this->yaml->registerFile(__DIR__.'/../../examples/multiple-groups.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $this->assertNotNull($GETRoutes);

        $this->assertArrayHasKey('entity/list', $GETRoutes);
        $this->assertEquals('entity.list', $GETRoutes['entity/list']->action['as']);

        $this->assertArrayHasKey('entity/{id}', $GETRoutes);
        $this->assertEquals('entity.item', $GETRoutes['entity/{id}']->action['as']);

        $this->assertArrayHasKey('post/list', $GETRoutes);
        $this->assertEquals('post.list', $GETRoutes['post/list']->action['as']);

        $this->assertArrayHasKey('post/{id}', $GETRoutes);
        $this->assertEquals('post.item', $GETRoutes['post/{id}']->action['as']);
    }
}
