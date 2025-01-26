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
	sleep(1);
}

// get all blacklist
$sql = "select email from xcore_blacklist where deleted = 'no'";
$blacklists = DB()->query($sql)->fetchAllOne();

$pause_sent_seconds_min = \Core\Globals::get('core.newsletter.pause_sent_seconds_min', 1, true);
$pause_sent_seconds_max = \Core\Globals::get('core.newsletter.pause_sent_seconds_max', 3, true);

$pause_steps_emails = \Core\Globals::get('core.newsletter.pause_steps_emails', 500, true);
$pause_steps_seconds_min = \Core\Globals::get('core.newsletter.pause_steps_seconds_min', 2*60, true);
$pause_steps_seconds_max = \Core\Globals::get('core.newsletter.pause_steps_seconds_max', 5*60, true);

$global_step_nb_sent = 0;
foreach($newsletters as $n)
{
	\Model\Newsletter::update(['scheduler_date_start' => now()], $n['id']);
	$date_start = now();

	// get all emails from mailing list
	$mailinglist_ids = str_replace(';', ',', $n['mailinglist_ids']);
	$sql = "select * from xcore_mailinglist_subscriber where deleted = 'no' and xcore_mailinglist_id in({$mailinglist_ids})";
	$subscribers = DB()->query($sql)->fetchAll();

	$dones = [];
	$skip_blacklist = [];
	$skip_error = [];
	$skip_dones = [];
	foreach($subscribers as $subscriber)
	{
		$subscriber['email'] = strtolower(trim($subscriber['email']));

		// blacklist
		$domain = explode('@', $subscriber['email']);
		if(in_array($subscriber['email'], $blacklists) || (count($domain) >= 2 && in_array("*@".end($domain), $blacklists)))
		{
			$skip_blacklist[] = $subscriber['email'];

			// log
			$f2 = [];
			$f2['xcore_newsletter_id'] = $n['id'];
			$f2['xcore_mailinglist_id'] = $subscriber['xcore_mailinglist_id'];
			$f2['xcore_mailinglist_subscriber_id'] = $subscriber['id'];
			$f2['date'] = now();
			$f2['email'] = $subscriber['email'];
			$f2['action'] = 'blacklisted';
			DB('xcore_newsletter_data')->insert($f2);


			continue;
		}

		// error
		if(empty($subscriber['email']) || !isEmail($subscriber['email']))
		{
			$skip_error[] = $subscriber['email'];

			// log
			$f2 = [];
			$f2['xcore_newsletter_id'] = $n['id'];
			$f2['xcore_mailinglist_id'] = $subscriber['xcore_mailinglist_id'];
			$f2['xcore_mailinglist_subscriber_id'] = $subscriber['id'];
			$f2['date'] = now();
			$f2['email'] = $subscriber['email'];
			$f2['action'] = 'sent-error';
			$f2['error_message'] = 'invalid email';
			DB('xcore_newsletter_data')->insert($f2);

			continue;
		}

		// dones
		if(in_array($subscriber['email'], $dones))
		{
			$skip_dones[] = $subscriber['email'];
			continue;
		}

		// get body
		if($n['template_url'][0] == '/')
			$body = file_get_contents(APP_PATH.$n['template_url']);
		else
			$body = file_get_contents($n['template_url']);

		// add pixel tracker for view
		$pix_track_url = \Core\Config::get('url')."/service/newsletter/pix-track/?nid={$n['id']}&mlid={$subscriber['xcore_mailinglist_id']}&mlids={$mailinglist_ids}&sid={$subscriber['id']}&email={$subscriber['email']}";
		$pix_track = '<img src="'.$pix_track_url.'" alt="pxtrk"  style="display:none;" width="1" height="1" border="0">';
		if(!str_contains($body, '</body>'))
			$body .= $pix_track;
		else
			$body = str_replace('</body>', $pix_track.'</body>', $body);

		// unsubscribe parsing + lang
		$unsubscribe_url = \Core\Config::get('url')."/service/newsletter/unsubscribe/?lang={$subscriber['language']}&nid={$n['id']}&mlid={$subscriber['xcore_mailinglist_id']}&mlids={$mailinglist_ids}&sid={$subscriber['id']}&email={$subscriber['email']}";

		$data = [];
		$data['pix_track_url'] = $pix_track_url;
		$data['unsubscribe_link'] = $unsubscribe_url;
		$data['email'] = $subscriber['email'];
		$data['firstname'] = $subscriber['firstname'];
		$data['lastname'] = $subscriber['lastname'];

		// header List-Unsubscribe (List-Unsubscribe: <https://example.com/unsubscribe>)
		$headers = [];
		$headers['List-Unsubscribe'] = "<{$unsubscribe_url}>";
		$headers['X-Campaign-Id'] = $n['id'];
		$headers['X-List-Id'] = $subscriber['xcore_mailinglist_id'];
		$headers['X-Subscriber-Id'] = $subscriber['id'];

		// sent email
		if(empty($n['expeditor_email_label']))
			$from = $n['expeditor_email'];
		else
			$from = [$n['expeditor_email_label'], $n['expeditor_email']];

		$to = $subscriber['email'];
		$subject = $n['subject'];

		// reply to
		$options = [];
		if(!empty($n['reply_to']))
		{
			$php_mail_options['reply-to'] = $n['reply_to'];
		}

		$json_added = [];
		$json_added['test_mode'] = false;
		$json_added['email'] = $subscriber['email'];
		$json_added['newsletter_id'] = $n['id'];
		$json_added['mailinglist_id'] = $subscriber['xcore_mailinglist_id'];
		$json_added['mailinglist_subscriber_id'] = $subscriber['id'];



		\Model\Newsletter::send($from, $to, $subject, $body, $data, $options, $headers, $json_added);
		$global_step_nb_sent++;
		$sent_error = false;
		$error_message = \Core\Mailer::getLastError();
		if(!empty($error_message))
		{
			$sent_error = true;
			\Core\Mailer::$last_error = '';
		}

		// log
		$f2 = [];
		$f2['xcore_newsletter_id'] = $n['id'];
		$f2['xcore_mailinglist_id'] = $subscriber['xcore_mailinglist_id'];
		$f2['xcore_mailinglist_subscriber_id'] = $subscriber['id'];
		$f2['date'] = now();
		$f2['email'] = $subscriber['email'];

		if($sent_error)
		{
			$f2['action'] = 'sent-error';
			$f2['error_message'] = $error_message;
		}
		else
		{
			$f2['action'] = 'sent';
		}

		DB('xcore_newsletter_data')->insert($f2);
		$dones[] = $subscriber['email'];


		// step pause
		if($global_step_nb_sent != $pause_steps_emails)
		{
			sleep(rand($pause_sent_seconds_min, $pause_sent_seconds_max));
		}
		else
		{
			$global_step_nb_sent = 0;
			sleep(rand($pause_steps_seconds_min, $pause_steps_seconds_max));
		}

	}

	// flag finished
	\Model\Newsletter::update(['status' => 'finished', 'scheduler_date_end' => now()], $n['id']);

	// send report
	if($n['scheduler_report_email'] == 'yes')
	{
		$date_end = now();

		$total_sent = count($dones);
		$total_blacklisted = count($skip_blacklist);
		$total_double = count($skip_dones);
		$total_error = count($skip_error);

		$report_message = "<b>{$n['subject']} #{$n['id']}</b><br>
<br>
	- start at : {$date_start}<br>
	- end at : {$date_end}<br>
	<hr>
	- total sent : {$total_sent}<br>
	- total blacklist : {$total_blacklisted}<br>
	- total double : {$total_double}<br>
	- total error : {$total_error}";

		$app_name = \Core\Config::get('name');
		$report_subject = "[$app_name] Report newsletter #{$n['id']}";

		\Core\Mailer::send("", $n['scheduler_report_email_recipients'], $report_subject, $report_message, [], [], [], [], false);
	}


}


Cron::writeHR();