<?php
declare(strict_types=1);

use think\facade\Route;

return [
    'public' => function () {},
    'auth' => function () {
        Route::get('addondev/addons', '\addons\addondev\controller\Addons@index')
            ->middleware(\app\middleware\Permission::class)
            ->option(['perms' => 'addondev:addons:view']);
        Route::post('addondev/addons/list', '\addons\addondev\controller\Addons@list')
            ->middleware(\app\middleware\Permission::class)
            ->option(['perms' => 'addondev:addons:list']);
        Route::rule('addondev/addons/add', '\addons\addondev\controller\Addons@add', 'GET|POST')
            ->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])
            ->option(['perms' => 'addondev:addons:add', 'title' => '新建插件']);
        Route::rule('addondev/addons/edit/:name', '\addons\addondev\controller\Addons@edit', 'GET|POST')
            ->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])
            ->option(['perms' => 'addondev:addons:edit', 'title' => '编辑插件']);
        Route::rule('addondev/addons/package/:name', '\addons\addondev\controller\Addons@package', 'GET|POST')
            ->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])
            ->option(['perms' => 'addondev:addons:package', 'title' => '插件打包']);
        Route::get('addondev/menu', '\addons\addondev\controller\Menu@index')
            ->middleware(\app\middleware\Permission::class)
            ->option(['perms' => 'addondev:menu:list']);
        Route::post('addondev/menu/list', '\addons\addondev\controller\Menu@list')
            ->middleware(\app\middleware\Permission::class)
            ->option(['perms' => 'addondev:menu:list']);
        Route::post('addondev/menu/sync/:name', '\addons\addondev\controller\Menu@sync')
            ->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])
            ->option(['perms' => 'addondev:menu:edit', 'title' => '同步插件菜单']);
    },
];
