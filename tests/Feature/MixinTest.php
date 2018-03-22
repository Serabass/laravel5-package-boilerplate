<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\Tests\PackageTestCase;
use Serabass\Yaroute\Yaroute;

class MixinTest extends PackageTestCase
{
    /**
     * @var Yaroute
     */
    public $yaml;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->yaml = new Yaroute();
    }

    public function testMixin()
    {
        $this->yaml->registerFile(__DIR__ . '/yaml/mixins.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $POSTRoutes = $routes->get('POST');
        $PUTRoutes = $routes->get('PUT');
        $DELETERoutes = $routes->get('DELETE');
        $this->assertNotNull($GETRoutes);
        $this->assertNotNull($POSTRoutes);
        $this->assertNotNull($PUTRoutes);
        $this->assertNotNull($DELETERoutes);

        $this->assertArrayHasKey('entity', $GETRoutes);
        $entityListRoute = $GETRoutes['entity'];
        $this->assertEquals('myEntity.list', $entityListRoute->action['as']);


        $this->assertArrayHasKey('entity/{id}', $GETRoutes);
        $entityElementRoute = $GETRoutes['entity/{id}'];
        $this->assertEquals('show', $entityElementRoute->action['as']);

        $this->assertArrayHasKey('entity/{id}', $POSTRoutes);
        $entityUpdateRoute = $POSTRoutes['entity/{id}'];
        $this->assertEquals('update', $entityUpdateRoute->action['as']);

        $this->assertArrayHasKey('entity/{id}', $PUTRoutes);
        $entityCreateRoute = $PUTRoutes['entity/{id}'];
        $this->assertEquals('create', $entityCreateRoute->action['as']);


        $this->assertArrayHasKey('entity2', $GETRoutes);
        $entityListRoute = $GETRoutes['entity2'];
        $this->assertEquals('MyEntityController@list', $entityListRoute->action['controller']);
        $this->assertEquals('.list', $entityListRoute->action['as']);

        $this->assertArrayHasKey('entity2/{id}', $GETRoutes);
        $entityElementRoute = $GETRoutes['entity2/{id}'];
        $this->assertEquals('show', $entityElementRoute->action['as']);

        $this->assertArrayHasKey('entity2/{id}', $POSTRoutes);
        $entityUpdateRoute = $POSTRoutes['entity2/{id}'];
        $this->assertEquals('update', $entityUpdateRoute->action['as']);

        $this->assertArrayHasKey('entity2/{id}', $PUTRoutes);
        $entityCreateRoute = $PUTRoutes['entity2/{id}'];
        $this->assertEquals('create', $entityCreateRoute->action['as']);


        $this->assertArrayHasKey('entity3', $GETRoutes);
        $entityListRoute = $GETRoutes['entity3'];
        $this->assertEquals('MyEntityController@list', $entityListRoute->action['controller']);
        $this->assertEquals('.list', $entityListRoute->action['as']);

        $this->assertArrayHasKey('entity3/{id}', $GETRoutes);
        $entityElementRoute = $GETRoutes['entity3/{id}'];
        $this->assertEquals('show', $entityElementRoute->action['as']);

        $this->assertArrayHasKey('entity3/{id}', $POSTRoutes);
        $entityUpdateRoute = $POSTRoutes['entity3/{id}'];
        $this->assertEquals('update', $entityUpdateRoute->action['as']);

        $this->assertArrayHasKey('entity3/{id}', $PUTRoutes);
        $entityCreateRoute = $PUTRoutes['entity3/{id}'];
        $this->assertEquals('create', $entityCreateRoute->action['as']);

        $this->assertArrayHasKey('entity3/{id}', $PUTRoutes);
        $entityCreateRoute = $PUTRoutes['entity3/{id}'];
        $this->assertEquals('create', $entityCreateRoute->action['as']);

        $this->assertArrayHasKey('entity3/anotherRoute', $GETRoutes);
        $entity3GetRoute = $GETRoutes['entity3/anotherRoute'];
        $this->assertEquals('another', $entity3GetRoute->action['as']);
    }

    public function testSimpleMixins()
    {
        $this->yaml->registerFile(__DIR__ . '/yaml/mixins.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $POSTRoutes = $routes->get('POST');

        $this->assertNotNull($GETRoutes);
        $this->assertNotNull($POSTRoutes);

        $this->assertArrayHasKey('my', $GETRoutes);
        $myRoute = $GETRoutes['my'];
        $this->assertEquals('SimpleController@index', $myRoute->action['controller']);

        $this->assertArrayHasKey('my/info', $GETRoutes);
        $myRoute = $GETRoutes['my/info'];
        $this->assertEquals('SimpleController@info', $myRoute->action['controller']);

        $this->assertArrayHasKey('my/submit', $POSTRoutes);
        $myRoute = $POSTRoutes['my/submit'];
        $this->assertEquals('SimpleController@submit', $myRoute->action['controller']);

        $this->assertArrayHasKey('my/contact', $GETRoutes);
        $myRoute = $GETRoutes['my/contact'];
        $this->assertEquals('SimpleController@contact', $myRoute->action['controller']);

        $this->assertArrayHasKey('my/feedback', $GETRoutes);
        $myRoute = $GETRoutes['my/feedback'];
        $this->assertEquals('SimpleController@feedback', $myRoute->action['controller']);

    }
}
