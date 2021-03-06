<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     * 这里是5.2新加的,在5.1里web下面的中间件都在上面那个数组里
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,     //这个名字太误导了,并不是guest账户可以登录的页面.而是登陆后的帐号就跳转的页面.
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        //权限方面
//        'role' => \Zizaco\Entrust\Middleware\EntrustRole::class,
//        'permission' => \Zizaco\Entrust\Middleware\EntrustPermission::class,
//        'ability' => \Zizaco\Entrust\Middleware\EntrustAbility::class,

        //上面那个是Entrust自带的,下面是作者自己改写的
        //自定义检测权限
        'permission' => \App\Http\Middleware\Permission::class,
        'authAdmin' => \App\Http\Middleware\AuthenticateAdmin::class,
        'menu'=>\App\Http\Middleware\GetMenu::class,
    ];
}
