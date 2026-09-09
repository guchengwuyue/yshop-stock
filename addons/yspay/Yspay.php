<?php
declare(strict_types=1);

namespace addons\yspay;

use app\common\addon\Addon;

/**
 * 支付聚合
 */
class Yspay extends Addon
{
    public function install(): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function enable(): bool
    {
        return true;
    }

    public function disable(): bool
    {
        return true;
    }
}
