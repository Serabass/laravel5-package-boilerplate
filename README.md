**Yaroute** is a simple route-organizer that uses YAML to register routes in Laravel.

1. [Installation](#installation)
2. [Docs](#docs)
3. [Examples](#examples)
4. [Usage](#usage)
5. [Mixins](#mixins)
6. [Regular Expressions presets](#regular-expressions-presets)
7. [Generating YAML](#generating-yaml)

# Installation
` $ composer require serabass/yaroute `

# Docs
The format of simple route must look like `<METHOD> /<PATH> [as <NAME>] [uses <MIDDLEWARE>]: <ACTION>`

The format of group must look like:

```yaml
<PREFIX> [uses <MIDDLEWARE>]:
  <METHOD> /<PATH> [as <NAME>]: <ACTION>
```

Groups can be nested

# Examples

```yaml
GET / as home uses guest: HomeController@index
```
This simple config creates a route with url `/`, named `home`, that uses `guest` middleware and executes
    `HomeController@index` action


```yaml
/api uses api:
  GET /entity: EntityController@list
```

This simple config creates a group that uses `api` middleware and contains `/entity` route

You can see all examples in [Examples directory](./examples).

# Usage

1. Create your `.yaml` file, e.g. `api.yaml` in any directory (e.g. `routes`)
2. Write just one line in your `routes/web.php` or `routes/api.php` and you can register all routes in your `.yaml`

```php
\Serabass\Yaroute\Yaroute::registerFile(__DIR__ . '/api.yaml');
```

Simple group config:
```yaml
/api uses api:
  GET /entity: EntityController@list
  GET /entity/{id ~ \d+}: EntityController@get
  POST /entity/{id ~ \d+}: EntityController@save

  GET /entity/{id}/getComments:
    action: EntityController@getComments

  /admin:
    GET /index: AdminController@index
    GET /entity/{id ~ \d+}: AdminController@entity
    /subroute:
      GET /entity/{id ~ \d+}: AdminController@entity
      GET /data/{alias ~ .+}: AdminController@getData
```

It'll be converted to this:
```php
    Route::group(['prefix' => 'api', 'uses' => 'api'], function () {
        Route::get('entity', 'EntityController@list');
        Route::get('entity/{id}', 'EntityController@get')->where('id', '\d+');
        Route::post('entity/{id}', 'EntityController@save')->where('id', '\d+');
        Route::get('entity/{id}/getComments', 'EntityController@getComments')->where('id', '\d+');
        Route::group('admin', function () {
            Route::get('index', 'AdminController@index');
            Route::get('entity/{id}', 'AdminController@entity')->where('id', '\d+');
            Route::group('subroute', function () {
                Route::get('entity/{id}', 'AdminController@entity')->where('id', '\d+');
                Route::get('data/{alias}', 'AdminController@getData')->where('alias', '.+');
            });
        });
    });
```

You can see all examples in [Examples directory](./examples).

## Mixins

```yaml
+myResourceMixin(ControllerName, Alias = myResource):
  GET / as ${Alias}.list: ${ControllerName}@list
  /{id ~ \d+} as .${Alias}.element:
    GET / as .show: ${ControllerName}@show
    POST / as .update: ${ControllerName}@update
    PUT / as .create: ${ControllerName}@create
    DELETE / as .delete: ${ControllerName}@destroy

/entity as entityResource:
  +: myResourceMixin(MyEntityController, myEntity)
```

# Regular Expressions presets

You can create predefined names for RegExps that using in uri params.
It's simple to do. See the example below:

```yaml
~hex: '[a-f0-9]+'
~urlAlias: '[A-Z-]+'

# Note: all regexes must be quoted because yaml-parser recognizes [...] as array

```

And if you want to use it in route you can write as:
```yaml
GET /entity/{id ~hex} as entity: EntityController@show
```

Please note that there is no space. It's important. If you placed a space char there, 
the value will passed as plain regex `/numeric/`

Yaroute has few prefedined aliases:

* **numeric**: `\d+`
* **hex**: `[\da-fA-F]+`
* **alias**: `[\w-]+`
* **boolean**: `[01]`

# Generating YAML

Also you can generate new YAML document (based on registered routes in app)
 with `$ php artisan yaroute:generate`.
It will be printed to stdout so you can pipe it to needed file, e.g.:

`$ php artisan yaroute:generate > routes/api.yaml`
