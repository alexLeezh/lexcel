<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
class UserController extends Controller
{
    /**
     * Retrieve the user for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function show()
    {
        $results = app('db')->select("SELECT * FROM user");

        event(new ExampleEvent(['user'=>'zg']));
        dispatch(new ExampleJob);
        return json_encode($results);
    }
}
