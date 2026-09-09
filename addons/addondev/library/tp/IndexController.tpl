<?php
declare(strict_types=1);

namespace addons\{$name}\controller;

use addons\{$name}\service\IndexService;
use app\controller\BaseController;
use think\facade\View;

/**
 * {$title}
 */
class Index extends BaseController
{
    protected IndexService $service;

    protected function initialize()
    {
        $this->service = new IndexService();
    }

    /**
     * 首页
     */
    public function index()
    {
        return View::fetch(addon_view('{$name}', 'index/index'), ['msg' => $this->service->hello()]);
    }
}
