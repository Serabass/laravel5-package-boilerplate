<?php

namespace Serabass\Yaroute\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class GenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yaroute:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates YAML config file in routes directory based on registered routes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $methods = ['GET', 'POST'];
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

                $row = "$method $uri: $controller";
                $result[] = $row;
            }
        }
        echo join("\n", $result);
    }
}
