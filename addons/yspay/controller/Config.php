<?php
declare(strict_types=1);

namespace addons\yspay\controller;

use app\common\addon\AddonManager;
use app\controller\BaseController;
use think\facade\View;

/**
 * 支付配置
 */
class Config extends BaseController
{
    public function index()
    {
        $manager = AddonManager::instance();
        if ($this->request->isPost()) {
            try {
                $post = $this->request->post();
                $incoming = $post['config'] ?? $post;
                $current = $manager->getConfig('yspay');
                foreach ((array) $incoming as $k => $v) {
                    if (in_array($k, ['name'], true)) {
                        continue;
                    }
                    if (is_string($v) && (str_starts_with(trim($v), '{') || str_starts_with(trim($v), '['))) {
                        $decoded = json_decode($v, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $v = $decoded;
                        }
                    }
                    $current[$k] = $v;
                }
                $manager->setConfig('yspay', $current);
                return $this->success('保存配置');
            } catch (\Throwable $e) {
                return $this->error($e->getMessage());
            }
        }
        $schema = $manager->fillSchema($manager->getConfigSchema('yspay'), $manager->getConfig('yspay'));
        foreach ($schema as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $item['valueText'] = is_array($item['value'] ?? null)
                ? json_encode($item['value'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                : (string) ($item['value'] ?? '');
        }
        unset($item);
        return View::fetch(addon_view('yspay', 'config/index'), ['schema' => $schema, 'name' => 'yspay']);
    }

    public function upload()
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->error('请上传证书文件');
        }
        $dir = addon_path('yspay') . 'certs' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getOriginalName()) ?: ('cert_' . time() . '.pem');
        $file->move($dir, $name);
        return $this->success('上传成功', ['path' => '/addons/yspay/certs/' . $name]);
    }
}
