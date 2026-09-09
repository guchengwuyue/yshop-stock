<?php
declare(strict_types=1);

namespace addons\yspay\library;

use think\facade\Log;
use think\facade\Session;
use Yansongda\Pay\Pay;

/**
 * 支付下单与回调
 */
class Service
{
    public const SDK_VERSION_V2 = 'v2';
    public const SDK_VERSION_V3 = 'v3';

    /**
     * 提交支付订单
     * @param array|float|string $amount
     */
    public static function submitOrder(
        $amount,
        $orderid = null,
        $type = null,
        $title = null,
        $notifyurl = null,
        $returnurl = null,
        $method = null,
        $openid = '',
        $custom = []
    ): mixed {
        if (!self::isVersionV3()) {
            throw new \RuntimeException('当前仅支持 Composer yansongda/pay V3');
        }

        $request = request();
        $addonConfig = get_addon_config('yspay');

        if (!is_array($amount)) {
            $params = [
                'amount'    => $amount,
                'orderid'   => $orderid,
                'type'      => $type,
                'title'     => $title,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method'    => $method,
                'openid'    => $openid,
                'custom'    => $custom,
            ];
        } else {
            $params = $amount;
        }

        if (empty($params['orderid']) && !empty($params['out_trade_no'])) {
            $params['orderid'] = $params['out_trade_no'];
        }

        $type = isset($params['type']) && in_array($params['type'], ['alipay', 'wechat'], true) ? $params['type'] : 'wechat';
        $method = $params['method'] ?? 'web';
        $orderid = $params['orderid'] ?? (date('YmdHis') . mt_rand(100000, 999999));
        $amount = $params['amount'] ?? 1;
        $title = $params['title'] ?? '支付';
        $auth_code = $params['auth_code'] ?? '';
        $openid = $params['openid'] ?? '';
        $custom = $params['custom'] ?? [];

        $notifyurl = !empty($params['notifyurl'])
            ? $params['notifyurl']
            : $request->root(true) . '/addons/yspay/api/notifyx/' . $type;
        $returnurl = !empty($params['returnurl'])
            ? $params['returnurl']
            : $request->root(true) . '/addons/yspay/api/returnx/' . $type;

        $config = self::getConfig($type, array_merge($custom, [
            'notify_url' => $notifyurl,
            'return_url' => $returnurl,
        ]));

        $isMobile = method_exists($request, 'isMobile') ? $request->isMobile() : false;
        $ua = (string) $request->server('HTTP_USER_AGENT');
        $isWechat = stripos($ua, 'MicroMessenger') !== false;

        $result = null;
        if ($type === 'alipay') {
            if ($method === 'web') {
                if ($isWechat || !empty($addonConfig['alipay']['scanpay'])) {
                    Session::set('alipayorderdata', $params);
                    return redirect(addon_url('yspay/api/alipay', [], true, true));
                }
                if ($isMobile) {
                    $method = 'wap';
                }
            }
            $pay = Pay::alipay($config);
            $order = [
                'out_trade_no' => $orderid,
                'total_amount' => $amount,
                'subject'      => $title,
            ];
            $result = match ($method) {
                'web' => $pay->web($order),
                'wap' => $pay->wap($order),
                'app' => $pay->app($order),
                'scan' => $pay->scan($order),
                'pos' => $pay->pos(array_merge($order, ['auth_code' => $auth_code])),
                'mini', 'miniapp' => $pay->mini(array_merge($order, is_numeric($openid) && strlen((string) $openid) === 16
                    ? ['buyer_id' => $openid]
                    : ['buyer_open_id' => $openid])),
                default => null,
            };
        } else {
            if ($method === 'web') {
                if ($isMobile && !$isWechat) {
                    $method = 'wap';
                } else {
                    Session::set('wechatorderdata', $params);
                    return redirect(addon_url('yspay/api/wechat', [], true, true));
                }
            }
            $totalFee = (int) (function_exists('bcmul') ? bcmul((string) $amount, '100') : $amount * 100);
            $ip = $request->ip();
            $openidName = (($addonConfig['wechat']['mode'] ?? '') === 'service') ? 'sub_openid' : 'openid';
            $pay = Pay::wechat($config);
            $order = [
                'out_trade_no' => $orderid,
                'description'  => $title,
                'amount'       => ['total' => $totalFee],
            ];
            $result = match ($method) {
                'mp' => $pay->mp(array_merge($order, ['payer' => [$openidName => $openid]])),
                'wap' => $pay->wap(array_merge($order, [
                    'scene_info' => [
                        'payer_client_ip' => $ip,
                        'h5_info'         => ['type' => 'Wap'],
                    ],
                ])),
                'app' => $pay->app($order),
                'scan' => $pay->scan($order),
                'pos' => $pay->pos(array_merge($order, ['auth_code' => $auth_code])),
                'mini', 'miniapp' => $pay->mini(array_merge($order, ['payer' => [$openidName => $openid]])),
                default => null,
            };
        }

        return $result;
    }

