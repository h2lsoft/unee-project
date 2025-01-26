<?php

namespace Core;

use Core\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class Mailer
{
	public static string $last_error = "";

	public static function getLastError():string {return self::$last_error;}


	/**
	 * Send email by module email template
	 *
	 * @param string|int $template_name
	 * @param string     $to
	 * @param array      $vars
	 * @param string     $locale
	 * @param array      $attachments
	 * @param array      $options
	 *
	 * @return bool
	 */
	public static function sendTemplate(string|int $template_name, string $to, array $vars=[], string $locale='', array $attachments=[], array $options=[]):bool
	{
		if(empty($locale))$locale = App()->locale;
		$locale = strtolower($locale);

		$column = (is_int($template_name)) ? 'id' : 'name';
		$template = \Model\Mail_Template::findOne("{$column} = :value", [':value' => $template_name], '*');

		$from = $template["sender_{$locale}"];

		$subject = $template["subject_{$locale}"];
		$body = $template["body_{$locale}"];
		$body = str_replace(['[[ ', ' ]]'], ['[[', ']]'], $body);

		foreach($vars as $var => $val)
			$body = str_replace("[[{$var}]]", $val, $body);


		return \Core\Mailer::send($from, $to, $subject, $body, $attachments, $options);

	}



	/**
	 * @param string|array $from array(author|email) or string with | as separator
	 * @param string|array $to  if string separated by ;
	 * @param string       $subject
	 * @param string       $body
	 * @param array        $attachments array name and path
	 * @param array        $options (like charset : utf-8 by default or html : true by default or reply-to, bcc, cc)
	 * @param array        $data append value for data in body
	 *
	 * @return bool
	 */
	public static function send(string|array $from, string $to, string $subject, string $body, array $attachments=[], array $options=[], array $data=[], array $headers=[], $wrap_email=true):bool
	{
		global $request;

		$mail = new PHPMailer(true);

		$cfg = Config::get('mailer/package/'.APP_MAIL_PACKAGE.'/');
		if($cfg['smtp'])
		{
			$mail->isSMTP();
			$mail->SMTPSecure = $cfg['security'];
		}

		$mail->Host = $cfg['host'];
		$mail->Port = $cfg['port'];
		$mail->SMTPAuth = $cfg['auth'];
		$mail->Username = $cfg['auth.username'];
		$mail->Password = $cfg['auth.password'];

		if(!isset($options['charset']))$options['charset'] = 'utf-8';
		$mail->CharSet = $options['charset'];

		if(!isset($options['X-Mailer']) && !isset($options['XMailer']) && !isset($options['x-mailer']))
			$mail->XMailer = null;

		// add headers
		foreach($headers as $header_name => $header_value)
		{
			$mail->addCustomHeader($header_name, $header_value);
		}

		// html
		$html_mode = (isset($options['html'])) ? $options['html'] : true;
		$mail->isHTML($html_mode);

		// from
		if(empty($from))$from = \Core\Config::get('noreply_email');
		$from_email = $from;
		$from_author = '';
		if(is_array($from))
		{
			$from_author = $from[0];
			$from_email = $from[1];
		}
		elseif(count(explode('|', $from)) == 2)
		{
			list($from_author, $from_email) = explode('|', $from);
		}

		$mail->setFrom(trim($from_email), trim($from_author), true);

		// to
		if(!is_array($to))
		{
			$to = str_erase(' ', str_replace(',', ';', trim($to)));
			$to = explode(';', $to);
			$to = array_map('trim', $to);
		}

		foreach($to as $email)
		{
			$mail->addAddress($email);
		}

		// attachments
		foreach($attachments as $file)
		{
			if(empty($file['name']))$file['name'] = basename($file['path']);
			$mail->addAttachment($file['path'], $file['name']);
		}

		// reply-to
		if(isset($options['reply-to']) && !empty($options['reply-to']))
			$mail->addReplyTo($options['reply-to']);

		// cc
		if(isset($options['cc']))
		{
			$to = $options['cc'];
			if(!is_array($to))
			{
				$to = str_erase(' ', str_replace(',', ';', trim($options['cc'])));
				$to = explode(';', $to);
				$to = array_map('trim', $to);
			}

			foreach($to as $email)
				$mail->addCC($email);
		}

		// bcc
		if(isset($options['bcc']))
		{
			$to = $options['bcc'];
			if(!is_array($to))
			{
				$to = str_erase(' ', str_replace(',', ';', trim($options['cc'])));
				$to = explode(';', $to);
				$to = array_map('trim', $to);
			}

			foreach($to as $email)
				$mail->addBCC($email);
		}

		// parsing subject, body
		foreach($data as $key => $val)
		{
			$subject = str_replace("[[{$key}]]", $val, $subject);
			$body = str_replace("[[{$key}]]", $val, $body);
		}

		$mail->Subject = $subject;

		// add envelop
		$data['IP'] = $request->getClientIp();
		$data['contents'] = $body;
		$data['vars'] = $cfg['template']['vars'];

		if($wrap_email)
		{
			$body = View($cfg['template']['path'], $data, false);
		}

		$uri = \Core\Config::get('url');
		$body = str_replace(' src="/',  " src=\"{$uri}/", $body);
		$body = str_replace(' href="/',  " href=\"{$uri}/", $body);

		$mail->Body = $body;

		try {
			$mail->send();
			return true;
		} catch (Exception $e) {
			self::$last_error = "Mailer Error: {$mail->ErrorInfo}";
			return false;
		}

	}
}