<?php
/**
 * @link Specification of YAML 1.2 http://www.yaml.org/spec/1.2/spec.html
 */

namespace Serabass\Yaroute;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;

class Yaroute
{
    /**
     * Regular expression to parse full method string
     *
     * @example GET / as home uses guest
     * @example GET /info as info uses guest
     * @example GET /cabinet as cabinet uses auth
     * @example GET /logout uses auth
     */
    const FULL_REGEX =
        '%^(?:(?P<method>[\w|]+)\s+)' .
        '(?P<path>/.*?)' .
        '(?:\s+as\s+(?P<name>[\w.]+?))?' .
        '(?:\s+uses\s+(?P<middleware>[\w;:\s]+))?$%sim';

    /**
     * Regular expression to parse controller-action string
     *
     * @example HomeController@index
     * @example HomeController@info
     * @example UserController@cabinet
     */
    const ACTION_REGEX = '/^(?P<controller>[\w\\\\]+)@(?P<action>\w+)$/sim';

    /**
     * Regular expression to parse group string
     *
     * @example /
     * @example /api
     * @example /user as user uses auth
     * @example /logout uses auth
     * @example /login uses guest
     */
    const GROUP_REGEX =
        '%^(?P<prefix>/.*?)' .
        '(?:\s+as\s+(?P<name>[\w.]+?))?' .
        '(?:\s+uses\s+(?P<middleware>[\w;:\s]+))?$%sim';

    /**
     * Regular expression to parse uri parameter string
     *
     * @example {id}
     * @example {id ~ \d+}
     * @example {alias ~ [\w-]}
     * @example {path ~ .+}
     */
    const PARAM_REGEX =
        '/\{(?P<param>[\w?]+)' .
        '(?:\s+~\s+(?P<regex>.+?))?\}/sim';

    /**
     * Regular expression to parse uri parameter string
     *
     * @example +myMixinName
     * @example +myMixinName(Param)
     * @example +myMixinName(Param1, Param2)
     * @example +myMixinName(Param, OptionalParam = DefaultValue)
     */
    const MIXIN_REGEX = '/^\+(?P<name>\w+)(?:\((?P<params>.+?)\))?$/m';

    public $yamlPath;

    public $mixins = [];

    /**
     * @param $file
     *
     * @return Yaroute
     * @throws IncorrectDataException
     */
    public static function registerFile($file)
    {
        $yaml = new Yaroute();
        $file = $yaml->prepareFileName($file);
        $yaml->registerFileImpl($file);

        return $yaml;
    }

    /**
     * @param $string
     *
     * @return array|null
     */
    public function parseGroupString($string)
    {
        if (!preg_match(self::GROUP_REGEX, $string, $matches)) {
            return null;
        }

        $result = [
            'prefix' => $matches['prefix']
        ];

        if (isset($matches['middleware'])) {
            $result['middleware'] = preg_split('/\s*;\s*/', $matches['middleware']);
        }

        if (isset($matches['name'])) {
            $result['as'] = $matches['name'];
        }

        return $result;
    }

    /**
     * @param string $string
     *
     * @return array|null
     */
    public function parseActionString(string $string)
    {
        if (!preg_match(self::ACTION_REGEX, $string, $matches)) {
            return null;
        }

        return [
            'controller' => $matches['controller'],
            'action'     => $matches['action']
        ];
    }

    /**
     * @param $string
     *
     * @return array|null
     */
    public function parseRouteString($string)
    {
        if (!preg_match(self::FULL_REGEX, $string, $matches)) {
            return null;
        }

        $result = [
            'path' => $matches['path']
        ];

        if (!empty($matches['method'])) {
            $result['method'] = preg_split('/\s*\|\s*/', $matches['method']);
        }

        if (!empty($matches['name'])) {
            $result['name'] = $matches['name'];
        }

        if (!empty($matches['middleware'])) {
            $result['middleware'] = preg_split('/\s*;\s*/', $matches['middleware']);
        }

        return $result;
    }

