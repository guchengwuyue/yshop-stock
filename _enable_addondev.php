<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use app\common\addon\AddonManager;
use think\facade\Db;
use think\facade\Route;

$mgr = AddonManager::instance();

echo "enabling addondev...\n";
try {
    $mgr->enable('addondev');
    echo "enable ok\n";
} catch (Throwable $e) {
    echo 'enable fail: ' . $e->getMessage() . "\n";
}

$row = Db::name('sys_addon')->where('name', 'addondev')->find();
echo 'db status=' . ($row['status'] ?? '?') . "\n";
$info = $mgr->getInfo('addondev');
echo 'ini state=' . ($info['state'] ?? '?') . "\n";
echo 'isEnabled=' . ($mgr->isEnabled('addondev') ? '1' : '0') . "\n";

// Re-include route file style merge
$mgr->mergeAuthRoutes();
$mgr->mergePublicRoutes();

$found = false;
foreach (Route::getRuleList() as $item) {
    $rule = is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) $item;
    if (stripos($rule, 'addondev') !== false) {
        echo $rule . "\n";
        $found = true;
    }
}
echo $found ? "routes found\n" : "NO addondev routes\n";

// menu visible
$menus = Db::name('sys_menu')->where('remark', 'addon:addondev')->field('menu_name,url,visible,perms')->select()->toArray();
echo "menus:\n";
print_r($menus);
