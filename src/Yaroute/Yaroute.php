<?php
/**
 * @link Specification of YAML 1.2 http://www.yaml.org/spec/1.2/spec.html
 */
namespace Serabass\Yaroute;

use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;

class Yaroute
{
    const FULL_REGEX =
        '%^(?:(?P<method>[\w|]+)\s+)?(?P<path>/.*?)(?:\s+as\s+(?P<name>[\w.]+?))?(?:\s+uses\s+(?P<middleware>[\w,\s]+))?$%sim';

    const ACTION_REGEX = '/^(?P<controller>[\w\\\\]+)@(?P<action>\w+)$/sim';

    const GROUP_REGEX = '%^\^(?P<prefix>/.+?)(?:\s+uses\s+(?P<middleware>[\w,:\s]+))?$%sim';

    const PARAM_REGEX = '/\{(?P<param>[\w?]+)(?:\s+~\s+(?P<regex>.+?))?\}/sim';

    public $yamlPath;

    public static function registerFile($file) {
        $yaml = new Yaroute();
        $file = $yaml->prepareFileName($file);
        $yaml->registerFileImpl($file);
        return $yaml;
    }

    public function parseGroupString($string)
    {
        if (!preg_match(self::GROUP_REGEX, $string, $matches)) {
            return null;
        }

        $result = [
            'prefix' => $matches['prefix'],
        ];

        if (isset($matches['middleware'])) {
            $result['middleware'] = $matches['middleware'];
        }

        return $result;
    }

    public function parseActionString(string $string)
    {
        if (!preg_match(self::ACTION_REGEX, $string, $matches)) {
            return null;
        }

        return [
            'controller' => $matches['controller'],
            'action' => $matches['action']
        ];
    }

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
        } else {
            $result['method'] = ['GET']; // or ALL
        }

        if (!empty($matches['name'])) {
            $result['name'] = $matches['name'];
        }

        if (!empty($matches['middleware'])) {
            $result['middleware'] = preg_split('/\s*,\s*/', $matches['middleware']);
        }

        return $result;
    }

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
            'url' => $url,
            'wheres' => $wheres
        ];
    }

    private function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function parseFile($file)
    {
        $this->yamlPath = $file;
        $contents = file_get_contents($this->yamlPath);
        return Yaml::parse($contents);
    }

    private function prepareFileName($string)
    {
        if (!ends_with($string, '.yaml'))
            return $string . '.yaml';

        return $string;
    }

    public function register($data)
    {
        if (is_null($data))
            return;

        if (!$this->isAssoc($data)) {
            foreach ($data as $file) {
                $dir = dirname($this->yamlPath);
                self::registerFile($dir . '/' .$file);
            }
        }

        foreach ($data as $url => $value) {

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

                    $route = Route::$method($urlMatches['path'], $controller . '@' . $action);
                    if (isset($urlMatches['name'])) {
                        $route->name($urlMatches['name']);
                    }
                    if (isset($urlMatches['middleware'])) {
                        $route->middleware(join(',', $urlMatches['middleware']));
                    }

                } else {
                    dump(12321312);
                    die;
                }
            }
        }
    }

    public function registerFileImpl($file)
    {
        $file = $this->prepareFileName($file);
        $options = $this->parseFile($file);
        $this->register($options);
    }

}
