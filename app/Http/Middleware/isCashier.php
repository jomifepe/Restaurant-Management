<?php

namespace App\Http\Middleware;

use Closure;

class isCashier
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
        
        if (auth()->guard('api')->user()->type !== 'cashier') {
            return response('Unauthorized', '401');
        }

        return $next($request);
    }
}
