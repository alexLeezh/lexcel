<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    
    public function up(Request $res)
    {
        //处理队列
        event(new ExampleEvent($res));

        return response()->json($res);
    }

    public function ls(Request $res)
    {
        return response()->json($res);
    }

    public function delete($id)
    {
        $results = app('db')->select("SELECT * FROM user");

        event(new ExampleEvent(['user'=>'zg']));
        return json_encode($results);
    }

    public function generate()
    {
        $results = app('db')->select("SELECT * FROM user");

        event(new ExampleEvent(['user'=>'zg']));
        return json_encode($results);
    }
}
