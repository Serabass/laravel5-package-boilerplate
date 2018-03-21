**Yaroute** is a simple route-organizer that uses YAML to register routes in Laravel.

# Installation
` $ composer require serabass/yaroute `

# Docs
The format of simple route must look like `<METHOD> /<PATH> [as <NAME>] [uses <MIDDLEWARE>]: <ACTION>`

The format of group must look like:

```yaml
^<PREFIX> [uses <MIDDLEWARE>]:
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
^/api uses api:
  GET /entity: EntityController@list
```

This simple config creates a group that uses `api` middleware and contains `/entity` route

# Usage

```php
\Serabass\Yaroute\Yaroute::registerFile(__DIR__ . '/api.yaml');
```

Simple group config:
```yaml
^/api uses api:
  GET /entity: EntityController@list
  GET /entity/{id ~ \d+}: EntityController@get
  POST /entity/{id ~ \d+}: EntityController@save

  GET /entity/{id}/getComments:
    action: EntityController@getComments

  ^/admin:
    GET /index: AdminController@index
    GET /entity/{id ~ \d+}: AdminController@entity
    ^/subroute:
      GET /entity/{id ~ \d+}: AdminController@entity
      GET /data/{alias ~ .+}: AdminController@entity
```

Also you can generate new YAML document with `$ php artisan yaroute:generate`.
It will be printed to stdout and you can pipe it to needed file, e.g.:

`$ php artisan yaroute:generate > routes/api.yaml`
