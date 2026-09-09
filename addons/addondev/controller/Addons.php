<?php
declare(strict_types=1);

namespace addons\addondev\controller;

use addons\addondev\service\PackageService;
use addons\addondev\service\ScaffoldService;
use app\controller\BaseController;
use think\facade\View;

/**
 * 插件开发
 */
class Addons extends BaseController
{
    protected ScaffoldService $scaffoldService;
    protected PackageService $packageService;

    protected function initialize()
    {
        $this->scaffoldService = new ScaffoldService();
        $this->packageService = new PackageService();
    }

    /**
     * 列表页
     */
    public function index()
    {
        return View::fetch(addon_view('addondev', 'addons/addons'));
    }

    /**
     * 分页列表
     */
    public function list()
    {
        [$rows, $total] = $this->scaffoldService->selectList($this->request->param());
        return $this->getDataTable($rows, $total);
    }

    /**
     * 创建插件
     */
    public function add()
    {
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->scaffoldService->create($this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch(addon_view('addondev', 'addons/add'));
    }

    /**
     * 编辑插件
     */
    public function edit(string $name = '')
    {
        $name = $name !== '' ? $name : (string) $this->request->param('name', '');
        if ($this->request->isPost()) {
            try {
                return $this->toAjax($this->scaffoldService->update($name, $this->request->post()));
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $info = get_addon_info($name);
        if (!$info) {
            return $this->error('插件不存在');
        }
        return View::fetch(addon_view('addondev', 'addons/edit'), ['info' => $info]);
    }

    /**
     * 插件打包
     */
    public function package(string $name = '')
    {
        $name = $name !== '' ? $name : (string) $this->request->param('name', '');
        $info = get_addon_info($name);
        if (!$info) {
            return $this->error('插件不存在');
        }
        if ($this->request->isPost()) {
            try {
                $version = (string) $this->request->post('version', $info['version'] ?? '1.0.0');
                $path = $this->packageService->package($name, $version);
                return $this->success('打包成功', ['path' => $path]);
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        return View::fetch(addon_view('addondev', 'addons/package'), ['info' => $info]);
    }
}
