<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        '*',
        '/pay/alipay/notify_url',
        '/weixin/valid',
        '/weixin/groupsending',
        '/auth',
        '/auth/wxpmmedia',
        '/auth/wxpmmedia/{id}',
        '/auth/wxuser/send',
        '/weixin/getmsg',
        '/weixin/pay/find',
        '/curl',
        '/kaoshi',

    ];
}
