<?php

namespace App\Http\Controllers;
use App\Events\ExampleEvent;
use App\Jobs\ExampleJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        try {
            $user = \App\Models\User::where('name', $request->input('username'))
                ->where('password', $request->input('password'))->first();
            if (!$token = Auth::login($user)) {
                $response['code']     = '5000';
                $response['errorMsg'] = '系统错误，无法生成令牌';
            } else {
                $response['data']['user_id']      = strval($user->id);
                $response['data']['access_token'] = $token;
                $response['data']['expires_in']   = strval(time() + 86400);
            }
        } catch (QueryException $queryException) {
            $response['code'] = '5002';
            $response['msg']  = '无法响应请求，服务端异常';
        }

        return response()->json($response);
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
        $response = array('code' => '0');

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
        $response = array('code' => '0');

        if (!$token = Auth::refresh(true, true)) {
            $response['code']     = '5000';
            $response['errorMsg'] = '系统错误，无法生成令牌';
        } else {
            $response['data']['access_token'] = $token;
            $response['data']['expires_in']   = strval(time() + 86400);
        }

        return response()->json($response);
    }
}
