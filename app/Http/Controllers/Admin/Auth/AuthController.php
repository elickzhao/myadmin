<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\AdminUser as User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     *
     * 这个Auth目录基本上就是把Controllers/Auth 照搬过来
     * 就是多了下面这些定义跳转目录和模版而已
     *
     * 这个控制器是处理登录,注册等相关操作的,是根据routes里Route::auth();创建的路由指向这里的
     */
    protected $redirectAfterLogout = '/admin/login';
    protected $redirectTo = '/admin';
    protected $loginView = 'admin.auth.login';
    protected $registerView = 'admin.auth.register';
    protected $guard = 'admin';


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //下面这句现在明白了 'guest'是固定中间件,而guestMiddleware()是往里传参数,得到一个自定义中间件
        //[!- 5.2和5.1这里有点区别 5.1这里是这么写的 $this->middleware('guest', ['except' => 'getLogout']); -]
        //$this->guestMiddleware() 返回的是自定义中间件,这里返回的是 'guest:admin'
        //原来这个 : 是分割参数的 前面是中间:后面是参数值 这里应该是RedirectIfAuthenticated类的第三个参数$guard
        //他在配置文件$this->app['config']["auth.guards.{$name}"] 数组里多加了个admin  这个就是取得这个配置 其实和web是一样的
        //好像昨天想错了 这里排除了 logout 说明什么? 可能是login的时候不需要验证登录token 要不肯定会报错的
        //guest这个中间件这个名字太误导了,并不是guest账户可以登录的页面.而是登陆后的帐号就跳转的到主页面. 比如login,register页面登录后就不需要显示了
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
