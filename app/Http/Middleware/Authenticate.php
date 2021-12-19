<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

use Closure;

use App\Models\Service;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        $valid_key = "azo";
        $getKeyValue = $request->bearerToken();
        $storedKeyValue = Service::where('service_name',$request->service)->first();

        if ($storedKeyValue) {
            if ($getKeyValue==$storedKeyValue->api_key) {
                return $next($request);
            } else {
                echo "Authentication is failed.";
                exit();
            }
        } else {
            echo "Authentication is failed.";
            exit();
        }

    }
}