    /**
     * 验证异步通知
     */
    public static function checkNotify(string $type, array $custom = []): mixed
    {
        $type = strtolower($type);
        if (!in_array($type, ['wechat', 'alipay'], true)) {
            return false;
        }
        if (!self::isVersionV3()) {
            return false;
        }
        try {
            $config = self::getConfig($type, $custom);
            $pay = $type === 'wechat' ? Pay::wechat($config) : Pay::alipay($config);
            $data = $pay->callback();
            if ($type === 'alipay') {
                $status = $data['trade_status'] ?? '';
                if (in_array($status, ['TRADE_SUCCESS', 'TRADE_FINISHED'], true)) {
                    return $pay;
                }
                return false;
            }
            return $pay;
        } catch (\Throwable $e) {
            Log::error('yspay notify parse error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 验证同步跳转
     * @deprecated
     */
    public static function checkReturn(string $type, array $custom = []): bool
    {
        return true;
    }

    /**
     * 获取支付配置
     */
    public static function getConfig(string $type = 'wechat', array $custom = []): array
    {
        $addonConfig = get_addon_config('yspay');
        $config = $addonConfig[$type] ?? ($addonConfig['wechat'] ?? []);

        if ($type === 'wechat') {
            foreach (['cert_client', 'cert_key', 'public_key'] as $field) {
                self::processAddonsPath($config, $field);
            }
            $config['mp_app_id'] = $config['app_id'] ?? '';
            $config['app_id'] = $config['appid'] ?? '';
            $config['mini_app_id'] = $config['miniapp_id'] ?? '';
            $config['mch_secret_key'] = $config['key_v3'] ?? '';
            $config['mch_secret_cert'] = $config['cert_key'] ?? '';
            $config['mch_public_cert_path'] = $config['cert_client'] ?? '';
            $config['wechat_public_cert_path'] = [
                ($config['public_key_id'] ?? '') => ($config['public_key'] ?? ''),
            ];
            $config['sub_mp_app_id'] = $config['sub_appid'] ?? '';
            $config['sub_mini_app_id'] = $config['sub_miniapp_id'] ?? '';
        } elseif ($type === 'alipay') {
            $config['signtype'] = $config['signtype'] ?? 'publickey';
            if (($config['signtype'] ?? '') === 'cert') {
                foreach (['app_cert_public_key', 'alipay_root_cert', 'ali_public_key'] as $field) {
                    self::processAddonsPath($config, $field);
                }
            } else {
                $config['app_cert_public_key'] = '';
                $config['alipay_root_cert'] = '';
            }
            $config['app_secret_cert'] = $config['private_key'] ?? '';
            $config['app_public_cert_path'] = $config['app_cert_public_key'] ?? '';
            $config['alipay_public_cert_path'] = $config['ali_public_key'] ?? '';
            $config['alipay_root_cert_path'] = $config['alipay_root_cert'] ?? '';
            $config['service_provider_id'] = $config['pid'] ?? '';
        }

        $modeArr = ['normal' => 0, 'dev' => 1, 'service' => 2];
        $config['mode'] = $modeArr[$config['mode'] ?? 'normal'] ?? 0;

        if (!empty($config['log'])) {
            $logDir = runtime_path() . 'yspay' . DIRECTORY_SEPARATOR;
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $config['log'] = [
                'enable' => true,
                'file'   => $logDir . $type . '-' . date('Y-m-d') . '.log',
                'level'  => 'debug',
            ];
        } else {
            $config['log'] = ['enable' => false];
        }

        $config['http'] = ['timeout' => 10, 'connect_timeout' => 10];

        $config['notify_url'] = empty($config['notify_url'])
            ? addon_url('yspay/api/notifyx', [], false) . '/' . $type
            : $config['notify_url'];
        if (!preg_match('#^https?://#i', (string) $config['notify_url'])) {
            $config['notify_url'] = request()->root(true) . $config['notify_url'];
        }
        $config['return_url'] = empty($config['return_url'])
            ? addon_url('yspay/api/returnx', [], false) . '/' . $type
            : $config['return_url'];
        if (!preg_match('#^https?://#i', (string) $config['return_url'])) {
            $config['return_url'] = request()->root(true) . $config['return_url'];
        }

        $config = array_merge($config, $custom);

        return [
            $type    => ['default' => $config],
            'logger' => $config['log'],
            'http'   => $config['http'],
            '_force' => true,
        ];
    }

    /**
     * 获取微信 Openid
     */
    public static function getOpenid(array $custom = []): string
    {
        $openid = (string) Session::get('openid', '');
        if ($openid !== '') {
            return $openid;
        }
        $addonConfig = get_addon_config('yspay');
        $appId = $custom['app_id'] ?? ($addonConfig['wechat']['app_id'] ?? '');
        $secret = $custom['app_secret'] ?? ($addonConfig['wechat']['app_secret'] ?? '');
        if ($appId === '' || $secret === '') {
            return '';
        }
        $wechat = new Wechat($appId, $secret);
        return (string) $wechat->getOpenid();
    }

    /**
     * 获取 SDK 版本
     */
    public static function getSdkVersion(): string
    {
        $addonConfig = get_addon_config('yspay');
        return $addonConfig['version'] ?? self::SDK_VERSION_V3;
    }

    public static function isVersionV2(): bool
    {
        return self::getSdkVersion() === self::SDK_VERSION_V2;
    }

    public static function isVersionV3(): bool
    {
        return self::getSdkVersion() === self::SDK_VERSION_V3;
    }

    private static function processAddonsPath(array &$config, string $field): void
    {
        if (!isset($config[$field]) || !is_string($config[$field])) {
            return;
        }
        if (str_starts_with($config[$field], '/addons/')) {
            $config[$field] = root_path() . str_replace('/', DIRECTORY_SEPARATOR, ltrim($config[$field], '/'));
        }
    }
}
