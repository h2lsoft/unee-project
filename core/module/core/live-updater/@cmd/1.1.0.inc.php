<?php

// blacklist
$f = [];
$f['xcore_menu_id'] = 3;
$f['name'] = 'Blacklist';
$f['type'] = 'normal';
$f['icon'] = 'bi bi-shield-slash-fill';
$f['route_prefix_name'] = 'blacklist';
$f['actions'] = "list\nadd\nedit\ndelete";
$f['url'] = "";
$f['xcore_plugin_id'] = 0;
$f['position'] = 21;
$f['visible'] = 'yes';
$f['versioning'] = 'no';
$f['active'] = 'yes';
DB('xcore_plugin')->insert($f);

// alter error newsletter
$sql = <<<SQL
CREATE TABLE `xcore_blacklist` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`date` DATETIME NOT NULL,
	`email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_unicode_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=MyISAM
SQL;
DB()->query($sql);

// newsletter data
DB()->query("DROP TABLE xcore_newsletter_data");
$sql = <<<SQL
CREATE TABLE `xcore_newsletter_data` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_newsletter_id` BIGINT(20) UNSIGNED NOT NULL,
	`xcore_mailinglist_id` BIGINT(20) UNSIGNED NOT NULL,
	`xcore_mailinglist_subscriber_id` BIGINT(20) UNSIGNED NOT NULL,
	`date` DATETIME NOT NULL,
	`email` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`action` ENUM('sent','sent-error','unsubscribe','view','click','blacklisted') NOT NULL COLLATE 'utf8mb4_general_ci',
	`error_message` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
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
COLLATE='utf8mb4_general_ci'
ENGINE=MyISAM
SQL;
DB()->query($sql);



