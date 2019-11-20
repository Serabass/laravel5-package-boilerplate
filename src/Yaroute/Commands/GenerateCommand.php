<?php

namespace Serabass\Yaroute\Commands;

use Illuminate\Console\Command;
use Serabass\Yaroute\Yaroute;

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
    protected $description = 'Generates YAML config file based on registered routes and outputs it\'s contents to stdout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yaml = new Yaroute();
        $generateYamlFromRoutes = $yaml->generateYamlFromRoutes();
        echo $generateYamlFromRoutes;

        return $generateYamlFromRoutes;
    }
}