    /**
     * @param $string
     * @param $value
     *
     * @return array|null
     * @throws IncorrectDataException
     */
    public function parseMixinString($string, $value)
    {
        if (!preg_match(self::MIXIN_REGEX, $string, $matches)) {
            return null;
        }

        $name = $matches['name'];
        $params = isset($matches['params']) ? $matches['params'] : null;

        $params = preg_split('/\s*,\s*/', $params);
        $paramsOrder = [];
        $parametersWithValues = [];

        foreach ($params as $param) {
            $chunks = preg_split('/\s*=\s*/', $param);
            switch (count($chunks)) {
                case 1:
                    list($paramName) = $chunks;
                    $parametersWithValues[$paramName] = null;
                    break;
                case 2:
                    list($paramName, $paramValue) = $chunks;
                    $parametersWithValues[$paramName] = $paramValue;
                    break;
                default:
                    throw new IncorrectDataException();
            }
            $paramsOrder[] = $paramName;
        }
        $callback = function (array $params) use ($parametersWithValues, $name, $value) {
            $result = [];
            foreach ($value as $url => $route) {
                $closure = function ($match) use ($parametersWithValues, $name, $value, $params) {
                    $name = $match['name'];

                    return isset($params[$name]) ? $params[$name] : $parametersWithValues[$name];
                };
                $url = preg_replace_callback('/\$\{(?P<name>\w+)}/', $closure, $url);
                if (is_string($route)) {
                    $route = preg_replace_callback('/\$\{(?P<name>\w+)}/', $closure, $route);
                } else {
                    array_walk_recursive($route, function (&$value, &$key) use ($closure) {
                        $key = preg_replace_callback('/\$\{(?P<name>\w+)}/', $closure, $key);
                        $value = preg_replace_callback('/\$\{(?P<name>\w+)}/', $closure, $value);
                    });
                    $this->register($route);
                }

                $result[$url] = $route;
            }

            return $result;
        };

        return [
            'name'        => $name,
            'params'      => $parametersWithValues,
            'paramsOrder' => $paramsOrder,
            'callback'    => $callback
        ];
    }

    /**
     * @param $url
     *
     * @return array
     */
    private function getWheres($url)
    {
        $wheres = [];
        $url = preg_replace_callback(self::PARAM_REGEX, function ($matches) use (&$wheres) {
            $param = $matches['param'];
            $paramNoQ = str_replace('?', '', $param);

            if (isset($matches['regex'])) {
                $regex = $matches['regex'];
                $wheres[$paramNoQ] = $regex;
            }

            return '{' . $param . '}';
        }, $url);

        return [
            'url'    => $url,
            'wheres' => $wheres
        ];
    }

    /**
     * @param array $arr
     *
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        if ([] === $arr) return false;

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param $file
     *
     * @return mixed
     */
    public function parseFile($file)
    {
        $this->yamlPath = $file;
        $contents = file_get_contents($this->yamlPath);

        return Yaml::parse($contents);
    }

    /**
     * @param $string
     *
     * @return string
     */
    private function prepareFileName($string)
    {
        if (!ends_with($string, '.yaml'))
            return $string . '.yaml';

        return $string;
    }

    /**
     * @param $value
     *
     * @throws IncorrectDataException
     */
    private function useMixin($value)
    {
        preg_match('/^(?P<name>\w+)(?:\((?P<params>.*?)\))?$/m', $value, $matches);
        $name = $matches['name'];
        $passedParams = preg_split('/\s*,\s*/', $matches['params']);
        $mixin = $this->mixins[$name];
        $paramsOrder = $mixin['paramsOrder'];
        $paramsForCallback = [];

        foreach ($paramsOrder as $index => $paramValue) {
            if (!empty($passedParams[$index])) {
                $paramsForCallback[$paramValue] = $passedParams[$index];
            } else {
                $paramsForCallback[$paramValue] = $mixin['params'][$paramValue];
            }
        }

        $result = $mixin['callback']($paramsForCallback);
        $this->register($result);
    }

