<?php

$sql ="INSERT INTO `xcore_widget` (`xcore_plugin_id`, `name`, `method`, `autorefresh`, `autorefresh_seconds`, `position`, `active`, `deleted`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES (24, 'Updater', ' \\Core\\Live_UpdaterController::widgetRender()', 'no', 0, 1, 'yes', 'no', '2024-05-28 16:25:13', 'superadmin', '2024-05-28 16:25:13', '', NULL, NULL)";
DB()->query($sql);