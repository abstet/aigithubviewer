<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use \App\Repository;
use Illuminate\Http\Request;;

Route::get('/', function () {
    return view('index');
});

// REST
Route::match(['get', 'post'], '/github/repo/{owner}/{repo}', function ($owner, $repo) {
    $r  =\App\Repository::get($owner, $repo);
    $r["status"] == "success" ? $code = 200 : $code = 400;
    return response()->json($r);
});

Route::match(['get', 'post'], '/github/repo', function () {
    $r = \App\Repository::list();
    $r["status"] == "success" ? $code = 200 : $code = 400;
    return response()->json($r);
});

Route::put('/github/repo/{owner}/{repo}', function ($owner, $repo) {
    $r = \App\Repository::import($owner, $repo);
    $r["status"] == "success" ? $code = 201 : $code = 400;
    return response()->json($r, $code);
});

Route::delete('/github/repo/{owner}/{repo}', function ($owner, $repo) {
    $r = \App\Repository::remove($owner, $repo);
    $r["status"] == "success" ? $code = 200 : $code = 400;
    return response()->json($r, $code);
});

Route::post('/github/repo/commits', function (Request $request) {
    $r = \App\Repository::removeCommits($request->post());
    $r["status"] == "success" ? $code = 200 : $code = 400;
    return response()->json($r, $code);
});

Route::patch('/github/repo/{owner}/{repo}', function ($owner, $repo) {
    $r = \App\Repository::clear($owner, $repo);
    $r["status"] == "success" ? $code = 200 : $code = 400;
    return response()->json($r, $code);
});


