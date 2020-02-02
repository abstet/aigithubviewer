<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GithubRepoImport extends Command
{
    protected $signature = 'github:repo:import {owner/repo}';
    protected $description = 'Import repo';
    protected $help = "Import <owner/repo> from GitHub to local DB";

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $args = $this->arguments();
        if (!isset($args['owner/repo']) || strpos($args['owner/repo'], "/") === false) {
            $this->error("Invalid format for <owner/repo>");
            return;
        }
        list($owner, $repo) = explode('/', $args['owner/repo']);
        $this->info("$owner/$repo > DB");

        $stat = \App\Repository::import($owner, $repo);
        $this->warn(json_encode($stat));
    }
}
