<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commits extends Model
{
    public $timestamps = false,
        $keyType = "string";

    protected $table = "commits",
        $fillable = ["repo_id", "sha", "json"],
        $hidden = [],
        $casts = [];
}
