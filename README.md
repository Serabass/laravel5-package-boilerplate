**Yaroute** is a simple route-organizer that uses YAML to register route in Laravel.

# Installation
` $ composer require serabass/yaroute `

# Examples

```yaml
    GET / as home [guest]: HomeController@index
```
This simple config creates a route with url `/`, named `home`, uses `guest` middleware and executes
    `HomeController@index` action
    
    
    
TODO:
1. Создать команду artisan yaroute:make api/web, которая сгеренирует yaml-документ.
    В идеале это будет генератор документов на основании текущих роутов.
...