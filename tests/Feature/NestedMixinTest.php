<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\Tests\PackageTestCase;

class NestedMixinTest extends PackageTestCase
{
    public function testNestedMixinPoint()
    {
        $this->yaml->registerFile(__DIR__.'/../../examples/nested-mixins.yaml');
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

        $this->assertArrayHasKey('point', $GETRoutes);
        $entityListRoute = $GETRoutes['point'];
        $this->assertEquals('point.list', $entityListRoute->action['as']);
        $this->assertEquals('PointController@list', $entityListRoute->action['controller']);

        $this->assertArrayHasKey('point', $PUTRoutes);
        $entityCreateRoute = $PUTRoutes['point'];
        $this->assertEquals('point.create', $entityCreateRoute->action['as']);
        $this->assertEquals('PointController@create', $entityCreateRoute->action['controller']);
        $this->assertEquals(['auth:api'], $entityCreateRoute->action['middleware']);

        $this->assertArrayHasKey('point/{id}', $GETRoutes);
        $entityItemRoute = $GETRoutes['point/{id}'];
        $this->assertEquals('point.item', $entityItemRoute->action['as']);
        $this->assertEquals('PointController@item', $entityItemRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemRoute->action['wheres']);

        $this->assertArrayHasKey('point/{id}', $POSTRoutes);
        $entityUpdateRoute = $POSTRoutes['point/{id}'];
        $this->assertEquals('point.update', $entityUpdateRoute->action['as']);
        $this->assertEquals('PointController@update', $entityUpdateRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityUpdateRoute->action['wheres']);

        $this->assertArrayHasKey('point/{id}', $DELETERoutes);
        $entityDeleteRoute = $DELETERoutes['point/{id}'];
        $this->assertEquals('point.delete', $entityDeleteRoute->action['as']);
        $this->assertEquals('PointController@delete', $entityDeleteRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityDeleteRoute->action['wheres']);

        $this->assertArrayHasKey('point/{id}/comments', $GETRoutes);
        $entityItemCommentsRoute = $GETRoutes['point/{id}/comments'];
        $this->assertEquals('point.comments', $entityItemCommentsRoute->action['as']);
        $this->assertEquals('PointController@getComments', $entityItemCommentsRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemCommentsRoute->action['wheres']);

        $this->assertArrayHasKey('point/{id}/addComment', $PUTRoutes);
        $entityItemAddCommentRoute = $PUTRoutes['point/{id}/addComment'];
        $this->assertEquals('point.postComment', $entityItemAddCommentRoute->action['as']);
        $this->assertEquals('PointController@addComment', $entityItemAddCommentRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemAddCommentRoute->action['wheres']);

        $this->assertArrayHasKey('point/{id}/updateComment/{commentId}', $POSTRoutes);
        $entityItemUpdateCommentRoute = $POSTRoutes['point/{id}/updateComment/{commentId}'];
        $this->assertEquals('point.updateComment', $entityItemUpdateCommentRoute->action['as']);
        $this->assertEquals('PointController@updateComment', $entityItemUpdateCommentRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemUpdateCommentRoute->action['wheres']);
        $this->assertEquals(['commentId' => '\d+'], $entityItemUpdateCommentRoute->wheres);

        $this->assertArrayHasKey('point/{id}/deleteComment/{commentId}', $DELETERoutes);
        $entityItemDeleteCommentRoute = $DELETERoutes['point/{id}/deleteComment/{commentId}'];
        $this->assertEquals('point.deleteComment', $entityItemDeleteCommentRoute->action['as']);
        $this->assertEquals('PointController@deleteComment', $entityItemDeleteCommentRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemUpdateCommentRoute->action['wheres']);
        $this->assertEquals(['commentId' => '\d+'], $entityItemUpdateCommentRoute->wheres);
    }

    public function testNestedMixinGood()
    {
        $this->yaml->registerFile(__DIR__.'/../../examples/nested-mixins.yaml');
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

        $this->assertArrayHasKey('good', $GETRoutes);
        $entityListRoute = $GETRoutes['good'];
        $this->assertEquals('good.list', $entityListRoute->action['as']);
        $this->assertEquals('GoodController@list', $entityListRoute->action['controller']);

        $this->assertArrayHasKey('good', $PUTRoutes);
        $entityCreateRoute = $PUTRoutes['good'];
        $this->assertEquals('good.create', $entityCreateRoute->action['as']);
        $this->assertEquals('GoodController@create', $entityCreateRoute->action['controller']);
        $this->assertEquals(['auth:api'], $entityCreateRoute->action['middleware']);

        $this->assertArrayHasKey('good/{id}', $GETRoutes);
        $entityItemRoute = $GETRoutes['good/{id}'];
        $this->assertEquals('good.item', $entityItemRoute->action['as']);
        $this->assertEquals('GoodController@item', $entityItemRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemRoute->action['wheres']);

        $this->assertArrayHasKey('good/{id}', $POSTRoutes);
        $entityUpdateRoute = $POSTRoutes['good/{id}'];
        $this->assertEquals('good.update', $entityUpdateRoute->action['as']);
        $this->assertEquals('GoodController@update', $entityUpdateRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityUpdateRoute->action['wheres']);

        $this->assertArrayHasKey('good/{id}', $DELETERoutes);
        $entityDeleteRoute = $DELETERoutes['good/{id}'];
        $this->assertEquals('good.delete', $entityDeleteRoute->action['as']);
        $this->assertEquals('GoodController@delete', $entityDeleteRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityDeleteRoute->action['wheres']);

        $this->assertArrayHasKey('good/{id}/comments', $GETRoutes);
        $entityItemCommentsRoute = $GETRoutes['good/{id}/comments'];
        $this->assertEquals('good.comments', $entityItemCommentsRoute->action['as']);
        $this->assertEquals('GoodController@getComments', $entityItemCommentsRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemCommentsRoute->action['wheres']);

        $this->assertArrayHasKey('good/{id}/addComment', $PUTRoutes);
        $entityItemAddCommentRoute = $PUTRoutes['good/{id}/addComment'];
        $this->assertEquals('good.postComment', $entityItemAddCommentRoute->action['as']);
        $this->assertEquals('GoodController@addComment', $entityItemAddCommentRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemAddCommentRoute->action['wheres']);

        $this->assertArrayHasKey('good/{id}/updateComment/{commentId}', $POSTRoutes);
        $entityItemUpdateCommentRoute = $POSTRoutes['good/{id}/updateComment/{commentId}'];
        $this->assertEquals('good.updateComment', $entityItemUpdateCommentRoute->action['as']);
        $this->assertEquals('GoodController@updateComment', $entityItemUpdateCommentRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemUpdateCommentRoute->action['wheres']);
        $this->assertEquals(['commentId' => '\d+'], $entityItemUpdateCommentRoute->wheres);

        $this->assertArrayHasKey('good/{id}/deleteComment/{commentId}', $DELETERoutes);
        $entityItemDeleteCommentRoute = $DELETERoutes['good/{id}/deleteComment/{commentId}'];
        $this->assertEquals('good.deleteComment', $entityItemDeleteCommentRoute->action['as']);
        $this->assertEquals('GoodController@deleteComment', $entityItemDeleteCommentRoute->action['controller']);
        $this->assertEquals(['id' => '\d+'], $entityItemUpdateCommentRoute->action['wheres']);
        $this->assertEquals(['commentId' => '\d+'], $entityItemUpdateCommentRoute->wheres);
    }
}