    /**
     * @param $data
     *
     * @return bool
     * @throws IncorrectDataException
     */
    public function register($data): bool
    {
        if (is_null($data))
            return false;

        if (!$this->isAssoc($data)) {
            foreach ($data as $file) {
                $dir = dirname($this->yamlPath);
                self::registerFile($dir . '/' . $file);
            }
        }

        foreach ($data as $url => $value) {

            if ($mixin = $this->parseMixinString($url, $value)) {
                $this->mixins[$mixin['name']] = $mixin;
            }

            if ($groupMatches = $this->parseGroupString($url)) {
                $options = [];
                if (isset($groupMatches['middleware'])) {
                    $options['middleware'] = $groupMatches['middleware'];
                }

                $wheres = $this->getWheres($groupMatches['prefix']);

                $options['prefix'] = $wheres['url'];
                $options['wheres'] = $wheres['wheres'];

                Route::group($options, function () use ($value, $url) {
                    $this->register($value);
                });
            }

            if ($url === '+') {
                if (is_string($value)) {
                    $this->useMixin($value);
                } elseif (is_array($value) && !$this->isAssoc($value)) {
                    foreach ($value as $index => $item) {
                        $this->useMixin($item);
                    }
                }
            }

            if ($urlMatches = $this->parseRouteString($url)) {
                $wheres = $this->getWheres($urlMatches['path']);

                $options['path'] = $wheres['url'];
                $options['wheres'] = $wheres['wheres'];

                if (is_string($value)) {

                    if ($actionMatches = $this->parseActionString($value)) {
                        foreach ($urlMatches['method'] as $method) {
                            /**
                             * @var $route Route
                             */
                            $route = Route::$method($options['path'], $value);
                            if (isset($urlMatches['name'])) {
                                $route->name($urlMatches['name']);
                            }

                            if (isset($urlMatches['middleware'])) {
                                $route->middleware(join(',', $urlMatches['middleware']));
                            }

                            if (isset($options['wheres']) && count($options['wheres']) > 0) {
                                foreach ($options['wheres'] as $param => $regex) {
                                    $route->where($param, $regex);
                                }
                            }
                        }
                    }
                } elseif (is_array($value)) {
                    $method = isset($value['method']) ? $value['method'] : 'GET';
                    $action = $value['action'];
                    if ($actionMatches = $this->parseActionString($action)) {
                        $action = $actionMatches['action'];
                        $controller = $actionMatches['controller'];
                    } else {
                        $action = $value['action'];
                        $controller = $value['controller'];
                    }

                    /**
                     * @var $route Route
                     */
                    $route = Route::$method($urlMatches['path'], $controller . '@' . $action);
                    if (isset($urlMatches['name'])) {
                        $route->name($urlMatches['name']);
                    }
                    if (isset($urlMatches['middleware'])) {
                        $route->middleware(join(',', $urlMatches['middleware']));
                    }
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $file
     *
     * @throws IncorrectDataException
     */
    public function registerFileImpl($file)
    {
        $file = $this->prepareFileName($file);
        $options = $this->parseFile($file);
        $this->register($options);
    }

    /**
     * @return string
     */
    public function generateYamlFromRoutes()
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD'];

        /**
         * @var $routes RouteCollection
         */
        $routes = Route::getRoutes();
        $result = [];

        foreach ($methods as $method) {
            $data = $routes->get($method);

            foreach ($data as $url => $options) {
                $controller = $options->action['controller'];
                $where = $options->wheres;

                $uri = preg_replace_callback('/\{(?P<param>[\w]+)\??\}/m', function ($m) use ($url, $where) {
                    $param = $m['param'];

                    if (isset($where[$param])) {
                        return '{' . $param . ' ~ ' . $where[$param] . '}';
                    }

                    return $m[0];
                }, $options->uri);

                if (isset($options->action['as'])) {
                    $as = $options->action['as'];
                    $uri = $uri . " as $as";
                }

                if (!starts_with($url, '/')) {
                    $uri = '/' . $uri;
                }

                $row = "$method ${uri}: $controller";
                $result[] = $row;
            }
        }

        return join("\n", $result);
    }
}
