<?php


namespace App\Http\Middleware;

use Closure;

class CrossRequestMiddleware
{

    public function handle($request, Closure $next)
    {

        // $response = $next($request);

        // header("Access-Control-Allow-Origin: *");
        // header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

        $response = $next($request);
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
        $allow_origin = [
            'http://localhost:8000',
            //'http://localhost:8094',
        ];
        if (in_array($origin, $allow_origin)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
            $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
            $response->header('Access-Control-Allow-Credentials', 'true');
        }
        return $response;
    }

}
