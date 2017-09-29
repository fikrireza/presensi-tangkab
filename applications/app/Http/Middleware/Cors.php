<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return $next($request)
        //   ->header('Access-Control-Allow-Origin', '*')
        //   ->header('Access-Control-Allow-Method', 'GET, POST')
        //   ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');


        // $response = $next($request);
        // $IlluminateResponse = 'Illuminate\Http\Response';
        // $SymfonyResopnse = 'Symfony\Component\HttpFoundation\Response';
        // $headers = [
        //     'Access-Control-Allow-Origin' => '*',
        //     'Access-Control-Allow-Methods' => 'POST, GET',
        //     'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        // ];
        //
        // if($response instanceof $IlluminateResponse) {
        //     foreach ($headers as $key => $value) {
        //         $response->header($key, $value);
        //     }
        //     return $response;
        // }
        //
        // if($response instanceof $SymfonyResopnse) {
        //     foreach ($headers as $key => $value) {
        //         $response->headers->set($key, $value);
        //     }
        //     return $response;
        // }
        //
        // return $response;

        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-XSRF-TOKEN');
        return $response;
    }
}
