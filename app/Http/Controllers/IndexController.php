<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
class IndexController extends Controller
{
    /**
     * Retrieve the user for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function import()
    {
        $results = app('db')->select("SELECT * FROM user");
        dispatch(new ExampleJob(['user'=>'zg']));
        // event(new ExampleJob(['user'=>'zg']));
        return json_encode($results);
    }

    public function export()
    {
        $results = app('db')->select("SELECT * FROM user");

        event(new ExampleEvent(['user'=>'zg']));
        return json_encode($results);
    }
}
