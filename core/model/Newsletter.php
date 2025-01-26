<?php

namespace Model;

class Newsletter extends \Core\Entity
{
	public static string $table = 'xcore_newsletter';


	public static function send(string|array $from, string $to, string $subject, string $body, array $data=[], array $options=[], array $headers=[], array $json_added=[]):bool
	{
		// parse links
		$pattern = '/<a\s+[^>]*href="([^"]+)"[^>]*>/i';
		if(preg_match_all($pattern, $body, $matches))
		{
			$links = $matches[1];
			foreach($links as $link)
			{
				if(empty($link) || $link[0] == '[') continue;

				$json = $json_added;
				$json['url'] = $link;
				$link2 = \Core\Config::get('url')."/service/newsletter/link/?uri=".urlencode(base64_encode(json_encode($json)));
				$body = str_replace(' href="'.$link.'"', ' href="'.$link2.'"', $body);

			}
		}

		return \Core\Mailer::send($from, $to, $subject, $body, [], $options, $data, $headers, false);
	}


}