<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Auth;
class IndexController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        dump($user);
        echo "aaaaaaaaa";
        Log::warning('warning');
        return view('admin.index.index');
    }
    
}
