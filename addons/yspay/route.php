<?php
declare(strict_types=1);

use think\facade\Route;

return [
    'public' => function () {
        Route::any('addons/yspay/api/submit', '\addons\yspay\controller\Api@submit');
        Route::any('addons/yspay/api/wechat', '\addons\yspay\controller\Api@wechat');
        Route::any('addons/yspay/api/alipay', '\addons\yspay\controller\Api@alipay');
        Route::any('addons/yspay/api/notifyx/:type', '\addons\yspay\controller\Api@notifyx');
        Route::any('addons/yspay/api/returnx/:type', '\addons\yspay\controller\Api@returnx');
    },
    'auth' => function () {
        Route::rule('yspay/config', '\addons\yspay\controller\Config@index', 'GET|POST')
            ->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])
            ->option(['perms' => 'yspay:config:edit', 'title' => '支付配置']);
        Route::post('yspay/config/upload', '\addons\yspay\controller\Config@upload')
            ->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])
            ->option(['perms' => 'yspay:config:edit', 'title' => '上传证书']);
    },
];
