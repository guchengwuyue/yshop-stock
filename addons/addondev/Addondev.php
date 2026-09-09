<?php
declare(strict_types=1);

namespace addons\addondev;

use app\common\addon\Addon;

/**
 * 插件开发
 */
class Addondev extends Addon
{
    /**
     * 安装
     */
    public function install(): bool
    {
        return true;
    }

    /**
     * 卸载
     */
    public function uninstall(): bool
    {
        return true;
    }

    /**
     * 启用
     */
    public function enable(): bool
    {
        return true;
    }

    /**
     * 禁用
     */
    public function disable(): bool
    {
        return true;
    }
}
