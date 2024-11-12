CREATE TABLE IF NOT EXISTS `xcore_crontask` (
                                                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                `category` varchar(255) NOT NULL,
                                                `name` varchar(255) NOT NULL,
                                                `description` text NOT NULL,
                                                `script_path` text NOT NULL,
                                                `script_parameters` text NOT NULL,
                                                `repetition_months` varchar(255) NOT NULL DEFAULT '',
                                                `repetition_days` varchar(255) NOT NULL DEFAULT '',
                                                `repetition_hours` varchar(255) NOT NULL DEFAULT '',
                                                `repetition_minutes` varchar(255) NOT NULL DEFAULT '',
                                                `email_report` enum('yes','no') NOT NULL DEFAULT 'no',
                                                `email_report_subject` varchar(255) NOT NULL DEFAULT '',
                                                `email_report_recipients` text NOT NULL,
                                                `email_report_failure` enum('yes','no') NOT NULL DEFAULT 'no',
                                                `status` varchar(255) NOT NULL,
                                                `locked` enum('yes','no') NOT NULL DEFAULT 'no',
                                                `locked_date` datetime NOT NULL,
                                                `last_error_message` text NOT NULL,
                                                `last_execution_date_start` datetime NOT NULL,
                                                `last_execution_date_end` datetime NOT NULL,
                                                `priority` int(10) unsigned NOT NULL DEFAULT 0,
                                                `active` enum('yes','no') NOT NULL DEFAULT 'yes',
                                                `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                `created_at` datetime DEFAULT NULL,
                                                `created_by` varchar(255) DEFAULT NULL,
                                                `updated_at` datetime DEFAULT NULL,
                                                `updated_by` varchar(255) DEFAULT NULL,
                                                `deleted_at` datetime DEFAULT NULL,
                                                `deleted_by` varchar(255) DEFAULT NULL,
                                                PRIMARY KEY (`id`),
                                                KEY `deleted` (`deleted`),
                                                KEY `priority` (`priority`),
                                                KEY `active` (`active`),
                                                KEY `status` (`status`(250))
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

CREATE TABLE IF NOT EXISTS `xcore_globals` (
                                               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                               `package` varchar(255) NOT NULL DEFAULT 'default',
                                               `name` varchar(255) NOT NULL,
                                               `value` text NOT NULL,
                                               `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                               `created_at` datetime DEFAULT NULL,
                                               `created_by` varchar(255) DEFAULT NULL,
                                               `updated_at` datetime DEFAULT NULL,
                                               `updated_by` varchar(255) DEFAULT NULL,
                                               `deleted_at` datetime DEFAULT NULL,
                                               `deleted_by` varchar(255) DEFAULT NULL,
                                               PRIMARY KEY (`id`) USING BTREE,
                                               KEY `deleted` (`deleted`) USING BTREE,
                                               KEY `package` (`package`(250))
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

CREATE TABLE IF NOT EXISTS `xcore_group` (
                                             `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                             `name` varchar(255) NOT NULL,
                                             `description` varchar(255) DEFAULT NULL,
                                             `priority` int(10) unsigned NOT NULL,
                                             `access_backend` enum('yes','no') NOT NULL DEFAULT 'no',
                                             `access_frontend` enum('yes','no') NOT NULL DEFAULT 'no',
                                             `active` enum('yes','no') NOT NULL DEFAULT 'yes',
                                             `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                             `created_at` datetime DEFAULT NULL,
                                             `created_by` varchar(255) DEFAULT NULL,
                                             `updated_at` datetime DEFAULT NULL,
                                             `updated_by` varchar(255) DEFAULT NULL,
                                             `deleted_at` datetime DEFAULT NULL,
                                             `deleted_by` varchar(255) DEFAULT NULL,
                                             PRIMARY KEY (`id`) USING BTREE,
                                             KEY `deleted` (`deleted`) USING BTREE
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

INSERT INTO `xcore_group` (`id`, `name`, `description`, `priority`, `access_backend`, `access_frontend`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
VALUES
(1, 'Superadmin', 'group with max privilege', 0, 'yes', 'yes', 'yes', 'no', NOW(), NULL, NOW(), 'superadmin', NULL, NULL),
(2, 'Administrator', 'Admin group', 1, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);

CREATE TABLE IF NOT EXISTS `xcore_group_right` (
                                                   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                   `xcore_group_id` bigint(20) unsigned NOT NULL,
                                                   `xcore_plugin_id` bigint(20) unsigned NOT NULL,
                                                   `action` varchar(255) DEFAULT NULL,
                                                   `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                   `created_at` datetime NOT NULL,
                                                   `created_by` varchar(255) DEFAULT NULL,
                                                   `updated_at` datetime NOT NULL,
                                                   `updated_by` varchar(255) DEFAULT NULL,
                                                   `deleted_at` datetime NOT NULL,
                                                   `deleted_by` varchar(255) DEFAULT NULL,
                                                   PRIMARY KEY (`id`) USING BTREE,
                                                   KEY `deleted` (`deleted`) USING BTREE,
                                                   KEY `action` (`action`(250)),
                                                   KEY `xcore_group_id` (`xcore_group_id`),
                                                   KEY `xcore_module_id` (`xcore_plugin_id`) USING BTREE
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

CREATE TABLE IF NOT EXISTS `xcore_log` (
                                           `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                           `author` varchar(255) DEFAULT NULL,
                                           `application` varchar(255) NOT NULL DEFAULT 'backend',
                                           `date` datetime NOT NULL,
                                           `level` enum('info','warning','error','fatal','debug') NOT NULL DEFAULT 'info',
                                           `plugin` varchar(255) NOT NULL,
                                           `action` varchar(255) NOT NULL,
                                           `message` text NOT NULL,
                                           `values` longtext NOT NULL,
                                           `record_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                           `ip` varchar(255) NOT NULL DEFAULT '',
                                           `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                           `created_at` datetime NOT NULL,
                                           `created_by` varchar(255) DEFAULT NULL,
                                           PRIMARY KEY (`id`),
                                           KEY `date` (`date`),
                                           KEY `level` (`level`),
                                           KEY `deleted` (`deleted`),
                                           KEY `application` (`application`(250)),
                                           KEY `action` (`action`(250)),
                                           KEY `module` (`plugin`(250)) USING BTREE,
                                           KEY `author` (`author`(250))
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

CREATE TABLE IF NOT EXISTS `xcore_mail_template` (
                                                     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                     `category` varchar(255) NOT NULL,
                                                     `name` varchar(255) NOT NULL,
                                                     `description` varchar(255) NOT NULL DEFAULT '',
                                                     `sender_en` varchar(255) NOT NULL,
                                                     `subject_en` varchar(255) NOT NULL DEFAULT '',
                                                     `body_en` longtext NOT NULL,
                                                     `sender_fr` varchar(255) NOT NULL,
                                                     `subject_fr` varchar(255) NOT NULL DEFAULT '',
                                                     `body_fr` longtext NOT NULL,
                                                     `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                     `created_at` datetime DEFAULT NULL,
                                                     `created_by` varchar(255) DEFAULT NULL,
                                                     `updated_at` datetime DEFAULT NULL,
                                                     `updated_by` varchar(255) DEFAULT NULL,
                                                     `deleted_at` datetime DEFAULT NULL,
                                                     `deleted_by` varchar(255) DEFAULT NULL,
                                                     PRIMARY KEY (`id`),
                                                     KEY `category` (`category`(250)),
                                                     KEY `deleted` (`deleted`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

INSERT INTO `xcore_mail_template` (`id`, `category`, `name`, `description`, `sender_en`, `subject_en`, `body_en`, `sender_fr`, `subject_fr`, `body_fr`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
    (1, 'core/auth', 'password-reset', '', '', 'Password reset', 'Hi,<br>\r\n<br>\r\nYou have requested to reset your password,<br>\r\nin order to proceed please click on the link below :<br>\r\n<br>\r\n<a href="[[LINK]]">[[LINK]]</a>', '', 'Réinitialisation de votre mot de passe', 'Bonjour,<br>\r\n<br> \r\nVous avez demandé la réinitialisation de votre mot de passe,<br>\r\nafin de procéder merci de cliquer sur le lien ci-dessous :<br>\r\n<br>\r\n<a href="[[LINK]]">[[LINK]]</a>', 'no', NULL, NULL, NOW(), 'superadmin', NULL, NULL);


CREATE TABLE IF NOT EXISTS `xcore_menu` (
                                            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                            `name` varchar(255) NOT NULL,
                                            `position` int(10) unsigned NOT NULL,
                                            `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                            `created_at` datetime DEFAULT NULL,
                                            `created_by` varchar(255) DEFAULT NULL,
                                            `updated_at` datetime DEFAULT NULL,
                                            `updated_by` varchar(255) DEFAULT NULL,
                                            `deleted_at` datetime DEFAULT NULL,
                                            `deleted_by` varchar(255) DEFAULT NULL,
                                            PRIMARY KEY (`id`) USING BTREE,
                                            KEY `deleted` (`deleted`) USING BTREE,
                                            KEY `position` (`position`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;


INSERT INTO `xcore_menu` (`id`, `name`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
VALUES
    (1, 'ADMINISTRATION', 1, 'no', NULL, NULL, NOW(), '', NULL, NULL),
    (2, 'WEBSITE', 4, 'no', NULL, NULL, NOW(), '', NULL, NULL),
    (3, 'COMMUNICATION', 5, 'no', NULL, NULL, NOW(), '', NULL, NULL),
    (4, 'TOOLS', 6, 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);


CREATE TABLE IF NOT EXISTS `xcore_migration` (
                                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                 `version` varchar(12) NOT NULL DEFAULT '0',
                                                 `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                 `created_at` datetime DEFAULT NULL,
                                                 `created_by` varchar(255) DEFAULT NULL,
                                                 `updated_at` datetime DEFAULT NULL,
                                                 `updated_by` varchar(255) DEFAULT NULL,
                                                 `deleted_at` datetime DEFAULT NULL,
                                                 `deleted_by` varchar(255) DEFAULT NULL,
                                                 PRIMARY KEY (`id`),
                                                 KEY `deleted` (`deleted`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;


CREATE TABLE IF NOT EXISTS `xcore_plugin` (
                                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                              `xcore_menu_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                              `name` varchar(255) NOT NULL,
                                              `type` enum('normal','url','core') NOT NULL DEFAULT 'normal',
                                              `icon` varchar(255) NOT NULL,
                                              `route_prefix_name` varchar(255) NOT NULL,
                                              `actions` text NOT NULL,
                                              `url` varchar(255) DEFAULT NULL,
                                              `xcore_plugin_id` bigint(20) unsigned DEFAULT 0 COMMENT 'parent plugin',
                                              `position` int(10) unsigned NOT NULL,
                                              `visible` enum('yes','no') NOT NULL DEFAULT 'yes',
                                              `versioning` enum('yes','no') NOT NULL DEFAULT 'yes',
                                              `active` enum('yes','no') NOT NULL DEFAULT 'yes',
                                              `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                              `created_at` datetime DEFAULT NULL,
                                              `created_by` varchar(255) DEFAULT NULL,
                                              `updated_at` datetime DEFAULT NULL,
                                              `updated_by` varchar(255) DEFAULT NULL,
                                              `deleted_at` datetime DEFAULT NULL,
                                              `deleted_by` varchar(255) DEFAULT NULL,
                                              PRIMARY KEY (`id`) USING BTREE,
                                              KEY `deleted` (`deleted`) USING BTREE,
                                              KEY `position` (`position`),
                                              KEY `xcore_category_id` (`xcore_menu_id`) USING BTREE,
                                              KEY `active` (`active`),
                                              KEY `visible` (`visible`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 1, 'Menu', 'normal', 'bi bi-list', 'menu', 'list add edit delete', NULL, 0, 5, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (2, 1, 'Group', 'normal', 'bi bi-people-fill', 'group', 'list add edit delete', '', 0, 2, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (3, 1, 'User', 'normal', 'bi bi-person-fill', 'user', 'list add edit delete', NULL, 2, 3, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (4, 1, 'Right', 'normal', 'bi bi-shield-fill', 'right', 'list execute', NULL, 0, 4, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (5, 4, 'Log viewer', 'normal', 'bi bi-incognito', 'log', 'list purge', NULL, 0, 4, 'yes', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (6, 4, 'System info', 'normal', 'bi bi-info-circle', 'system-info', 'exec', NULL, 0, 10, 'yes', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (7, 1, 'Plugin', 'normal', 'bi bi-plugin', 'plugin', 'list add edit delete', NULL, 0, 6, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (8, 1, 'Versioning', 'normal', 'bi bi-file-diff', 'versioning', 'list replace delete', NULL, 0, 7, 'no', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (9, 4, 'Dumper', 'normal', 'bi bi-table', 'dumper', 'list add edit delete dump', '', 0, 7, 'no', 'no', 'yes', 'no', NULL, NULL, NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (10, 1, 'Dashboard', 'core', 'bi bi-dashboard', '', 'exec', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (11, 1, 'Bookmark', 'core', 'bi bi-bookmarks', 'user-bookmark', 'list delete toggle', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (12, 1, 'My profile', 'core', 'bi bi-person-fill', 'my-profile', 'exec', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (13, 3, 'Mail template', 'normal', 'bi bi-envelope-at', 'mail-template', 'list add edit delete ', NULL, 0, 9, 'yes', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (18, 1, 'Cron', 'normal', 'bi bi-gear-wide-connected', 'cron', 'list add edit delete execute', '', 0, 9, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (20, 1, 'Widget', 'normal', 'bi bi-card-heading', 'widget', 'list add edit delete', '', 0, 8, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (21, 1, 'Globals', 'normal', 'bi bi-database-gear', 'globals', 'list add edit delete', '', 0, 1, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (23, 1, 'Search', 'core', 'bi bi-search', 'user-search', 'list add delete', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (24, 4, 'Live updater', 'normal', 'bi bi-arrow-repeat', 'live-updater', 'execute', '', 0, 12, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (25, 2, 'Page manager', 'normal', 'bi bi-pencil-square', 'page-manager', 'list add edit delete duplicate rename move ', '', 0, 2, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (26, 2, 'Page zone', 'normal', 'bi bi-collection-fill', 'page-zone', 'list add edit delete', '', 0, 1, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);

UPDATE xcore_plugin SET actions = REPLACE(actions, " ", "\n");


CREATE TABLE IF NOT EXISTS `xcore_tag` (
                                           `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                           `signature` varchar(255) NOT NULL,
                                           `tag` varchar(255) NOT NULL,
                                           `record_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                           `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                           `created_at` datetime NOT NULL,
                                           `created_by` varchar(255) DEFAULT NULL,
                                           `updated_at` datetime NOT NULL,
                                           `updated_by` varchar(255) DEFAULT NULL,
                                           `deleted_at` datetime NOT NULL,
                                           `deleted_by` varchar(255) DEFAULT NULL,
                                           PRIMARY KEY (`id`),
                                           KEY `record_id` (`record_id`),
                                           KEY `deleted` (`deleted`),
                                           KEY `signature` (`signature`(250)),
                                           KEY `tag` (`tag`(250))
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;


CREATE TABLE IF NOT EXISTS `xcore_user` (
                                            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                            `xcore_group_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                            `avatar` text DEFAULT NULL,
                                            `language` char(5) NOT NULL DEFAULT 'en',
                                            `lastname` varchar(50) NOT NULL,
                                            `firstname` varchar(50) NOT NULL,
                                            `address` varchar(255) DEFAULT NULL,
                                            `address2` varchar(255) DEFAULT NULL,
                                            `address3` varchar(255) DEFAULT NULL,
                                            `zip_code` varchar(20) DEFAULT NULL,
                                            `city` varchar(255) DEFAULT NULL,
                                            `country` varchar(255) DEFAULT NULL,
                                            `phone` varchar(30) DEFAULT NULL,
                                            `mobile` varchar(30) DEFAULT NULL,
                                            `birthdate` date DEFAULT NULL,
                                            `service` varchar(255) DEFAULT NULL,
                                            `job` varchar(255) DEFAULT NULL,
                                            `email` varchar(255) NOT NULL,
                                            `login` varchar(30) NOT NULL,
                                            `password` varbinary(255) NOT NULL,
                                            `timezone` varchar(255) DEFAULT '',
                                            `format_date` varchar(255) DEFAULT NULL,
                                            `format_datetime` varchar(255) DEFAULT NULL,
                                            `last_connection_date` datetime DEFAULT NULL,
                                            `last_connection_application` varchar(255) DEFAULT NULL,
                                            `last_connection_ip` varchar(255) DEFAULT NULL,
                                            `password_token` varchar(255) DEFAULT NULL,
                                            `password_token_date` datetime DEFAULT NULL,
                                            `password_token_expiration_date` datetime DEFAULT NULL,
                                            `active` enum('yes','no') NOT NULL DEFAULT 'yes',
                                            `note` text NOT NULL,
                                            `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                            `created_at` datetime DEFAULT NULL,
                                            `created_by` varchar(255) DEFAULT NULL,
                                            `updated_at` datetime DEFAULT NULL,
                                            `updated_by` varchar(255) DEFAULT NULL,
                                            `deleted_at` datetime DEFAULT NULL,
                                            `deleted_by` varchar(255) DEFAULT NULL,
                                            PRIMARY KEY (`id`) USING BTREE,
                                            KEY `deleted` (`deleted`) USING BTREE,
                                            KEY `active` (`active`),
                                            KEY `xcore_group_id` (`xcore_group_id`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;


CREATE TABLE IF NOT EXISTS `xcore_user_bookmark` (
                                                     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                     `xcore_user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                                     `xcore_plugin_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                                     `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                     `created_at` datetime NOT NULL,
                                                     `created_by` varchar(255) DEFAULT NULL,
                                                     `updated_at` datetime DEFAULT NULL,
                                                     `updated_by` varchar(255) DEFAULT NULL,
                                                     `deleted_at` datetime DEFAULT NULL,
                                                     `deleted_by` varchar(255) DEFAULT NULL,
                                                     PRIMARY KEY (`id`) USING BTREE,
                                                     KEY `deleted` (`deleted`) USING BTREE,
                                                     KEY `xcore_user_id` (`xcore_user_id`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

CREATE TABLE IF NOT EXISTS `xcore_user_note` (
                                                 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                 `xcore_user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                                 `text` text NOT NULL,
                                                 `color` varchar(20) NOT NULL,
                                                 `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                 `created_at` datetime DEFAULT NULL,
                                                 `created_by` varchar(255) DEFAULT NULL,
                                                 `updated_at` datetime DEFAULT NULL,
                                                 `updated_by` varchar(255) DEFAULT NULL,
                                                 `deleted_at` datetime DEFAULT NULL,
                                                 `deleted_by` varchar(255) DEFAULT NULL,
                                                 PRIMARY KEY (`id`),
                                                 KEY `xcore_user_id` (`xcore_user_id`),
                                                 KEY `deleted` (`deleted`),
                                                 KEY `created_at` (`created_at`),
                                                 KEY `updated_at` (`updated_at`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;

CREATE TABLE IF NOT EXISTS `xcore_user_search` (
                                                   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                                   `xcore_user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                                   `xcore_plugin_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                                   `name` varchar(255) NOT NULL,
                                                   `url` longtext NOT NULL,
                                                   `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                                   `created_at` datetime NOT NULL,
                                                   `created_by` varchar(255) DEFAULT NULL,
                                                   `updated_at` datetime DEFAULT NULL,
                                                   `updated_by` varchar(255) DEFAULT NULL,
                                                   `deleted_at` datetime DEFAULT NULL,
                                                   `deleted_by` varchar(255) DEFAULT NULL,
                                                   PRIMARY KEY (`id`) USING BTREE,
                                                   KEY `deleted` (`deleted`) USING BTREE,
                                                   KEY `xcore_user_id` (`xcore_user_id`) USING BTREE
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;


CREATE TABLE IF NOT EXISTS `xcore_widget` (
                                              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                                              `xcore_plugin_id` bigint(20) unsigned NOT NULL DEFAULT 0,
                                              `name` varchar(255) NOT NULL,
                                              `method` varchar(255) NOT NULL,
                                              `autorefresh` enum('yes','no') NOT NULL DEFAULT 'no',
                                              `autorefresh_seconds` int(11) NOT NULL DEFAULT 5,
                                              `position` int(10) unsigned NOT NULL DEFAULT 0,
                                              `active` enum('yes','no') NOT NULL DEFAULT 'yes',
                                              `deleted` enum('yes','no') NOT NULL DEFAULT 'no',
                                              `created_at` datetime DEFAULT NULL,
                                              `created_by` varchar(255) DEFAULT NULL,
                                              `updated_at` datetime DEFAULT NULL,
                                              `updated_by` varchar(255) DEFAULT NULL,
                                              `deleted_at` datetime DEFAULT NULL,
                                              `deleted_by` varchar(255) DEFAULT NULL,
                                              PRIMARY KEY (`id`),
                                              KEY `active` (`active`),
                                              KEY `position` (`position`),
                                              KEY `xcore_plugin_id` (`xcore_plugin_id`),
                                              KEY `deleted` (`deleted`)
) ENGINE=@DB_ENGINE DEFAULT CHARSET=@DB_CHARSET COLLATE=@DB_COLLATE;


INSERT INTO `xcore_widget` (`id`, `xcore_plugin_id`, `name`, `method`, `autorefresh`, `autorefresh_seconds`, `position`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`)
VALUES
(1, 10, 'User bookmarks', '\\Core_Backend\\DashboardController::widgetBookmarkRender()', 'no', 0, 2, 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL),
(2, 10, 'Last connected', '\\Core_Backend\\DashboardController::widgetUserLastConnectedRender()', 'no', 30, 4, 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL),
(4, 24, 'Updater', ' \\Core\\Live_UpdaterController::widgetRender()', 'no', 0, 1, 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);



