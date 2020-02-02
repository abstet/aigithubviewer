<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository;

class GithubRepoRemove extends Command
{
    protected $signature = 'github:repo:remove {owner/repo}';
    protected $description = 'Remove <owner/repo> commits from DB';

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
        $stat = \App\Repository::remove($owner, $repo);
        $this->warn(json_encode($stat));
    }
}
