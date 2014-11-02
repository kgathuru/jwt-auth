<?php

namespace Tymon\JWTAuth\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Events\Dispatcher;
use Tymon\JWTAuth\JWTAuth;

class AuthMiddleware implements Middleware {

    /**
     * Create a new Middleware instance
     * 
     * @param ResponseFactory  $response 
     * @param Dispatcher  $events   
     * @param JWTAuth  $auth     
     */
    public function __construct(ResponseFactory $response, Dispatcher $events, JWTAuth $auth)
    {
        $this->response = $response;
        $this->events = $events;
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! $token = $this->auth->getToken($request) )
        {
            return $this->respond('tymon.jwt.absent','token_not_provided', 400);
        }

        try
        {
            $user = $this->auth->toUser($token);
        }
        catch(TokenExpiredException $e)
        {
            return $this->respond('tymon.jwt.expired', 'token_expired', 401, [$e]);
        }
        catch(JWTException $e)
        {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', 400, [$e]);
        }

        if (! $user)
        {
            return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }

    /**
     * Fire event and return the response
     * 
     * @param  string   $event  
     * @param  string   $error  
     * @param  integer  $status 
     * @param  array    $payload 
     * @return mixed 
     */
    protected function respond($event, $error, $status, $payload = [])
    {
        $response = $this->events->fire($event, $payload, true);
        return $response ?: $this->response->json(['error' => $error], $status);
    }

}