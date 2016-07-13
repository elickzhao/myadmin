<?php

namespace App\Http\Middleware;

use Closure;
use Zizaco\Entrust\EntrustFacade as Entrust;
use Route,URL,Auth;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        //return $next($request);
        //Auth::guard('admin') //这是根据 config/auth 里做不同选项,把验证分开.比如管理员与前台普通用户
        //其实最主要是分表,因为管理员表和用户表不是一个表
        if(Auth::guard('admin')->user()->id === 1){     //获取当前已经验证的用户
            return $next($request);
        }

        $previousUrl = URL::previous(); //获取前一个请求的网址
        //因为表内把路由名放在name字段用于验证权限
        if(!Auth::guard('admin')->user()->can(Route::currentRouteName())) {  //->user()->can(Route::currentRouteName()) 是验证权限  获取当前路由名称
            if($request->ajax() && ($request->getMethod() != 'GET')) {
                return response()->json([
                    'status' => -1,
                    'code' => 403,
                    'msg' => '您没有权限执行此操作'
                ]);
            } else {
                return response()->view('admin.errors.403', compact('previousUrl'));
            }
        }

        return $next($request);
    }
}
