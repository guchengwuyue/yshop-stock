# 支付聚合插件 (yspay)

Composer: `yansongda/pay` V3

## 对外 API

```php
use addons\yspay\library\Service;

Service::submitOrder([
    'amount'  => '0.01',
    'orderid' => 'ORD001',
    'type'    => 'wechat', // wechat|alipay
    'title'   => 'demo',
    'method'  => 'scan',   // web|wap|app|scan|mp|mini
    'openid'  => '',
]);

$pay = Service::checkNotify('wechat');
$config = Service::getConfig('wechat');
$openid = Service::getOpenid();
```

Notify: `/addons/yspay/api/notifyx/{wechat|alipay}`
Return: `/addons/yspay/api/returnx/{wechat|alipay}`

Certs: `addons/yspay/certs/`