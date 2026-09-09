-- ----------------------------
-- 插件运行时：sys_addon + 插件管理菜单
-- 可单独执行，也可合并进 yshopadmin.sql
-- ----------------------------

CREATE TABLE IF NOT EXISTS `sys_addon` (
  `addon_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '插件ID',
  `name` varchar(64) NOT NULL COMMENT '插件标识',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '插件名称',
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本',
  `description` varchar(500) DEFAULT '' COMMENT '描述',
  `author` varchar(64) DEFAULT '' COMMENT '作者',
  `status` char(1) NOT NULL DEFAULT '0' COMMENT '状态（0停用 1启用）',
  `config` text COMMENT '配置JSON快照',
  `install_time` datetime DEFAULT NULL COMMENT '安装时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`addon_id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='插件注册表';

-- 插件管理菜单（系统管理下）
INSERT INTO `sys_menu` (`menu_id`, `menu_name`, `parent_id`, `order_num`, `url`, `target`, `menu_type`, `visible`, `is_refresh`, `perms`, `icon`, `create_by`, `create_time`, `update_by`, `update_time`, `remark`)
SELECT 2000, '插件管理', 1, 10, '/system/addon', '', 'C', '0', '1', 'system:addon:view', 'fa fa-puzzle-piece', 'admin', NOW(), '', NULL, '插件管理菜单'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_id` = 2000);

INSERT INTO `sys_menu` (`menu_id`, `menu_name`, `parent_id`, `order_num`, `url`, `target`, `menu_type`, `visible`, `is_refresh`, `perms`, `icon`, `create_by`, `create_time`, `update_by`, `update_time`, `remark`)
SELECT 2001, '插件查询', 2000, 1, '#', '', 'F', '0', '1', 'system:addon:list', '#', 'admin', NOW(), '', NULL, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_id` = 2001);

INSERT INTO `sys_menu` (`menu_id`, `menu_name`, `parent_id`, `order_num`, `url`, `target`, `menu_type`, `visible`, `is_refresh`, `perms`, `icon`, `create_by`, `create_time`, `update_by`, `update_time`, `remark`)
SELECT 2002, '插件安装', 2000, 2, '#', '', 'F', '0', '1', 'system:addon:install', '#', 'admin', NOW(), '', NULL, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_id` = 2002);

INSERT INTO `sys_menu` (`menu_id`, `menu_name`, `parent_id`, `order_num`, `url`, `target`, `menu_type`, `visible`, `is_refresh`, `perms`, `icon`, `create_by`, `create_time`, `update_by`, `update_time`, `remark`)
SELECT 2003, '插件卸载', 2000, 3, '#', '', 'F', '0', '1', 'system:addon:uninstall', '#', 'admin', NOW(), '', NULL, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_id` = 2003);

INSERT INTO `sys_menu` (`menu_id`, `menu_name`, `parent_id`, `order_num`, `url`, `target`, `menu_type`, `visible`, `is_refresh`, `perms`, `icon`, `create_by`, `create_time`, `update_by`, `update_time`, `remark`)
SELECT 2004, '插件启停', 2000, 4, '#', '', 'F', '0', '1', 'system:addon:edit', '#', 'admin', NOW(), '', NULL, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_id` = 2004);

INSERT INTO `sys_menu` (`menu_id`, `menu_name`, `parent_id`, `order_num`, `url`, `target`, `menu_type`, `visible`, `is_refresh`, `perms`, `icon`, `create_by`, `create_time`, `update_by`, `update_time`, `remark`)
SELECT 2005, '插件配置', 2000, 5, '#', '', 'F', '0', '1', 'system:addon:config', '#', 'admin', NOW(), '', NULL, ''
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_id` = 2005);
