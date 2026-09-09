<?php
declare(strict_types=1);

namespace addons\addondev\controller;

use addons\addondev\service\MenuService;
use app\controller\BaseController;
use app\common\addon\AddonManager;
use think\facade\View;

/**
 * 插件菜单维护
 */
class Menu extends BaseController
{
    protected MenuService $menuService;

    protected function initialize()
    {
        $this->menuService = new MenuService();
    }

    /**
     * 列表页
     */
    public function index()
    {
        $addons = AddonManager::instance()->scan();
        return View::fetch(addon_view('addondev', 'menu/menu'), ['addons' => $addons]);
    }

    /**
     * 菜单列表
     */
    public function list()
    {
        [$rows, $total] = $this->menuService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 从 menu.php 同步
     */
    public function sync(string $name = '')
    {
        $name = $name !== '' ? $name : (string) $this->request->param('name', '');
        try {
            return $this->toAjax($this->menuService->sync($name));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }
}
