<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use app\common\addon\AddonManager;
use think\facade\Db;
use think\facade\Route;

echo "=== sys_addon ===\n";
try {
    $rows = Db::name('sys_addon')->select()->toArray();
    foreach ($rows as $r) {
        echo ($r['name'] ?? '') . ' status=' . ($r['status'] ?? '') . "\n";
    }
    if (!$rows) {
        echo "(empty)\n";
    }
} catch (Throwable $e) {
    echo 'DB error: ' . $e->getMessage() . "\n";
}

echo "\n=== info.ini state ===\n";
foreach (['addondev', 'yspay'] as $n) {
    $info = AddonManager::instance()->getInfo($n);
    echo $n . ' state=' . ($info['state'] ?? '?') . ' enabled=' . (AddonManager::instance()->isEnabled($n) ? '1' : '0') . "\n";
}

echo "\n=== enabledNames ===\n";
print_r(AddonManager::instance()->enabledNames());

echo "\n=== try mergeAuthRoutes ===\n";
try {
    AddonManager::instance()->mergeAuthRoutes();
    echo "ok\n";
} catch (Throwable $e) {
    echo 'fail: ' . $e->getMessage() . "\n";
}

echo "\n=== route names containing addondev ===\n";
$rules = Route::getRuleList();
foreach ($rules as $item) {
    $rule = is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) $item;
    if (stripos($rule, 'addondev') !== false || stripos($rule, 'yspay') !== false || stripos($rule, 'addons') !== false) {
        echo $rule . "\n";
    }
}
