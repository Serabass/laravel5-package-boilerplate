<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\RegExpAliasAlreadySetException;
use Serabass\Yaroute\Tests\PackageTestCase;

class RegexPresetsTest extends PackageTestCase
{
    public function testRegexPresets()
    {
        $this->yaml->registerFile(__DIR__ . '/../../examples/regex-presets.yaml');

        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $this->assertNotNull($GETRoutes);

        $this->assertArrayHasKey('entity/{id}', $GETRoutes);
        $this->assertEquals('EntityController@show', $GETRoutes['entity/{id}']->action['controller']);
        $this->assertEquals(['id' => '\d+'], $GETRoutes['entity/{id}']->wheres);

        $this->assertArrayHasKey('post/{alias}', $GETRoutes);
        $this->assertEquals('PostController@show', $GETRoutes['post/{alias}']->action['controller']);
        $this->assertEquals(['alias' => '[\w-]+'], $GETRoutes['post/{alias}']->wheres);

        $this->assertArrayHasKey('redirect/{url}', $GETRoutes);
        $this->assertEquals('RedirectController@go', $GETRoutes['redirect/{url}']->action['controller']);
        $this->assertEquals(['url' => '.+'], $GETRoutes['redirect/{url}']->wheres);
    }

    public function testAlreadySet()
    {
        $this->assertException(function () {
            $this->yaml->registerFile(__DIR__ . '/yaml/regex-presets-existing.yaml');
        }, RegExpAliasAlreadySetException::class);
    }
}
