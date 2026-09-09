<?php
declare(strict_types=1);

namespace addons\addondev\service;

use app\common\addon\AddonManager;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 插件菜单维护
 */
class MenuService
{
    /**
     * 菜单列表
     * @return array{0: array, 1: int}
     */
    public function selectList(array $params = []): array
    {
        $name = trim((string) ($params['name'] ?? ''));
        if ($name === '') {
            return [[], 0];
        }
        $mark = 'addon:' . $name;
        $query = Db::name('sys_menu')->where('remark', $mark)->order('parent_id')->order('order_num');
        $rows = $query->select()->toArray();
        $pageNum = max(1, (int) ($params['pageNum'] ?? $params['page_num'] ?? 1));
        $pageSize = max(1, (int) ($params['pageSize'] ?? $params['page_size'] ?? 50));
        $total = count($rows);
        $slice = array_slice($rows, ($pageNum - 1) * $pageSize, $pageSize);
        return [PageHelper::camelRows($slice), $total];
    }

    /**
     * 从 menu.php 同步
     */
    public function sync(string $name): int
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('请指定插件标识');
        }
        if (!get_addon_info($name)) {
            throw new \RuntimeException('插件不存在');
        }
        AddonManager::instance()->syncMenus($name, true);
        return 1;
    }
}
