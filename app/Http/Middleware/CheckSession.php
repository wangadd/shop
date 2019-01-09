<?php

namespace App\Http\Middleware;

use Closure;

class CheckSession
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
        if(empty($_COOKIE['uid'])){
            echo "您还没有登录，正在为您跳转至登陆页面";
            header("refresh:2;url=/userlogin");
            exit;
        }
        if(empty($_COOKIE['token'])){
            exit('非法请求');
            header("refresh:2;url=/userlogin");
            exit;
        }
        if($_COOKIE['token'] != $request->session()->get('u_token')){
           echo "非法请求";
            header("refresh:2;url=/userlogin");
            exit;
        }

        return $next($request);

    }
}
