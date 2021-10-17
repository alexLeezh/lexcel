<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Events\UploadEvent;
use App\Jobs\ExampleJob;
use App\Jobs\UploadFileDataJob;
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
        // Redis::set('name', 'Taylor');
        $user =  Auth::getUser();

        print_r($user);exit;
        // $batch = app('session')->get('report_hash');
        // $school_type = app('session')->get('school_type');
        // var_dump($batch);exit;
        // dispatch(new UploadFileDataJob(['id'=>1,'status'=>2]));
        // app('session')->put('key','value');
        $sessionk = app('session')->get('key');
        var_dump($sessionk);
        // dispatch(new ExampleJob(['user'=>'zg']));
        
        // $gotoJob = (new ExampleJob(['user'=>'zg']))->onQueue('high');
        // app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($gotoJob);
        // dispatch($gotoJob);
        return view('admin.login', ['name' => 'James']);
    }

}
