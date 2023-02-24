<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use \Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 测试环境打印数据库日志
        if (App::environment() == 'develop') {
            DB::listen(function ($query) {
                logger(Str::replaceArray('?', $query->bindings, $query->sql));
            });
        }
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
