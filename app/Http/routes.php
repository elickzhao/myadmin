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
    return view('welcome');
});

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

Route::get('test',function(){
    $user = User::where('name','=','elick')->first();
    $a = $user->ability(['Admin','Owner'], ['manage_posts','manage_users']);
    dump($a);

});