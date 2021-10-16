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
        $q =  [
  0 => 3,
  1 => '慈溪市周巷职业高级中学',
  2 => '10.323:1',
  3 => '12:1',
  4 => '不达标',
  8 => '0',
  9 => '7500',
  10 => '不达标',
  5 => '0',
  6 => '90%',
  7 => '不达标',
];
        $user = Redis::get('name');
        print_r($q);
        ksort($q);
        print_r($q);exit;
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
