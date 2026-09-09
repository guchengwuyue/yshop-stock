<?php
declare(strict_types=1);

/**
 * 菜单配置（顶级目录 + 功能子菜单）
 */
return [
    [
        'menu_name' => '{$title}',
        'parent_id' => 0,
        'order_num' => 100,
        'url'       => '#',
        'menu_type' => 'M',
        'visible'   => '0',
        'perms'     => '',
        'icon'      => 'fa fa-folder-o',
        'children'  => [
            [
                'menu_name' => '首页',
                'order_num' => 1,
                'url'       => '/{$name}/index',
                'menu_type' => 'C',
                'visible'   => '0',
                'perms'     => '{$name}:index:view',
                'icon'      => 'fa fa-circle-o',
                'children'  => [
                    ['menu_name' => '列表', 'menu_type' => 'F', 'perms' => '{$name}:index:list', 'url' => '#'],
                ],
            ],
        ],
    ],
];
