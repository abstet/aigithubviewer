<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Commits;

class Repository extends Model
{
    public $timestamps = false;

    protected $table = 'repos',
        $primaryKey = 'repo_id',
        $fillable = ["repo", "owner"],
        $hidden = [],
        $casts = [];

    /**
     * @param $owner
     * @param $repo
     * @return array
     */
    static public function list() {
        try {
            $collection = \App\Repository::all();
            $data = array();
            foreach ($collection as $repo) {
                $data[] = array("repo_id" => $repo->repo_id,
                    "owner" => $repo->owner,
                    "repo" => $repo->repo);
            }
            return array("status" => "success",
                "data" => $data);
        } catch (\Throwable $t) {
            return array("status" => "error",
                "message" => $t->getMessage());
        }
    }

    /**
     * @param $owner
     * @param $repo
     * @return array
     */
    static public function get($owner, $repo) {
        try {
            $Repository = Repository::where('owner', $owner)->where('repo', $repo)->first();
            if (isset($Repository->repo_id)) {
                return \App\Commits::where('repo_id', $Repository->repo_id)->paginate(20);
            } else {
                return array("status" => "error",
                    "message" => "Repo not found");
            }
        } catch (\Throwable $t) {
            return array("status" => "error",
                "message" => $t->getMessage());
        }
    }

    /**
     * @param $owner
     * @param $repo
     * @return array
     */
    static public function clear($owner, $repo)
    {
        try {
            $Repository = Repository::where('owner', $owner)->where('repo', $repo)->first();
            $commits = \App\Commits::where('repo_id', $Repository->repo_id)->delete();
            return array("status" => "success",
                "message" => "Cleared $commits commit(s)");

        } catch (\Throwable $t) {
            return array("status" => "error",
                "message" => $t->getMessage());
        }
    }

    /**
     * @param $owner
     * @param $repo
     * @return array
     */
    static public function remove($owner, $repo)
    {
        try {
            $Repository = Repository::where('owner', $owner)->where('repo', $repo)->first();
            $commits = \App\Commits::where('repo_id', $Repository->repo_id)->delete();
            if (Repository::where('repo_id', $Repository->repo_id)->delete()) {
                return array("status" => "success",
                    "message" => "Removed with $commits commit(s)");
            }
        } catch (\Throwable $t) {
            return array("status" => "error",
                "message" => $t->getMessage());
        }
    }

    /**
     * @param $owner
     * @param $repo
     * @return array
     */
    static public function import($owner, $repo)
    {
        $stat = array();

        // fetch from GitHub
        $jCommits = array();
        $githubClient = new \Github\Client();
        try {
            $jCommits = $githubClient->api("repo")->commits()->all($owner, $repo, array("per_page" => 100));
        } catch (\Throwable $t) {
            return array("status" => "error",
                "message" => $t->getMessage());
        }

        // Repo: select or create new
        $Repository = Repository::where('owner', $owner)->where('repo', $repo)->first();
        if (!isset($Repository->repo_id)) {
            $Repository = new Repository();
            $Repository->owner = $owner;
            $Repository->repo = $repo;
            $Repository->save();
            $stat["newRepo"] = 1;
        }
        $repoId = $Repository->repo_id;
        @$stat["repoId"] = $repoId;
        @$stat["status"] = "success";

        // Commits: import
        if (count($jCommits)) {
            $stat["commitsImported"] = $stat["commitsFetched"] = 0;
            foreach ($jCommits as $jCommit) {
                if (!\App\Commits::where('sha', $jCommit["sha"])->first()) {
                    $commit = new \App\Commits();
                    $commit->sha = $jCommit["sha"];
                    $commit->repo_id = $repoId;
                    $commit->json = json_encode($jCommit, true);
                    $commit->save();
                    ++$stat["commitsImported"];
                }
                ++$stat["commitsFetched"];
            }
        }

        return $stat;
    }
}
