<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
use Illuminate\Support\Facades\View;

class AuthController extends Controller
{
    /**
     * Retrieve the user for the given ID.
     *
     * @param  int  $id
     * @return Response
     */
    public function login()
    {
        $data = app('db')->select("SELECT * FROM user");
        return view('admin.login', ['name' => 'James']);
    }

}
