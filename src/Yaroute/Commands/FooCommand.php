<?php

namespace Serabass\Yaroute\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class FooCommand extends Command
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
        $routes = Route::getRoutes();
    }
}
