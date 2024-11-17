<?php

use Model\Cron;

Cron::writeHR();

// page start
Cron::write("Update pages scheduled start");
$sql = "UPDATE xcore_page SET status = 'published' WHERE deleted = 'no' and status = 'scheduled' and publication_date >= NOW()";
$found = DB()->query($sql)->rowCount();
Cron::write("pages updated: {$found}");

// article start
Cron::write("Update articles scheduled start");
$sql = "UPDATE xcore_article SET status = 'published' WHERE deleted = 'no' and status = 'scheduled' and publication_date >= NOW()";
$found = DB()->query($sql)->rowCount();
Cron::write("articles updated: {$found}");

Cron::writeHR();