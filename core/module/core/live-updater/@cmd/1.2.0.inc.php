<?php

$sql = <<<SQL
ALTER TABLE `xcore_newsletter`
	ADD COLUMN `subject2` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject`,
	ADD COLUMN `subject3` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject2`,
	ADD COLUMN `template_url2` VARCHAR(255) NOT NULL AFTER `template_url`,
	ADD COLUMN `template_url3` VARCHAR(255) NOT NULL AFTER `template_url2`;
SQL;
DB()->query($sql);


$sql = <<<SQL
ALTER TABLE `xcore_newsletter_data`
	CHANGE COLUMN `action` `action` ENUM('sent','sent-error','unsubscribe','view','click','blacklisted','conversion') NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `email`,
	ADD COLUMN `conversion_type` VARCHAR(255) NOT NULL DEFAULT '' AFTER `action`,
	ADD COLUMN `conversion_params` LONGTEXT NOT NULL AFTER `conversion_type`,
	ADD INDEX `conversion_type` (`conversion_type`);
SQL;
DB()->query($sql);



$sql = <<<SQL
ALTER TABLE `xcore_newsletter_data`
	CHANGE COLUMN `action` `action` ENUM('sent','sent-error','unsubscribe','view','click','blacklisted','conversion','refused', 'bounce', 'queue', 'spam', 'quota-exceeded', 'mail not exists') NOT NULL COLLATE 'utf8mb4_general_ci' AFTER `email`;
SQL;
DB()->query($sql);


$sql = <<<SQL
ALTER TABLE `xcore_newsletter_data`
	ADD COLUMN `user_agent` VARCHAR(255) NOT NULL DEFAULT '' AFTER `action`,
	ADD COLUMN `ip` VARCHAR(255) NOT NULL DEFAULT '' AFTER `user_agent`;
SQL;
DB()->query($sql);


$sql = <<<SQL
ALTER TABLE `xcore_newsletter`
	ADD COLUMN `return_path` VARCHAR(255) NOT NULL AFTER `reply_to`;
SQL;
DB()->query($sql);