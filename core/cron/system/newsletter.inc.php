<?php

use Model\Cron;

Cron::writeHR();

$sql = "select * from xcore_newsletter where deleted = 'no' and status = 'scheduled' and programming_date <= now() order by programming_date";
$newsletters = DB()->query($sql)->fetchAll();
$found = count($newsletters);
Cron::write("get all scheduled newsletter: {$found}");

// update status
foreach($newsletters as $n)
{
	DB('xcore_newsletter')->update(['status' => 'running'], $n['id']);
}

foreach($newsletters as $n)
{
	// @todo> add pixel tracker for view
	// @todo> add header List-Unsubscribe (List-Unsubscribe: <https://example.com/unsubscribe>)
	// @todo> sent email

}


Cron::writeHR();