<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository;

class GithubRepoList extends Command
{
    protected $signature = 'github:repo:list';
    protected $description = 'Imported repos list';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $list = \App\Repository::list();
        if (isset($list["status"])
            && $list["status"] == "success"
            && $list["data"]) {

            $repos = array();
            foreach ($list["data"] as $repo) {
                $repos[] = $repo["owner"]."/".$repo["repo"];
            }
            echo join("\n", $repos);
        } else {
            $this->warn(json_encode($list));
        }
    }
}
