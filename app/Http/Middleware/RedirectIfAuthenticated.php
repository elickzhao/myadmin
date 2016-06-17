<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     *
     * 这个5.2和5.1有区别,5.2这个文件的写法更类似Authenticate这个类
     * 5.1那个有个构造函数,然后把$guard作为属性,而不像这里作为handle第三个参数
     */
    public function handle($request, Closure $next, $guard = null)
    {
        //返回Auth::guard($guard) 返回Guard实例 $guard是根据配置不同,保存不同的表,也就是用户表
        //guard作用主要是登录token  应该是每次登录生成新的token保存在数据库和session里 明天可以测试下
        //看来并没有 登录后数据库token并没有改变
        //什么鬼退出的时候token存入了
        //以前想错了 这个类的名字叫 如果验证了就重定向  而且这是登录页面时加载的中间件 说明用户还没有登录
        //所以如果是登录的用户就跳转到登录以后的页面
        //有点懂这个guard了 主要用这个机制应对同一应用不同登录验证 比如前台用户登录和后台管理员登录 就可以放在两个表里
        //然后配置不同的guard
        //终于明白了 guard就是检验是否登录 (* 下面这些是 check()做的 Auth::guard($guard)是先返回个实例)
        //根据配置如果是session 那么登录后会保存一个"login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d" => 2
        //这里login是固定的 admin是guard配置名后面是随机数 这个session的key的值 就是自定义用户表里的用户id
        //然后通过id获取用户信息 拼根据此判断是否登录 没有用户信息就是没登陆
        if (Auth::guard($guard)->check()) {
            return redirect('/');
        }

        return $next($request);
    }
}
