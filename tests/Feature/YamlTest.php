<?php

namespace Tests\Feature\Yaml;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Serabass\Yaroute\IncorrectDataException;
use Serabass\Yaroute\Tests\PackageTestCase;
use Serabass\Yaroute\Yaroute;

class YamlTest extends PackageTestCase
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

    public function testParseRouteString()
    {
        $this->assertNull($this->yaml->parseRouteString('/'));

        $this->assertEquals($this->yaml->parseRouteString('GET /checkToken2 as checkToken2 uses api;guest'),
            [
                'method'     => ['GET'],
                'path'       => '/checkToken2',
                'name'       => 'checkToken2',
                'middleware' => ['api', 'guest'],
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET|POST /checkToken3 as checkToken3'),
            [
                'method' => ['GET', 'POST'],
                'path'   => '/checkToken3',
                'name'   => 'checkToken3',
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET /checkToken4'),
            [
                'method' => ['GET'],
                'path'   => '/checkToken4',
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET /checkToken4 uses api'),
            [
                'method'     => ['GET'],
                'path'       => '/checkToken4',
                'middleware' => ['api'],
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET /checkToken5 as checkToken5 uses api'),
            [
                'method'     => ['GET'],
                'path'       => '/checkToken5',
                'name'       => 'checkToken5',
                'middleware' => ['api'],
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET    /checkToken5    as    checkToken5    uses api;    auth'),
            [
                'method'     => ['GET'],
                'path'       => '/checkToken5',
                'name'       => 'checkToken5',
                'middleware' => ['api', 'auth'],
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET|POST|PUT /checkToken6 uses api;auth'),
            [
                'method'     => ['GET', 'POST', 'PUT'],
                'path'       => '/checkToken6',
                'middleware' => ['api', 'auth'],
            ]);

        $this->assertEquals($this->yaml->parseRouteString('GET /checkToken7 as checkToken7'),
            [
                'method' => ['GET'],
                'path'   => '/checkToken7',
                'name'   => 'checkToken7',
            ]);

        $this->assertException(function () {
            $this->yaml->parseMixinString('+myResourceMixin2(ControllerName, Alias = = = =)', null);
        }, IncorrectDataException::class);

        $this->assertNull($this->yaml->parseRouteString('malformed string'));
    }

    public function testParseGroupString()
    {
        $this->assertEquals($this->yaml->parseGroupString('/'), [
            'prefix' => '/'
        ]);

        $this->assertEquals($this->yaml->parseGroupString('/ as root'), [
            'prefix' => '/',
            'as' => 'root'
        ]);

        $this->assertEquals($this->yaml->parseGroupString('/ uses auth:api'),
            [
                'prefix' => '/',
                'middleware'   => ['auth:api'],
                'as' => ''
            ]);

        $this->assertEquals($this->yaml->parseGroupString('/{id} as name uses auth:api'),
            [
                'prefix' => '/{id}',
                'middleware'   => ['auth:api'],
                'as'   => 'name',
            ]);

    }

    public function testParseActionString()
    {
        $this->assertEquals($this->yaml->parseActionString('UserController@myAction'), [
            'controller' => 'UserController',
            'action'     => 'myAction'
        ]);

        $this->assertEquals($this->yaml->parseActionString('UserController@myAction'), [
            'controller' => 'UserController',
            'action'     => 'myAction'
        ]);

    }

    public function testExample()
    {
        $this->yaml->registerFile(__DIR__ . '/yaml/routes1.yaml');
        $routes = Route::getRoutes();
        $this->assertTrue($routes instanceof RouteCollection);
        $GETRoutes = $routes->get('GET');
        $this->assertNotNull($GETRoutes);

        $this->assertArrayHasKey('{path?}', $GETRoutes);

        $this->assertEquals('HomeController@index', $GETRoutes['{path?}']->action['controller']);
        $this->assertEquals('home', $GETRoutes['{path?}']->action['as']);
        $this->assertEquals(['guest'], $GETRoutes['{path?}']->action['middleware']);
        $this->assertEquals(['path' => '^(?!admin).*'], $GETRoutes['{path?}']->wheres);

        $this->assertArrayHasKey('checkToken2', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['checkToken2']->methods);
        $this->assertEquals('UserController@checkToken2', $GETRoutes['checkToken2']->action['controller']);
        $this->assertEquals('checkToken2', $GETRoutes['checkToken2']->action['as']);
        $this->assertEquals(['api,guest'], $GETRoutes['checkToken2']->action['middleware']);

        $this->assertArrayHasKey('checkToken3', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['checkToken3']->methods);
        $this->assertEquals('UserController@checkToken3', $GETRoutes['checkToken3']->action['controller']);
        $this->assertEquals('checkToken3', $GETRoutes['checkToken3']->action['as']);


        $POSTRoutes = $routes->get('POST');
        $this->assertNotNull($POSTRoutes);
        $this->assertArrayHasKey('save', $POSTRoutes);
        $this->assertEquals(['POST'], $POSTRoutes['save']->methods);
        $this->assertEquals(['auth'], $POSTRoutes['save']->action['middleware']);

        $this->assertArrayHasKey('api/entity', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['api/entity']->methods);

        $this->assertEquals(['api'], $GETRoutes['api/entity']->action['middleware']);
        $this->assertEquals('EntityController@list', $GETRoutes['api/entity']->action['controller']);

        $this->assertArrayHasKey('api/entity/{id}', $POSTRoutes);
        $this->assertEquals(['POST'], $POSTRoutes['api/entity/{id}']->methods);
        $this->assertEquals(['api'], $POSTRoutes['api/entity/{id}']->action['middleware']);
        $wheres = $POSTRoutes['api/entity/{id}']->wheres;
        $this->assertArrayHasKey('id', $wheres);
        $this->assertEquals('\d+', $wheres['id']);

        $this->assertArrayHasKey('api/admin/index', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['api/admin/index']->methods);
        $this->assertEquals(['api'], $GETRoutes['api/admin/index']->action['middleware']);

        $this->assertArrayHasKey('api/admin/entity/{id}', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['api/admin/entity/{id}']->methods);
        $this->assertEquals(['api'], $GETRoutes['api/admin/entity/{id}']->action['middleware']);
        $wheres = $GETRoutes['api/admin/entity/{id}']->wheres;
        $this->assertArrayHasKey('id', $wheres);
        $this->assertEquals('\d+', $wheres['id']);

        $this->assertArrayHasKey('api/admin/subroute3/entity/{id}', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['api/admin/subroute3/entity/{id}']->methods);
        $this->assertEquals(['api'], $GETRoutes['api/admin/subroute3/entity/{id}']->action['middleware']);
        $wheres = $GETRoutes['api/admin/subroute3/entity/{id}']->wheres;
        $this->assertArrayHasKey('id', $wheres);
        $this->assertEquals('\d+', $wheres['id']);


        $this->assertArrayHasKey('api/admin123/{section}/index', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['api/admin123/{section}/index']->methods);
        $this->assertEquals(['api'], $GETRoutes['api/admin123/{section}/index']->action['middleware']);
        $wheres = $GETRoutes['api/admin123/{section}/index']->action['wheres'];
        $this->assertArrayHasKey('section', $wheres);
        $this->assertEquals('\w+', $wheres['section']);

        $this->assertArrayHasKey('sub/index', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['sub/index']->methods);
        $this->assertEquals('subindex1', $GETRoutes['sub/index']->action['as']);

        $this->assertArrayHasKey('sub/sub2/index', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['sub/sub2/index']->methods);
        $this->assertEquals('Sub\SubController@index', $GETRoutes['sub/sub2/index']->action['controller']);

        $this->assertArrayHasKey('api/model/export.{format}', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['api/model/export.{format}']->methods);
        $wheres = $GETRoutes['api/model/export.{format}']->wheres;
        $this->assertArrayHasKey('format', $wheres);
        $this->assertEquals('\w+', $wheres['format']);

        $this->assertArrayHasKey('included-route/index/{id}', $GETRoutes);
        $this->assertEquals(['GET', 'HEAD'], $GETRoutes['included-route/index/{id}']->methods);
        $this->assertEquals('myIndex', $GETRoutes['included-route/index/{id}']->action['as']);

    }

    public function testRegisterNull()
    {
        $this->assertFalse($this->yaml->register(null));
    }

    public function testRegisterMalformedData()
    {
        $this->assertFalse($this->yaml->register([
            'GET /' => null
        ]));
    }

    public function testGenerateYamlFromRoutes()
    {
        Route::get('/', 'HomeController@index')->name('home');
        Route::group(['prefix' => 'api'], function () {
            Route::get('/entity', 'Api\\EntityController@index')->name('entity.list');
            Route::post('/entity', 'Api\\EntityController@create')->name('entity.save');
            Route::get('/entity/{id}', 'Api\\EntityController@get')
                ->name('entity.get')
                ->where('id', '\d+');

            Route::group(['prefix' => '/article/{alias}', 'where' => ['alias' => '\w+']], function () {
                Route::get('index', 'ArticleController@index');
                Route::delete('', 'ArticleController@destroy');
            });

            Route::get('/sandbox/{param}', 'SandboxController@index')->name('sandbox');
        });
        $yaml = $this->yaml->generateYamlFromRoutes();
        $expected = [
            'GET / as home: HomeController@index',
            'GET /api/entity as entity.list: Api\\EntityController@index',
            'GET /api/entity/{id ~ \d+} as entity.get: Api\\EntityController@get',
            'GET /api/article/{alias ~ \w+}/index: ArticleController@index',
            'GET /api/sandbox/{param} as sandbox: SandboxController@index',
            'POST /api/entity as entity.save: Api\\EntityController@create',
            'DELETE /api/article/{alias ~ \w+}: ArticleController@destroy',
            'HEAD / as home: HomeController@index',
            'HEAD /api/entity as entity.list: Api\\EntityController@index',
            'HEAD /api/entity/{id ~ \d+} as entity.get: Api\\EntityController@get',
            'HEAD /api/article/{alias ~ \w+}/index: ArticleController@index',
            'HEAD /api/sandbox/{param} as sandbox: SandboxController@index',
        ];
        $joined = join("\n", $expected);
        $this->assertEquals($joined, $yaml);
    }
}
