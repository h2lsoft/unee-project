<?php

// user search
$sql ="ALTER TABLE `xcore_user_search` ADD COLUMN `default` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `url`";
DB()->query($sql);

$sql ="ALTER TABLE `xcore_user_search` ADD COLUMN `share` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `default`";
DB()->query($sql);

$sql = "UPDATE `xcore_plugin` SET `name`='User search', `type`='normal', `actions`='list\nadd\nadd-menu\nedit\ndelete', visible='yes', `position`=10  WHERE  `name`='Search'";
DB()->query($sql);

$sql = "ALTER TABLE `xcore_user_search`
			ADD COLUMN `default_xcore_group_id` BIGINT UNSIGNED NOT NULL DEFAULT (0) AFTER `default`,
			ADD COLUMN `share_xcore_group_id` BIGINT UNSIGNED NOT NULL DEFAULT (0) AFTER `share`";
DB()->query($sql);