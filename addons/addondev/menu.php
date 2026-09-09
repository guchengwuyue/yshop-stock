<?php
declare(strict_types=1);

return [
    [
        'menu_name' => '插件功能',
        'parent_id' => 0,
        'order_num' => 5,
        'url'       => '#',
        'menu_type' => 'M',
        'visible'   => '0',
        'perms'     => '',
        'icon'      => 'fa fa-puzzle-piece',
        'children'  => [
            [
                'menu_name' => '插件开发',
                'order_num' => 1,
                'url'       => '/addondev/addons',
                'menu_type' => 'C',
                'visible'   => '0',
                'perms'     => 'addondev:addons:view',
                'icon'      => 'fa fa-code',
                'children'  => [
                    ['menu_name' => '列表', 'menu_type' => 'F', 'perms' => 'addondev:addons:list', 'url' => '#'],
                    ['menu_name' => '新建', 'menu_type' => 'F', 'perms' => 'addondev:addons:add', 'url' => '#'],
                    ['menu_name' => '编辑', 'menu_type' => 'F', 'perms' => 'addondev:addons:edit', 'url' => '#'],
                    ['menu_name' => '打包', 'menu_type' => 'F', 'perms' => 'addondev:addons:package', 'url' => '#'],
                    ['menu_name' => '菜单', 'menu_type' => 'F', 'perms' => 'addondev:menu:list', 'url' => '#'],
                    ['menu_name' => '同步菜单', 'menu_type' => 'F', 'perms' => 'addondev:menu:edit', 'url' => '#'],
                ],
            ],
            [
                'menu_name' => '插件菜单',
                'order_num' => 2,
                'url'       => '/addondev/menu',
                'menu_type' => 'C',
                'visible'   => '0',
                'perms'     => 'addondev:menu:list',
                'icon'      => 'fa fa-bars',
                'children'  => [],
            ],
        ],
    ],
];
