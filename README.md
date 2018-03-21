**Yaroute** is a simple route-organizer that uses YAML to register route in Laravel.

# Installation
` $ composer require serabass/yaroute `

# Examples

```yaml
    GET / as home uses guest: HomeController@index
```
This simple config creates a route with url `/`, named `home`, uses `guest` middleware and executes
    `HomeController@index` action
    
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
    ^/subroute3:
      GET /entity/{id ~ \d+}: AdminController@entity
      GET /data/{alias ~ .+}: AdminController@entity
```
    
TODO:
1. Создать команду artisan yaroute:make api/web, которая сгеренирует yaml-документ.
    В идеале это будет генератор документов на основании текущих роутов.
...
