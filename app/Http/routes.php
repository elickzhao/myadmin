<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use App\Models\Role;
use App\Models\Permission;
use App\User;

Route::get('/', function () {
//    echo Session::getId();
//    dump(Session::all());
//    dump($this->app);   //这个和下面那个数组好像不是一回事
//    dump($this->app['session']); //这个在$this->app 里的 aliases 数组里可以看到 就是config/app.php里 用的是小写
    return view('home');
    //return view('welcome');
});

Route::get('admin', function () {
    return redirect('/admin/index');
});

Route::get('admin/index', ['as' => 'admin.index', 'middleware' => ['auth','menu'], 'uses'=>'Admin\\IndexController@index']);

$this->group(['namespace' => 'Admin','prefix' => '/admin',], function () {
    Route::auth();
});

$router->group(['namespace' => 'Admin', 'middleware' => ['auth','authAdmin','menu']], function () {
    //权限管理路由
    Route::get('admin/permission/{cid}/create', ['as' => 'admin.permission.create', 'uses' => 'PermissionController@create']);
    Route::get('admin/permission/{cid?}', ['as' => 'admin.permission.index', 'uses' => 'PermissionController@index']);
    Route::post('admin/permission/index', ['as' => 'admin.permission.index', 'uses' => 'PermissionController@index']); //查询

    Route::resource('admin/permission', 'PermissionController');
    Route::put('admin/permission/update', ['as' => 'admin.permission.edit', 'uses' => 'PermissionController@update']); //修改
    Route::post('admin/permission/store', ['as' => 'admin.permission.create', 'uses' => 'PermissionController@store']); //添加


    //角色管理路由
    Route::get('admin/role/index', ['as' => 'admin.role.index', 'uses' => 'RoleController@index']);
    Route::post('admin/role/index', ['as' => 'admin.role.index', 'uses' => 'RoleController@index']);
    Route::resource('admin/role', 'RoleController');
    Route::put('admin/role/update', ['as' => 'admin.role.edit', 'uses' => 'RoleController@update']); //修改
    Route::post('admin/role/store', ['as' => 'admin.role.create', 'uses' => 'RoleController@store']); //添加


    //用户管理路由
    Route::get('admin/user/manage', ['as' => 'admin.user.manage', 'uses' => 'UserController@index']);  //用户管理
    Route::post('admin/user/index', ['as' => 'admin.user.index', 'uses' => 'UserController@index']);
    Route::resource('admin/user', 'UserController');
    Route::put('admin/user/update', ['as' => 'admin.user.edit', 'uses' => 'UserController@update']); //修改
    Route::post('admin/user/store', ['as' => 'admin.user.create', 'uses' => 'UserController@store']); //添加


});



Route::auth();

Route::get('/home',function(){
    return view('welcome');
});

//权限测试
Route::get('add',function(){
    // Cache为file时 会出现Cache不支持tag的错误
    //换成array就好了
    $admin = new Role;
    $admin->name = 'Admin';
    $admin->save();

    $owner = new Role;
    $owner->name = 'Owner';
    $owner->save();

    #这个可以一起插入
    $manageUsers = new Permission();
    $manageUsers->name = 'manage_users';
    $manageUsers->display_name = 'Manage Users';
    $manageUsers->save();

    $managePosts = new Permission();
    $managePosts->name = 'manage_posts';
    $managePosts->display_name = 'Manage Posts';
    $managePosts->save();


    $owner->perms()->sync(array($managePosts->id, $manageUsers->id));
    $admin->perms()->sync(array($managePosts->id));


    // 获取用户
    $user = User::where('name','=','elick')->first();

    // 可以使用 Entrust 提供的便捷方法用户授权
    // 注: 参数可以为 Role 对象, 数组, 或者 ID
        $user->attachRole( $admin );

    // 或者使用 Eloquent 自带的对象关系赋值
        //$user->roles()->attach( $admin->id ); // id only

    $a = $user->hasRole("Owner");    // false
    $b = $user->hasRole("Admin");    // true

    $c = $user->can("manage_posts"); // true
    $d = $user->can("manage_users"); // false

    dump($a);dump($b);dump($c);dump($d);

    //can 和hasRole也能接收数组 一个对全都对
    $user->hasRole(['owner', 'admin']);       // true
    $user->can(['edit-user', 'create-post']); // true

    //如果想要两个参数同为真才行,可以用第三个参数
    $user->hasRole(['owner', 'admin']);             // true
    $user->hasRole(['owner', 'admin'], true);       // false, user does not have admin role
    $user->can(['edit-user', 'create-post']);       // true
    $user->can(['edit-user', 'create-post'], true); // false, user does not have edit-user permission

});

//这是测试权限
Route::get('test',function(){
    $user = User::where('name','=','elick')->first();
    $a = $user->ability(['Admin','Owner'], ['manage_posts','manage_users']);
    dump($a);

});

//这个也是测试面包屑
Route::get('home',['as'=>'home',function(){
    return view('home');
}]);

//这是为了测试面包屑
Route::get('blog',['as'=>'blog',function(){
    return view('blog');
}]);

//这是为了测试log插件
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

//这个是5.2新加的 就是添加 注册应用程序的典型身份验证路径
Route::auth();