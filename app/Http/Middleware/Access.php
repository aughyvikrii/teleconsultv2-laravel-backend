<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class Access
{

    private $admin = [
        '1'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role=null)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'status' => false,
                    'message' => 'Token is Invalid'
                ], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([
                    'status' => false,
                    'message' => 'Token is Expired'
                ], 401);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Authorization Token not found'
                ], 401);
            }
        }
        
        if(!$role && !$user) return redirect('need_login');
        else if(!$role) return $next($request);
        else if(!$role_id = @$this->{$role}) dd("ROLE TIDAK VALID");
        else {
            if(!in_array($user->lid, $role_id)) abort(404);
            else return $next($request);
        }
    }
}
