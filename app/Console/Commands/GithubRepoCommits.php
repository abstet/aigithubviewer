<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository;

class GithubRepoCommits extends Command
{
    protected $signature = 'github:repo:commits {owner/repo}';
    protected $description = 'Show latest commits';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // args from CLI
        $args = $this->arguments();
        if (!isset($args['owner/repo']) || strpos($args['owner/repo'], "/") === false) {
            $this->error("Invalid format for <owner/repo>");
            return;
        }
        list($owner, $repo) = explode('/', $args['owner/repo']);

        // data from repo
        $data = \App\Repository::get($owner, $repo)->toArray();
        if (isset($data["data"])) {
            $commits = $data["data"];
            $rows = array();
            foreach ($commits as $commit) {
                if (isset($commit["json"])) {
                    $json = json_decode($commit["json"], true);
                    if (isset($json["sha"])
                        && isset($json["commit"]["author"]["name"])
                        && isset($json["commit"]["author"]["date"])) {
                        $rows[] = sprintf("%s\t%-20s\t%s",
                            $json["sha"],
                            $json["commit"]["author"]["name"],
                            $json["commit"]["author"]["date"]);
                    }
                }
            }
            echo join("\n", $rows);
            if (isset($data["total"])) {
                $this->info("\n\t... latest " . count($rows) . " from " . $data["total"]);
            }
        } else if (isset($data["message"])) {
            $this->warn($data["message"]);
        }
    }
}
