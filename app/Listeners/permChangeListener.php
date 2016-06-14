<?php

namespace App\Listeners;

use App\Events\permChangeEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Cache;
class permChangeListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  permChangeEvent  $event
     * @return void
     */
    public function handle(permChangeEvent $event)
    {
        Cache::store('file')->forget('perms');//清理缓存
        Cache::store('file')->forget('menus');//清理缓存

        $file_path = storage_path('framework/cache') . '/Breadcrumbs.php';
        if (file_exists($file_path))unlink($file_path); //删除文件重新生成
        $this->breadcrumbs($file_path);
    }
    
    public function breadcrumbs($file_path){
        $str = '<?php';
        // Home
        $str .= '
                Breadcrumbs::register("admin.index", function ($breadcrumbs) {
                    $breadcrumbs->push("首页", route("admin.index"));
                });
            ';
        //首先获取用户下所有的权限
        $perms = Cache::store("file")->rememberForever("perms", function () {
            return \App\Models\Permission::all();
        });;


        $arr = [];
        foreach ($perms as $permission) {
            $arr[$permission->cid][] = $permission;
        }
        foreach ($arr[0] as $v) {
            //循环所有 以cid为0的权限(也就是一级权限) 他们没有上级面包屑目录 所以和二级权限写法不同 所以要分开
            $index = [];
            $str .= 'Breadcrumbs::register("' . $v->name . '", function ($breadcrumbs){
                        $breadcrumbs->push("' . $v->display_name . '", route("' . $v->name . '"));
                    });';
            //循环二级权限
            if ($arr[$v->id]) {
                foreach ($arr[$v->id] as $vv) {
                    //ends_with 函数判断指定字符串结尾是否为指定内容 返回bool
                    //他这个二级是用index结尾 下级的全部归到这个index下面
                    if (ends_with($vv->name, '.index')) {
                        $index[$vv->name] = $vv->name;
                        $str .= 'Breadcrumbs::register("' . $vv->name . '", function ($breadcrumbs) {
                                    $breadcrumbs->parent("' . $v->name . '");
                                    $breadcrumbs->push("' . $vv->display_name . '", route("' . $vv->name . '"));
                                });';
                    }
                }
                //循环三级权限
                foreach ($arr[$v->id] as $vv) {
                    //结尾不是inidex
                    if (!ends_with($vv->name, '.index')) {
                        //这段应该和下面重复了
//                        $name_arr = explode('.', $vv->name);
//                        $index_str = $name_arr[0] . '.' . $name_arr[1] . '.index'; //通过 . 分割后 第二级和index的相同
                        $str .= 'Breadcrumbs::register("' . $vv->name . '", function ($breadcrumbs) {';
                        $name_arr = explode(".", $vv->name);
                        //这里组合成了 admin.permission.index 这种形式 然后去比较
                        $index_str = $name_arr[0] . "." . $name_arr[1] . ".index";
                        if (isset($index[$index_str])) {
                            $str .= '$breadcrumbs->parent("' . $index[$index_str] . '");';
                        } else {
                            $str .= '$breadcrumbs->parent("' . $v->name . '");'; //如果没有index这级就返回上一级为父级别
                        }
                        $str .= '$breadcrumbs->push("' . $vv->display_name . '", route("' . $vv->name . '"));';
                        $str .= '});';
                    }
                }
            }
        }
        file_put_contents($file_path,$str);
    }
}
