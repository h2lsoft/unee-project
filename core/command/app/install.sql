CREATE TABLE `xcore_article` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`language` CHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`type` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`xcore_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`date` DATE NOT NULL,
	`title` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`content` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`header_image` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`resume` VARCHAR(120) NOT NULL COLLATE 'utf8mb4_general_ci',
	`source` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`status` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`archived` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`archived_date` DATETIME NOT NULL,
	`publication_date` DATETIME NOT NULL,
	`note` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `date` (`date`) USING BTREE,
	INDEX `status` (`status`(250)) USING BTREE,
	INDEX `publication_date` (`publication_date`) USING BTREE,
	INDEX `xcore_user_id` (`xcore_user_id`) USING BTREE,
	INDEX `language` (`language`) USING BTREE,
	INDEX `category` (`type`(250)) USING BTREE,
	INDEX `archived` (`archived`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_block` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` ENUM('content','file') NOT NULL DEFAULT 'content' COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`file_path` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`content` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`publication_date_start` DATETIME NOT NULL,
	`publication_date_end` DATETIME NOT NULL,
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `visible` (`active`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_crontask` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`script_path` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`script_parameters` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`repetition_months` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`repetition_days` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`repetition_hours` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`repetition_minutes` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`email_report` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`email_report_subject` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`email_report_recipients` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`email_report_failure` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`status` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`locked` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`locked_date` DATETIME NOT NULL,
	`last_error_message` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`last_execution_date_start` DATETIME NOT NULL,
	`last_execution_date_end` DATETIME NOT NULL,
	`priority` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `priority` (`priority`) USING BTREE,
	INDEX `active` (`active`) USING BTREE,
	INDEX `status` (`status`(250)) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_files` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`category_slug` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`date` DATE NOT NULL,
	`title` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`url` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`source` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`visible` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `visible` (`visible`) USING BTREE,
	INDEX `category` (`category`(250)) USING BTREE,
	INDEX `category_slug` (`category_slug`(250)) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_gallery` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`class` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `active` (`active`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_gallery_card` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_gallery_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`title` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`image` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`visible` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`position` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `visible` (`visible`) USING BTREE,
	INDEX `position` (`position`) USING BTREE,
	INDEX `xcore_slide_id` (`xcore_gallery_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_globals` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`package` VARCHAR(255) NOT NULL DEFAULT 'default' COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`value` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `package` (`package`(250)) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_group` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`priority` INT(10) UNSIGNED NOT NULL,
	`access_backend` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`access_frontend` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_group_right` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_group_id` BIGINT(20) UNSIGNED NOT NULL,
	`xcore_plugin_id` BIGINT(20) UNSIGNED NOT NULL,
	`action` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NOT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NOT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NOT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `action` (`action`(250)) USING BTREE,
	INDEX `xcore_group_id` (`xcore_group_id`) USING BTREE,
	INDEX `xcore_module_id` (`xcore_plugin_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_log` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`author` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`application` VARCHAR(255) NOT NULL DEFAULT 'backend' COLLATE 'utf8mb4_general_ci',
	`date` DATETIME NOT NULL,
	`level` ENUM('info','warning','error','fatal','debug','success') NOT NULL DEFAULT 'info' COLLATE 'utf8mb4_general_ci',
	`plugin` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`action` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`message` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`values` LONGTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`record_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`ip` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NOT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `date` (`date`) USING BTREE,
	INDEX `level` (`level`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `application` (`application`(250)) USING BTREE,
	INDEX `action` (`action`(250)) USING BTREE,
	INDEX `module` (`plugin`(250)) USING BTREE,
	INDEX `author` (`author`(250)) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_mailinglist` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_mailinglist_subscriber` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_mailinglist_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '1',
	`language` CHAR(5) NOT NULL DEFAULT 'en' COLLATE 'utf8mb4_general_ci',
	`date` DATETIME NOT NULL,
	`email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`firstname` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`lastname` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`unsubscribe_newsletter_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`unsubscribe_date` DATETIME NOT NULL,
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`, `xcore_mailinglist_id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `email` (`email`(250)) USING BTREE,
	INDEX `unsubscribe_newsletter_id` (`unsubscribe_newsletter_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_mail_template` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`sender_en` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`subject_en` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`body_en` LONGTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`sender_fr` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`subject_fr` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`body_fr` LONGTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `category` (`category`(250)) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_menu` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`icon` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`position` INT(10) UNSIGNED NOT NULL,
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `position` (`position`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_migration` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`date` DATETIME NULL DEFAULT NULL,
	`filename` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_page` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`language` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_general_ci',
	`xcore_page_zone_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`xcore_page_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`position` INT(10) UNSIGNED NOT NULL,
	`xcore_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`type` VARCHAR(255) NOT NULL DEFAULT 'page' COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`template` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`featured_image` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`headline` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`content` LONGTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`menu_visible` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`meta_title` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`meta_description` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`meta_keywords` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`meta_robot` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`meta_og_type` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`meta_og_image` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`sitemap_priority` VARCHAR(4) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`sitemap_change_freq` ENUM('','hourly','daily','weekly','monthly','yearly','always','never') NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`sitemap_pagination_pattern` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`sitemap_follow_url_pattern` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`sitemap_follow_url_priority` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`url` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`is_homepage` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`list_subpage` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`status` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`publication_date` DATETIME NOT NULL,
	`locked` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`locked_at` DATETIME NOT NULL,
	`locked_xcore_user_id` INT(11) NOT NULL DEFAULT '0',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `xcore_page_id` (`xcore_page_id`) USING BTREE,
	INDEX `status` (`status`(250)) USING BTREE,
	INDEX `menu_visible` (`menu_visible`) USING BTREE,
	INDEX `lang` (`language`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `is_homepage` (`is_homepage`) USING BTREE,
	INDEX `xcore_zone_id` (`xcore_page_zone_id`) USING BTREE,
	INDEX `type` (`type`(250)) USING BTREE,
	INDEX `xcore_user_id` (`xcore_user_id`) USING BTREE,
	INDEX `created_at` (`created_at`) USING BTREE,
	INDEX `updated_at` (`updated_at`) USING BTREE,
	INDEX `position` (`position`) USING BTREE,
	INDEX `publication_date` (`publication_date`) USING BTREE,
	INDEX `locked` (`locked`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_page_visitor` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`date` DATETIME NOT NULL,
	`user_ip` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`user_agent` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`user_device` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`user_browser` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`user_browser_version` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`user_os` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`user_os_version` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`user_os_label` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`user_lang` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`referer` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`url` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`url_domain` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`url_query_string` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `date` (`date`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_page_zone` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`website` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`position` INT(10) UNSIGNED NOT NULL,
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `position` (`position`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_pattern` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`category` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`pattern` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`content` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `category` (`category`(250)) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `active` (`active`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_plugin` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_menu_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`type` ENUM('normal','url','core') NOT NULL DEFAULT 'normal' COLLATE 'utf8mb4_general_ci',
	`icon` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`route_prefix_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`actions` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`url` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`xcore_plugin_id` BIGINT(20) UNSIGNED NULL DEFAULT '0' COMMENT 'parent plugin',
	`position` INT(10) UNSIGNED NOT NULL,
	`visible` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`versioning` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `position` (`position`) USING BTREE,
	INDEX `xcore_category_id` (`xcore_menu_id`) USING BTREE,
	INDEX `active` (`active`) USING BTREE,
	INDEX `visible` (`visible`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_slider` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`description` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`class` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`js` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_speed` INT(10) UNSIGNED NOT NULL DEFAULT '400',
	`option_allow_touch` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_slider_per_view` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	`option_centered_slides` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_navigation` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_pagination` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_effect` ENUM('fade','coverflow','flip','card','creative','cube','none') NOT NULL DEFAULT 'fade' COLLATE 'utf8mb4_general_ci',
	`option_loop` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_direction` ENUM('horizontal','vertical') NOT NULL DEFAULT 'horizontal' COLLATE 'utf8mb4_general_ci',
	`option_gap` INT(11) NOT NULL DEFAULT '0',
	`option_grab_cursor` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_keyboard` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_mousewheel` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_zoom` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`option_autoplay` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`option_autoplay_delay_ms` INT(11) NOT NULL DEFAULT '3000',
	`option_breakpoints` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `active` (`active`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_slider_card` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_slider_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`image` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
	`title` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`title_color` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`text` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`text_color` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`button_text` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`button_text_color` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`button_bg_color` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`button_href` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`button_class` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`visible` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`position` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `visible` (`visible`) USING BTREE,
	INDEX `xcore_slide_id` (`xcore_slider_id`) USING BTREE,
	INDEX `position` (`position`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_tag` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`signature` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`tag` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`record_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NOT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NOT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NOT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `record_id` (`record_id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `signature` (`signature`(250)) USING BTREE,
	INDEX `tag` (`tag`(250)) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_user` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_group_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`avatar` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`language` CHAR(5) NOT NULL DEFAULT 'en' COLLATE 'utf8mb4_general_ci',
	`lastname` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`firstname` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`company` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`address` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`address2` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`address3` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`zip_code` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`city` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`country` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`phone` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`mobile` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`birthdate` DATE NULL DEFAULT NULL,
	`service` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`job` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`login` VARCHAR(30) NOT NULL COLLATE 'utf8mb4_general_ci',
	`password` VARBINARY(255) NOT NULL,
	`timezone` VARCHAR(255) NULL DEFAULT '' COLLATE 'utf8mb4_general_ci',
	`format_date` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`format_datetime` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`last_connection_date` DATETIME NULL DEFAULT NULL,
	`last_connection_application` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`last_connection_ip` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`password_token` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`password_token_date` DATETIME NULL DEFAULT NULL,
	`password_token_expiration_date` DATETIME NULL DEFAULT NULL,
	`bio` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`note` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `active` (`active`) USING BTREE,
	INDEX `xcore_group_id` (`xcore_group_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


CREATE TABLE `xcore_user_bookmark` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`xcore_plugin_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`position` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NOT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `xcore_user_id` (`xcore_user_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_user_note` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`text` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`color` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `xcore_user_id` (`xcore_user_id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `created_at` (`created_at`) USING BTREE,
	INDEX `updated_at` (`updated_at`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_user_search` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`xcore_plugin_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`url` LONGTEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NOT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE,
	INDEX `xcore_user_id` (`xcore_user_id`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_versioning` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_user_id` BIGINT(20) NULL DEFAULT NULL,
	`date` DATETIME NOT NULL,
	`application` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`table` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`record_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`data_json` TEXT NOT NULL COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `xcore_user_id` (`xcore_user_id`) USING BTREE,
	INDEX `application` (`application`(250)) USING BTREE,
	INDEX `record_id` (`record_id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';

CREATE TABLE `xcore_widget` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`xcore_plugin_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`method` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`autorefresh` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`autorefresh_seconds` INT(11) NOT NULL DEFAULT '5',
	`position` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`active` ENUM('yes','no') NOT NULL DEFAULT 'yes' COLLATE 'utf8mb4_general_ci',
	`deleted` ENUM('yes','no') NOT NULL DEFAULT 'no' COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT NULL,
	`created_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`updated_at` DATETIME NULL DEFAULT NULL,
	`updated_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`deleted_by` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `active` (`active`) USING BTREE,
	INDEX `position` (`position`) USING BTREE,
	INDEX `xcore_plugin_id` (`xcore_plugin_id`) USING BTREE,
	INDEX `deleted` (`deleted`) USING BTREE
)
COLLATE='utf8mb4_general_ci';


INSERT INTO `xcore_group` (`id`, `name`, `description`, `priority`, `access_backend`, `access_frontend`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 'Superadmin', 'group with max privilege', 0, 'yes', 'yes', 'yes', 'no', NOW(), NULL, NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_group` (`id`, `name`, `description`, `priority`, `access_backend`, `access_frontend`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (2, 'Administrator', 'Admin group', 1, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);

INSERT INTO `xcore_mail_template` (`id`, `category`, `name`, `description`, `sender_en`, `subject_en`, `body_en`, `sender_fr`, `subject_fr`, `body_fr`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 'core/auth', 'password-reset', '', '', 'Password reset', 'Hi,<br>\n<br>\nYou have requested to reset your password,<br>\nin order to proceed please click on the link below :<br>\n<br>\n<a href="[[LINK]]">[[LINK]]</a>', '', 'Réinitialisation de votre mot de passe', 'Bonjour,<br>\n<br>\nVous avez demandé la réinitialisation de votre mot de passe,<br>\nafin de procéder merci de cliquer sur le lien ci-dessous :<br>\n<br>\n<a href="[[LINK]]">[[LINK]]</a>', 'no', NULL, NULL, NOW(), 'superadmin', NULL, NULL);

INSERT INTO `xcore_menu` (`id`, `name`, `icon`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 'ADMINISTRATION', 'bi bi-gear', 1, 'no', NULL, NULL, NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_menu` (`id`, `name`, `icon`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (2, 'WEBSITE', 'bi bi-globe', 4, 'no', NULL, NULL, NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_menu` (`id`, `name`, `icon`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (3, 'COMMUNICATION', 'bi bi-megaphone', 5, 'no', NULL, NULL, NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_menu` (`id`, `name`, `icon`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (4, 'TOOLS', 'bi bi-wrench-adjustable-circle', 6, 'no', NOW(), 'superadmin', NULL, 'superadmin', NULL, NULL);
INSERT INTO `xcore_menu` (`id`, `name`, `icon`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (5, 'LINKS', 'bi bi-link', 7, 'no', NOW(), 'superadmin', NULL, NULL, NULL, NULL);



INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 1, 'Menu', 'normal', 'bi bi-list', 'menu', 'list\nadd\nedit\ndelete', NULL, 0, 5, 'yes', 'yes', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (2, 1, 'Group', 'normal', 'bi bi-people-fill', 'group', 'list\nadd\nedit\ndelete', '', 0, 2, 'yes', 'yes', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (3, 1, 'User', 'normal', 'bi bi-person-fill', 'user', 'list\nadd\nedit\ndelete', NULL, 2, 3, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (4, 1, 'Right', 'normal', 'bi bi-shield-fill', 'right', 'list\nexecute', NULL, 0, 4, 'yes', 'yes', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (5, 4, 'Log viewer', 'normal', 'bi bi-incognito', 'log', 'list\npurge', NULL, 0, 1, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (6, 4, 'System info', 'normal', 'bi bi-info-circle', 'system-info', 'exec', NULL, 0, 4, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (7, 1, 'Plugin', 'normal', 'bi bi-plugin', 'plugin', 'list\nadd\nedit\ndelete', NULL, 0, 6, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (8, 4, 'Versioning', 'normal', 'bi bi-file-diff', 'versioning', 'list\nview\nreplace\ndelete', '', 0, 3, 'yes', 'no', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (9, 4, 'Dumper', 'normal', 'bi bi-table', 'dumper', 'list\nadd\nedit\ndelete\ndump', '', 0, 2, 'no', 'yes', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (10, 1, 'Dashboard', 'core', 'bi bi-dashboard', '', 'exec', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (11, 1, 'Bookmark', 'core', 'bi bi-bookmarks', 'user-bookmark', 'list\ndelete\ntoggle\nreorder', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (12, 1, 'My profile', 'core', 'bi bi-person-fill', 'my-profile', 'exec', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (13, 3, 'Mail template', 'normal', 'bi bi-envelope-at', 'mail-template', 'list\nadd\nedit\ndelete ', NULL, 0, 9, 'yes', 'yes', 'yes', 'no', NULL, NULL, NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (18, 1, 'Cron', 'normal', 'bi bi-gear-wide-connected', 'cron', 'list\nadd\nedit\ndelete execute', '', 0, 9, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NULL, '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (20, 1, 'Widget', 'normal', 'bi bi-card-heading', 'widget', 'list\nadd\nedit\ndelete', '', 0, 8, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (21, 1, 'Globals', 'normal', 'bi bi-database-gear', 'globals', 'list\nadd\nedit\ndelete', '', 0, 1, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (23, 1, 'Search', 'core', 'bi bi-search', 'user-search', 'list\nadd\ndelete', NULL, 0, 0, 'no', 'no', 'yes', 'no', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (24, 4, 'Live updater', 'normal', 'bi bi-arrow-repeat', 'live-updater', 'execute', '', 0, 5, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (25, 2, 'Page', 'normal', 'bi bi-pencil-square', 'page', 'list\nadd_direct\nedit\ndelete\nrename', '', 0, 4, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (26, 2, 'Zone', 'normal', 'bi bi-collection-fill', 'zone', 'list\nadd\nedit\ndelete', '', 0, 1, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (27, 2, 'Pattern', 'normal', 'bi bi-code-square', 'pattern', 'list\nadd\nedit\ndelete', '', 0, 3, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (28, 2, 'Article', 'normal', 'bi bi-newspaper', 'article', 'list\nadd\nedit\ndelete', '', 0, 5, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (29, 4, 'File manager', 'normal', 'bi bi-folder-symlink-fill', 'file-manager', 'index\nrename\nunlink\ndir\nupload\nupload-image', '', 0, 6, 'no', 'no', 'yes', 'no', NOW(), 'superadmin', NULL, '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (30, 2, 'Slider', 'normal', 'bi bi-images', 'slider', 'list\nadd\nedit\ndelete all', '', 0, 6, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (31, 2, 'Slider card', 'normal', 'bi bi-images', 'slider-card', 'list\nadd\nedit\ndelete', '', 0, 7, 'no', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (32, 2, 'Files', 'normal', 'bi bi-folder-fill', 'files', 'list\nadd\nedit\ndelete', '', 0, 8, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (33, 2, 'Block', 'normal', 'bi bi-credit-card-2-front-fill', 'block', 'list\nadd\nedit\ndelete all', '', 0, 2, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (34, 2, 'Gallery', 'normal', 'bi bi-file-image-fill', 'gallery', 'list\nadd\nedit\ndelete all', '', 0, 15, 'yes', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (35, 2, 'Gallery card', 'normal', 'bi bi-file-image-fill', 'gallery-card', 'list\nadd\nedit\ndelete', '', 0, 16, 'no', 'no', 'yes', 'no', NOW(), 'superadmin', NULL, NULL, NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (36, 3, 'Mailing-list', 'normal', 'bi bi-person-lines-fill', 'mailinglist', 'list\nadd\nedit\ndelete download', '', 0, 17, 'yes', 'yes', 'yes', 'no', NOW(), 'superadmin', NOW(), '', NULL, NULL);
INSERT INTO `xcore_plugin` (`id`, `xcore_menu_id`, `name`, `type`, `icon`, `route_prefix_name`, `actions`, `url`, `xcore_plugin_id`, `position`, `visible`, `versioning`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (37, 3, 'Mailing-list subscriber', 'normal', 'bi bi-person-lines-fill', 'mailinglist-subscriber', 'list\nadd\nedit\ndelete', '', 0, 18, 'no', 'no', 'yes', 'no', NOW(), 'superadmin', NOW(), 'superadmin', NULL, NULL);



INSERT INTO `xcore_widget` (`id`, `xcore_plugin_id`, `name`, `method`, `autorefresh`, `autorefresh_seconds`, `position`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 10, 'User bookmarks', '\\Core_Backend\\DashboardController::widgetBookmarkRender()', 'no', 0, 2, 'yes', 'no', '2024-05-28 16:25:13', 'superadmin', '2024-05-28 16:25:13', '', NULL, NULL);
INSERT INTO `xcore_widget` (`id`, `xcore_plugin_id`, `name`, `method`, `autorefresh`, `autorefresh_seconds`, `position`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (2, 10, 'Last connected', '\\Core_Backend\\DashboardController::widgetUserLastConnectedRender()', 'no', 30, 4, 'yes', 'no', '2024-05-28 16:25:13', 'superadmin', '2024-05-28 16:25:13', '', NULL, NULL);
INSERT INTO `xcore_widget` (`id`, `xcore_plugin_id`, `name`, `method`, `autorefresh`, `autorefresh_seconds`, `position`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (4, 24, 'Updater', ' \\Core\\Live_UpdaterController::widgetRender()', 'no', 0, 1, 'yes', 'no', '2024-05-28 16:25:13', 'superadmin', '2024-05-28 16:25:13', '', NULL, NULL);


INSERT INTO `xcore_page_zone` (`id`, `name`, `website`, `position`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (1, 'MAIN MENU', '', 1, 'no', NOW(), 'superadmin', NULL, NULL, NULL, NULL);
