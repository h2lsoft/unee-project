<?php


$sql ="INSERT INTO `xcore_widget` 
	(`xcore_plugin_id`, `name`, `method`, `autorefresh`, `autorefresh_seconds`, `position`, `active`, `deleted`) 
VALUES 
	(
		6, 38, 'Analytics', ' \\Core_Frontend\\AnalyticsController::widgetRender()', 'no', 30, 3, 'yes', 'no'
	)";
DB()->query($sql);


$sql = "INSERT INTO `xcore_crontask` VALUES (3, 'System', 'Newsletter', '', '/core/cron/newsletter.inc.php', '', '*', '*', '*', '0', 'no', '', '', 'no', '', 'no', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'yes', 'no', '2024-12-16 22:47:20', 'superadmin', NULL, NULL, NULL, NULL);";
DB()->query($sql);


$sql = "CREATE TABLE `xcore_newsletter` (
`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`date` DATETIME NOT NULL,
`category` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
`campaign` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
`expeditor_email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
`expeditor_email_label` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
`reply_to` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
`subject` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
`template_url` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
`link_add_parameters` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
`mailinglist_ids` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
`status` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
`programming_date` DATETIME NOT NULL,
`scheduler_date_start` DATETIME NOT NULL,
`scheduler_date_end` DATETIME NOT NULL,
`scheduler_report_email` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
`scheduler_report_email_recipients` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
`created_at` DATETIME NULL DEFAULT NULL,
`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
`updated_at` DATETIME NULL DEFAULT NULL,
`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
`deleted_at` DATETIME NULL DEFAULT NULL,
`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
PRIMARY KEY (`id`) USING BTREE,
INDEX `date` (`date`) USING BTREE,
INDEX `status` (`status`(250)) USING BTREE,
INDEX `deleted` (`deleted`) USING BTREE,
INDEX `category` (`category`(250)) USING BTREE,
INDEX `programming_date` (`programming_date`) USING BTREE
)
COLLATE='utf8mb4_general_ci' ENGINE=MyISAM";
DB()->query($sql);



$sql = "CREATE TABLE `xcore_newsletter_data` (
`id` BIGINT(20) UNSIGNED NOT NULL,
`xcore_newsletter_id` BIGINT(20) UNSIGNED NOT NULL,
`xcore_mailinglist_id` BIGINT(20) UNSIGNED NOT NULL,
`xcore_mailinglist_subscriber_id` BIGINT(20) UNSIGNED NOT NULL,
`date` DATETIME NOT NULL,
`action` ENUM('sent','unsubscribe','view','click') NOT NULL COLLATE 'utf8mb4_general_ci',
`url` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
`created_at` DATETIME NULL DEFAULT NULL,
`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
`updated_at` DATETIME NULL DEFAULT NULL,
`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
`deleted_at` DATETIME NULL DEFAULT NULL,
`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
PRIMARY KEY (`id`) USING BTREE,
INDEX `xcore_mailinglist_id` (`xcore_mailinglist_id`) USING BTREE,
INDEX `xcore_mailinglist_subscriber_id` (`xcore_mailinglist_subscriber_id`) USING BTREE,
INDEX `date` (`date`) USING BTREE,
INDEX `action` (`action`) USING BTREE,
INDEX `xcore_newsletter_id` (`xcore_newsletter_id`) USING BTREE,
INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci' ENGINE=MyISAM";
DB()->query($sql);

