<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Events\UploadEvent;
use App\Jobs\ExampleJob;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

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
        // dispatch(new ExampleJob(['user'=>'zg']));
        
        // $gotoJob = (new ExampleJob(['user'=>'zg']))->onQueue('high');
        // app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($gotoJob);
        // dispatch($gotoJob);
        return view('admin.login', ['name' => 'James']);
    }

}
