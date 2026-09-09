<?php
declare(strict_types=1);

return [
    [
        'menu_name' => '支付功能',
        'parent_id' => 0,
        'order_num' => 6,
        'url'       => '#',
        'menu_type' => 'M',
        'visible'   => '0',
        'perms'     => '',
        'icon'      => 'fa fa-credit-card',
        'children'  => [
            [
                'menu_name' => '支付配置',
                'order_num' => 1,
                'url'       => '/yspay/config',
                'menu_type' => 'C',
                'visible'   => '0',
                'perms'     => 'yspay:config:view',
                'icon'      => 'fa fa-cog',
                'children'  => [
                    ['menu_name' => '查看', 'menu_type' => 'F', 'perms' => 'yspay:config:view', 'url' => '#'],
                    ['menu_name' => '编辑', 'menu_type' => 'F', 'perms' => 'yspay:config:edit', 'url' => '#'],
                ],
            ],
        ],
    ],
];
