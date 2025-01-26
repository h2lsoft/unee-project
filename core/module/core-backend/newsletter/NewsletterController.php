<?php

namespace Plugin\Core_Backend;

use Core\Html;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NewsletterController extends \Core\Controller {

	public string $table = 'xcore_newsletter';
	public string $object_label = 'newsletter';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table);
		$datagrid->qSelect("(select count(*) from xcore_newsletter_data where deleted = 'no' and xcore_newsletter_id=xcore_newsletter.id and action='sent') as total_sent");
		$datagrid->qSelect("(select count(*) from xcore_newsletter_data where deleted = 'no' and xcore_newsletter_id=xcore_newsletter.id and action='blacklisted') as total_blacklisted");
		$datagrid->qSelect("(select count(DISTINCT xcore_mailinglist_subscriber_id) from xcore_newsletter_data where deleted = 'no' and xcore_newsletter_id=xcore_newsletter.id and action='view') as total_opened");
		$datagrid->qSelect("(select count(DISTINCT xcore_mailinglist_subscriber_id) from xcore_newsletter_data where deleted = 'no' and xcore_newsletter_id=xcore_newsletter.id and action='click') as total_clicked");
		$datagrid->qSelect("(select count(DISTINCT xcore_mailinglist_subscriber_id) from xcore_newsletter_data where deleted = 'no' and xcore_newsletter_id=xcore_newsletter.id and action='unsubscribe') as total_unsubscribe");

		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddDatetime('date');
		$datagrid->searchAddSelectSql('category');
		$datagrid->searchAddText('campaign');
		$datagrid->searchAddText('subject');
		$datagrid->searchAddSelectSql('status');
		$datagrid->searchAddTagManager();

		// columns
		$datagrid->addColumn('id', '',  true, 'min');
		$datagrid->addColumnDatetime('date', '', '', true, 'min');
		$datagrid->addColumn('category', '',  true, 'min');
		$datagrid->addColumn('campaign', '',  false, 'min');
		$datagrid->addColumn('subject', '',  false, 'left');
		$datagrid->addColumnHtml('status', '',  false, 'min center');
		$datagrid->addColumnDatetime('programming_date', 'programming',  '', false, 'min center');
		$datagrid->addColumn('total_blacklisted', 'Blacklisted', false, 'min center');
		$datagrid->addColumn('total_sent', 'Sent',  false, 'min center');
		$datagrid->addColumn('total_opened', 'Open',  false, 'min center');
		$datagrid->addColumn('total_clicked', 'Click',  false, 'min center');
		$datagrid->addColumn('total_unsubscribe', 'Unsubscribe',  false, 'min center');
		$datagrid->addColumnTags();

		// hookData
		$datagrid->hookData(function($row){

			$row['total_blacklisted'] = int_formatX((int)$row['total_blacklisted']);
			if(!$row['total_blacklisted'])$row['total_blacklisted'] = '-';

			$row['total_sent'] = int_formatX((int)$row['total_sent']);
			if(!$row['total_sent'])$row['total_sent'] = '-';

			$row['total_opened'] = int_formatX((int)$row['total_opened']);
			if(!$row['total_opened'])$row['total_opened'] = '-';

			$row['total_clicked'] = int_formatX((int)$row['total_clicked']);
			if(!$row['total_clicked'])$row['total_clicked'] = '-';

			$row['total_unsubscribe'] = int_formatX((int)$row['total_unsubscribe']);
			if(!$row['total_unsubscribe'])$row['total_unsubscribe'] = '-';


			$class = '';
			if($row['status'] == 'draft') $class = 'bg-warning text-white';
			elseif($row['status'] == 'scheduled') $class = 'bg-info text-white';
			elseif($row['status'] == 'running') $class = 'bg-danger text-white';
			elseif($row['status'] == 'finished') $class = 'bg-success text-white';


			$status = $row['status'];
			$row['status'] = "<span class='badge {$class}'>{$row['status']}</span>";
			if(!in_array($status, ['draft', 'scheduled']))
			{
				$row['btn_edit_class'] = 'd-none';
			}


			return $row;
		});


		$data = [];
		$data['content'] = $datagrid->render();
		return View('@plugin-content', $data);
	}

	/**
	 * @route /@backend/@module/add/ {method:"GET|POST", controller:"add"}
	 * @route /@backend/@module/edit/{id}/ {method:"GET|PUT", controller:"edit"}
	 */
	public function getForm(int $id=0)
	{
		$form = new \Component\Form();
		$form->linkController($this, $id);
		$form->loadAssetsJs(['form.js']);

		if($form->is_editing)
		{
			$status = $form->getValue('status');
			if(!in_array($status, ['draft', 'scheduled']))
			{
				$form->addHtml("<div class='py-1 mb-2 bg-danger text-white text-center'><i18n>You can't edit newsletter (status not compatible)</i18n></div>");
				$form->addError('campaign', "You can't edit newsletter (status not compatible)");
			}
		}

		$form->addText('category', '', true, ['class' => 'ucfirst'])->datalist();
		$form->addText('campaign', '', true, ['class' => 'ucfirst'])->datalist();

		$form->addHeader('Newsletter');
		$form->addEmail('expeditor_email', 'Expeditor email', true)->datalist();
		$form->addText('expeditor_email_label', 'Expeditor label', false)->datalist();
		$form->addEmail('reply_to', 'Reply to', false)->datalist();

		$form->addText('subject', 'Subject', true);
		$form->addFileBrowser('template_url', 'Template url', true, 'newsletter');
		// $form->addTextarea('link_add_parameters', 'Link add parameters', false, ['placeholder' => "utm_campaign=my_campaign\nutm_medium=email"])->setHelp("One parameter by line");

		$sql = "select 
						id as value, 
						concat(name, ' (',(select count(*) from xcore_mailinglist_subscriber where deleted = 'no' and xcore_mailinglist_id = xcore_mailinglist.id),')') as label 
				from 
						xcore_mailinglist 
				where 
						deleted = 'no' and
						name not like '%blacklist%'
				order by
						label";
		$opts = DB()->query($sql)->fetchAll();


		if($form->is_editing)
		{
			$mls = $form->getValue('mailinglist_ids');
			$mls = explode(';', $mls);

			for($i=0; $i < count($opts); $i++)
			{
				if(in_array($opts[$i]['value'], $mls))
					$opts[$i]['selected'] = true;
			}
		}

		$form->addSelect('mailinglist_ids[]', 'Mailing-list', false, $opts, false, ['multiple' => 'multiple', 'size' => 8]);


		// report html
		$form->addHeader('Report');
		$form->addSwitch('scheduler_report_email', 'Email report', false);
		$form->addText('scheduler_report_email_recipients', 'Email report recipients', false, ['placeholder' => "email1@mail.com, email2@mail.com", 'disabled' => 'disabled'])->datalist();


		// test mode
		$form->addHeader('header_test', "Test mode");
		$form->addSwitch('mode_test', 'Send test email', false);
		$form->addText('mode_test_email', 'Test email', false, ['placeholder' => "email1@mail.com, email2@mail.com", 'disabled' => 'disabled'])->setHelp("Comma separated");


		// scheduled
		$form->addHeader('header_scheduler', "Scheduler");
		$opts = [];
		$opts[] = ['label' => 'Draft', 'value' => 'draft'];
		$opts[] = ['label' => 'Scheduled', 'value' => 'scheduled'];
		$form->addRadio('status', '', true, $opts)->setValue('draft');
		$form->addDatetime('programming_date', 'Programming', false, ['disabled' => 'disabled']);

		$form->addHeader('Tags');
		$form->addTagManager();


		// validation
		if($form->isSubmitted())
		{
			$data = [];

			// mailinglist
			$mls = post('mailinglist_ids', []);
			if(!count($mls))
			{
				$form->validator->addError("Mailing-list is required", [], 'mailinglist_ids[]');
			}

			// scheduler_report_email
			$form->validator->requiredIf('scheduler_report_email', 'yes', 'scheduler_report_email_recipients');

			// mode_test
			$form->validator->requiredIf('mode_test', 'yes', 'mode_test_email');


			// scheduled
			if(post('status') == 'scheduled')
			{
				$form->validator->input('programming_date')->required();
			}

			// check [unsubscribe_link] parameter
			if(!empty(post('template_url')))
			{
				$file_url = post('template_url');
				if(post('template_url')[0] == '/')
					$file_url = APP_PATH.$file_url;

				$contents = file_get_contents($file_url);
				if(!str_contains($contents, '[[unsubscribe_link]]'))
				{
					$form->validator->addError("`[[unsubscribe_link]]` not found in template", [], 'template_url');
				}
			}

			// valid
			if($form->isValid())
			{
				$mls = trim(join(';', post('mailinglist_ids')));

				$added = [];
				if($form->is_adding)
				{
					$added['date'] = now();
				}

				// test mode
				if(post('mode_test') == 'yes')
				{
					$emails = post('mode_test_email');

					$form->validator->addError("Test mode, please check your mail `{$emails}`", [], 'mode_test');

					// send test email
					$emails = str_erase(' ', $emails);
					$emails = str_replace(';', ',', $emails);
					$emails = explode(',', $emails);

					$from = post('expeditor_email');
					$from_label = post('expeditor_email_label');
					if(!empty($from_label))
						$from = [$from_label, $from];

					$subject = post('subject');

					// parse body
					$file_name = post('template_url');
					if($file_name[0] === '/')
						$file_name = APP_PATH.$file_name;
					$body_original = file_get_contents($file_name);
					$body = $body_original;

					foreach($emails as $to)
					{
						$json = [];
						$json['test_mode'] = (post('mode_test') == 'yes');
						$php_mail_options = [];
						if(($reply_to = post('reply_to')) != '')
						{
							$php_mail_options['reply-to'] = $reply_to;
						}

						\Model\Newsletter::send($from, $to, $subject, $body, $data, $php_mail_options, [], $json);
					}

				}
				else
				{
					$form->save(['mode_test', 'mode_test_email', 'mailinglist_ids[]'], $added);

					// update list
					$sql = "update xcore_newsletter set mailinglist_ids = :mls where id = :id";
					DB()->query($sql, ['mls' => $mls, 'id' => $form->id]);

				}
			}

			return $form->json();
		}

		return $form->render();
	}

	/**
	 * display pixel tracking
	 * @route /service/newsletter/pix-track/
	 */
	public static function pixTrackImageUrl()
	{
		$nid = (int)get('nid');
		$mlid = (int)get('mlid');
		$mlids = (int)get('mlids');
		$sid = (int)get('sid');
		$email = get('email');

		if($nid && $sid && isEmail($email))
		{
			$f = [];
			$f['xcore_newsletter_id'] = $nid;
			$f['xcore_mailinglist_id'] = $mlid;
			$f['xcore_mailinglist_subscriber_id'] = $sid;
			$f['date'] = now();
			$f['email'] = $email;
			$f['action'] = 'view';

			DB('xcore_newsletter_data')->insert($f);
		}

		$transparent_gif = base64_decode("R0lGODlhAQABAIAAAP///////yH5BAEAAAAALAAAAAABAAEAAAIBRAA7");

		return new \Core\Response($transparent_gif, 200, ['Content-Type' => 'image/gif']);
	}

	/**
	 * @route /service/newsletter/link/
	 */
	public static function redirectUrl()
	{
		$uri = get('uri');
		if(empty($uri))
			die("Error: uri not defined");

		$uri = json_decode(base64_decode(urldecode($uri)), true);

		if(!is_array($uri))
			die("Error: `uri` not defined");

		if(!isset($uri['url']))
			die("Error: `url` parameter not correct");

		if(!isset($uri['test_mode']) || !is_bool($uri['test_mode']))
			die("Error: `test_mode` parameter not correct");

		// real_mode
		if(!$uri['test_mode'])
		{
			$f = [];
			$f['xcore_newsletter_id'] = $uri['newsletter_id'];
			$f['xcore_mailinglist_id'] = $uri['mailinglist_id'];
			$f['xcore_mailinglist_subscriber_id'] = $uri['mailinglist_subscriber_id'];
			$f['date'] = now();
			$f['email'] = $uri['email'];
			$f['action'] = 'click';
			$f['url'] = $uri['url'];

			DB('xcore_newsletter_data')->insert($f);
		}

		return new RedirectResponse($uri['url']);
	}


	/**
	 * display pixel tracking
	 * @route /service/newsletter/unsubscribe/
	 */
	public static function unsubscribeRender()
	{
		$lang = get('lang', \Core\Config::get('frontend/langs')[0][0]);

		$nid = (int)get('nid');
		$mlid = (int)get('mlid');
		$mlids = get('mlids');
		$sid = (int)get('sid');
		$email = get('email');

		if($nid && $sid && isEmail($email))
		{
			// unsubscribe from all mailing list
			$sql = "select * from xcore_mailinglist_subscriber where deleted = 'no' and id=:sid and email = :email and xcore_mailinglist_id = :mlid limit 1";
			$found = DB()->query($sql, [':sid' => $sid, ':email' => $email, ':mlid' => $mlid])->fetch();
			if($found)
			{
				// remove from current mailinglist
				DB('xcore_mailinglist_subscriber')->update(['unsubscribe_newsletter_id' => $nid, 'unsubscribe_date' => now()], $found['id']);
				DB('xcore_mailinglist_subscriber')->delete($found['id']);

				// log
				$f = [];
				$f['xcore_newsletter_id'] = $nid;
				$f['xcore_mailinglist_id'] = $mlid;
				$f['xcore_mailinglist_subscriber_id'] = $sid;
				$f['date'] = now();
				$f['email'] = $email;
				$f['action'] = 'unsubscribe';
				DB('xcore_newsletter_data')->insert($f);


				// remove other mailing list from sending
				$mailinglist_ids = explode(',', $mlids);
				foreach($mailinglist_ids as $mailinglist_id)
				{
					$mailinglist_id = (int)$mailinglist_id;
					if(!$mailinglist_id || $mailinglist_id == $mlid)
						continue;

					$sql = "select * from xcore_mailinglist_subscriber where deleted = 'no' and email = :email and xcore_mailinglist_id = :mlid limit 1";
					$found = DB()->query($sql, [':email' => $email, ':mlid' => $mailinglist_id])->fetch();
					if($found)
					{
						// remove from current mailinglist
						DB('xcore_mailinglist_subscriber')->update(['unsubscribe_newsletter_id' => $nid, 'unsubscribe_date' => now()], $found['id']);
						DB('xcore_mailinglist_subscriber')->delete($found['id']);
					}
				}
			}
		}


		return View('unsubscribe', ['lang' => $lang]);
	}






}