<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * 登录
     * http://localhost:8008/login
     * @author admin
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Http\Response;
     */
    public function login(Request $request)
    {
        $response = array('code' => '0');
        Log::info($request->input('username'));
        try {
            $user = \App\Models\User::where('name', $request->input('username'))
                ->where('password', $request->input('password'))->first();
                Log::info($user);
            if (!$user) {
                $response['code']     = '5002';
                $response['message'] = '账号密码错误';
                return response()->json($response);
            }
                Log::info($user);
            if (!$token = Auth::login($user)) {
                $response['code']     = '5000';
                $response['message'] = '系统错误，无法生成令牌';
            } else {
                $response['data']['user_id']      = strval($user->id);
                $response['data']['access_token'] = $token;
                $response['data']['expires_in']   = strval(time() + 86400);
            }
        } catch (QueryException $queryException) {
            $response['code'] = '5002';
            $response['message']  = '无法响应请求，服务端异常';
        }

        return response()->json($response);
    }

    /**
     * 注册账号
     * @param  Request $request 
     * @return [type]          
     */
    public function register(Request $request)
    {
        $postData = $request->input();

        //去重
        if (!$postData['username'] || !$request->input('password')) {
            $response['code']     = '1';
            $response['message'] = '参数必填！';
            return response()->json($response);
        }

        if (\App\Models\User::where('name', $request->input('username'))
                ->first()) {
            $response['code']     = '1';
            $response['message'] = '账号已存在！';
            return response()->json($response);
        }

        $postData['name'] = $postData['username'];
        $postData['created_at'] = date('Y-m-d H:i:s',time());
        unset($postData['username']);
        if (!DB::table('user')->insertGetId($postData)) {
            $response['code']     = '1';
            $response['message'] = '创建失败，请稍后重试！';
            return response()->json($response);
         }

        $response['code']     = 0;
        $response['message'] = '恭喜你账号创建成功！';
        return response()->json($response);
    }

    /**
     * 修改密码
     * @param  Request $request 
     * @return [type]          
     */
    public function modfiy(Request $request)
    {
        $postData = $request->input();

        $user = \App\Models\User::where('name', $request->input('username'))
                ->where('password', $request->input('password'))->first();
        if (!$user) {
            $response['code']     = '1';
            $response['message'] = '账号或密码错误！';
            return response()->json($response);
        }

        $updateData['password'] = $postData['newpwd'];
        $updateData['updated_at'] = date('Y-m-d H:i:s',time());

        if (!DB::table('user')->where('id',$user->id)->update($updateData)) {
            $response['code']     = '1';
            $response['message'] = '修改失败，请稍后重试！';
            return response()->json($response);
         }

        $response['code']     = 0;
        $response['message'] = '密码修改成功！';
        return response()->json($response);
    }

    /**
     * 获取用户列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function ls(Request $request)
    {
        $user =  Auth::getUser();
        if ($user->id !=1) {
            // $response['code']     = 1;
            // $response['message'] = '无权限查看！';
            // return response()->json($response);
            return $this->responseData('succ',0, null);
        }

        $results = app('db')->select("SELECT name,email,created_at FROM user");
        return $this->responseData('succ',0, $data = $results);
    }

    /**
     * 用户登出
     * http://localhost:8008/api/v1/logout
     * @author AdamTyn
     *
     * @return \Illuminate\Http\Response;
     */
    public function logout()
    {
        $response = array('code' => '0','message'=>'succ');

        Auth::invalidate(true);

        return response()->json($response);
    }

    /**
     * 更新用户Token
     * http://localhost:8008/api/v1/refreshToken
     * @author AdamTyn
     *
     * @param \Illuminate\Http\Request;
     * @return \Illuminate\Http\Response;
     */
    public function refreshToken()
    {
        $response = array('code' => '0','message'=>'succ');

        if (!$token = Auth::refresh(true, true)) {
            $response['code']     = '5000';
            $response['message'] = '系统错误，无法生成令牌';
        } else {
            $response['data']['access_token'] = $token;
            $response['data']['expires_in']   = strval(time() + 86400);
        }

        return response()->json($response);
    }
}
