<?php
declare(strict_types=1);

namespace addons\yspay\controller;

use addons\yspay\library\Service;
use app\controller\BaseController;
use think\facade\Log;
use think\facade\Session;
use think\facade\View;
use Yansongda\Pay\Pay;

/**
 * 支付公开接口
 */
class Api extends BaseController
{
    /**
     * 外部提交
     */
    public function submit()
    {
        $outTradeNo = (string) $this->request->param('out_trade_no', '');
        $title = (string) $this->request->param('title', '');
        $amount = $this->request->param('amount');
        $type = (string) $this->request->param('type', $this->request->param('paytype', ''));
        $method = (string) $this->request->param('method', 'web');
        $openid = (string) $this->request->param('openid', '');
        $authCode = (string) $this->request->param('auth_code', '');
        $notifyurl = (string) $this->request->param('notifyurl', '');
        $returnurl = (string) $this->request->param('returnurl', '');

        if (!$amount || $amount < 0) {
            return $this->error('支付金额必须大于0');
        }
        if (!in_array($type, ['alipay', 'wechat'], true)) {
            return $this->error('支付类型错误');
        }

        return Service::submitOrder([
            'type'       => $type,
            'orderid'    => $outTradeNo,
            'title'      => $title,
            'amount'     => $amount,
            'method'     => $method,
            'openid'     => $openid,
            'auth_code'  => $authCode,
            'notifyurl'  => $notifyurl,
            'returnurl'  => $returnurl,
        ]);
    }

    public function wechat()
    {
        $config = Service::getConfig('wechat');
        $ua = (string) $this->request->server('HTTP_USER_AGENT');
        $isWechat = stripos($ua, 'MicroMessenger') !== false;
        $isMobile = method_exists($this->request, 'isMobile') ? $this->request->isMobile() : false;

        if ($this->request->isAjax()) {
            try {
                $pay = Pay::wechat($config);
                $orderid = (string) $this->request->post('orderid', '');
                $result = $pay->find(['out_trade_no' => $orderid]);
                return $this->success('', ['status' => $result['trade_state'] ?? 'NOTPAY']);
            } catch (\Throwable) {
                return $this->error('查询失败');
            }
        }

        $orderData = Session::get('wechatorderdata');
        if (!$orderData) {
            return $this->error('请求参数错误');
        }

        if ($isWechat && $isMobile) {
            if (empty($orderData['openid'])) {
                $orderData['openid'] = Service::getOpenid();
            }
            $orderData['method'] = 'mp';
            $type = 'jsapi';
            $payData = Service::submitOrder($orderData);
            if (!isset($payData['paySign']) && !(is_array($payData) && isset($payData['paySign']))) {
                $arr = is_object($payData) && method_exists($payData, 'toArray') ? $payData->toArray() : (array) $payData;
                $payData = $arr;
            }
            if (!isset($payData['paySign'])) {
                return $this->error('创建订单失败');
            }
        } else {
            $orderData['method'] = 'scan';
            $type = 'pc';
            $payData = Service::submitOrder($orderData);
            $arr = is_object($payData) && method_exists($payData, 'toArray') ? $payData->toArray() : (array) $payData;
            $payData = $arr;
            if (!isset($payData['code_url'])) {
                return $this->error('创建订单失败');
            }
        }

        return View::fetch(addon_view('yspay', 'api/wechat'), [
            'orderData' => $orderData,
            'payData'   => $payData,
            'type'      => $type,
            'title'     => '微信支付',
            'isWechat'  => $isWechat,
            'isMobile'  => $isMobile,
        ]);
    }

    public function alipay()
    {
        $config = Service::getConfig('alipay');
        if ($this->request->isAjax()) {
            try {
                $pay = Pay::alipay($config);
                $orderid = (string) $this->request->post('orderid', '');
                $result = $pay->find(['out_trade_no' => $orderid]);
                $status = $result['trade_status'] ?? '';
                return $this->success('', ['status' => $status]);
            } catch (\Throwable) {
                return $this->error('查询失败');
            }
        }

        $orderData = Session::get('alipayorderdata');
        if (!$orderData) {
            return $this->error('请求参数错误');
        }
        $orderData['method'] = 'scan';
        $payData = Service::submitOrder($orderData);
        $arr = is_object($payData) && method_exists($payData, 'toArray') ? $payData->toArray() : (array) $payData;
        $payData = $arr;
        if (!isset($payData['qr_code'])) {
            return $this->error('创建订单失败');
        }

        return View::fetch(addon_view('yspay', 'api/alipay'), [
            'orderData' => $orderData,
            'payData'   => $payData,
            'type'      => 'pc',
            'title'     => '支付宝支付',
        ]);
    }

    public function notifyx(string $type = '')
    {
        $type = $type !== '' ? $type : (string) $this->request->param('type', '');
        $pay = Service::checkNotify($type);
        if (!$pay) {
            return json(['code' => 'FAIL', 'message' => 'FAIL'], 500);
        }
        try {
            $data = $pay->callback();
            if ($type === 'wechat') {
                $data = $data['resource']['ciphertext'] ?? $data;
            }
            Log::info('yspay notify: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::error('yspay notify logic: ' . $e->getMessage());
        }
        $resp = $pay->success();
        if (is_object($resp) && method_exists($resp, 'getBody')) {
            return response($resp->getBody()->getContents(), 200, ['Content-Type' => 'application/json']);
        }
        return response('{\"code\":\"SUCCESS\",\"message\":\"SUCCESS\"}', 200, ['Content-Type' => 'application/json']);
    }

    public function returnx(string $type = '')
    {
        // checkReturn always true; do not treat as signature failure
        return '<!DOCTYPE html><html lang="zh"><body><h3>支付成功</h3></body></html>';
    }
}
