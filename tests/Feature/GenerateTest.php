<?php

namespace Tests\Feature\Yaml;

use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\Commands\GenerateCommand;
use Serabass\Yaroute\Tests\PackageTestCase;

class GenerateTest extends PackageTestCase
{
    public function testGenerateYamlFromRoutes()
    {
        Route::get('/', 'HomeController@index')->name('home');

        Route::group(['prefix' => 'api', 'as' => 'api.'], function () {
            Route::get('/entity', 'Api\\EntityController@index')->name('entity.list');

            Route::post('/entity', 'Api\\EntityController@create')->name('entity.save');

            Route::get('/entity/{id}', 'Api\\EntityController@get')
                ->name('entity.get')
                ->where('id', '\d+');

            $groupData = [
                'prefix' => '/article/{alias}',
                'where' => [
                    'alias' => '[\w-]+'
                ],
                'as' => 'article.'
            ];

            Route::group($groupData, function () {
                Route::get('index', 'ArticleController@index')->name('item');
                Route::delete('', 'ArticleController@destroy')->name('delete');
            });

            Route::get('/sandbox/{param}', 'SandboxController@index')->name('sandbox');
        });

        $cmd = new GenerateCommand();
        $result = $cmd->handle();
        $yaml = $this->yaml->generateYamlFromRoutes();

        $this->assertEquals($result, $yaml);

        $expected = [
            'GET / as home: HomeController@index',
            'GET /api/entity as api.entity.list: Api\\EntityController@index',
            'GET /api/entity/{id ~ \d+} as api.entity.get: Api\\EntityController@get',
            'GET /api/article/{alias ~ [\w-]+}/index as api.article.item: ArticleController@index',
            'GET /api/sandbox/{param} as api.sandbox: SandboxController@index',
            'POST /api/entity as api.entity.save: Api\\EntityController@create',
            'DELETE /api/article/{alias ~ [\w-]+} as api.article.delete: ArticleController@destroy',
        ];
        $joined = join("\n", $expected);
        $this->assertEquals($joined, $yaml);
    }

    public function testGenerateWithCallables() {
        Route::get('/', function() {

        })->name('home');

        $cmd = new GenerateCommand();
        $result = $cmd->handle();
        $yaml = $this->yaml->generateYamlFromRoutes();

        $this->assertEquals($result, $yaml);
        $this->assertEquals('GET / as home: ::CALLABLE::', $yaml);
    }
}
