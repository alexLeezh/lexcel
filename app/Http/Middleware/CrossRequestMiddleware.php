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
        
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
        $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
        $response->header('Access-Control-Allow-Methods', '*');
        $response->header('Access-Control-Allow-Credentials', 'true');
        return $response;
    }

}
