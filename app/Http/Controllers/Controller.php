<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @param $message
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseData($message,$code, $data = null)
    {
        $res = array(
            'code' => $code,
            'message' => $message,
        );
        if (isset($data)) {
            $res['data'] = $data;
        }
        return response()->json($res);

    }
}
