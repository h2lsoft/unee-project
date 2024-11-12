<?php

namespace Plugin\Core_Frontend;

class SitemapController extends \Core\Controller {

	/**
	 * @route /service/sitemap/generate/
	 * @route /sitemap.xml
	 */
	public function generate():\Core\Response
	{


		$languages = \Core\Config::get("frontend/langs");

		$xml = '<?xml version="1.0" encoding="UTF-8"?>'.CR;
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.CR;

		// get all website linked in zone
		$zones = \Model\Zone::all("", [], "", "", 'position');

		$websites = [];
		foreach($zones as $zone)
		{
			if(empty($zone['website']))
				$zone['website'] = \Core\Config::get('url');

			if(!in_array($zone['website'], array_keys($websites)))
				$websites[$zone['website']][] = $zone['id'];
		}


		foreach($websites as $website => $zones)
		{

			foreach($zones as $zone_id)
			{
				foreach($languages as $lang)
				{
					$fields = "id, name, url, sitemap_priority, sitemap_change_freq, sitemap_pagination_pattern, sitemap_follow_url_pattern, sitemap_follow_url_priority";

					$where = "language = :lang and xcore_page_zone_id = :zone_id and status = 'published' and sitemap_priority not in('', 0)";
					$params = [':lang' => $lang[0], ':zone_id' => $zone_id];
					$pages = \Model\Page::all($where, $params, $fields, "", "id");

					foreach($pages as $p)
					{
						$website_host = explode('/', $website);
						$website_host = "{$website_host[0]}//{$website_host[2]}";

						$p['url'] = \Model\Page::getAbsoluteUrl($p['id'], $p['url'], $website_host);

						$xml .= "  <url>\n";
						$xml .= "    <loc>{$p['url']}</loc>\n";
						$xml .= "    <priority>{$p['sitemap_priority']}</priority>\n";
						if(!empty($p['sitemap_change_freq']))
							$xml .= "    <changefreq>{$p['sitemap_change_freq']}</changefreq>\n";
						$xml .= "  </url>\n";

						// pagination detected
						if(!empty($p['sitemap_pagination_pattern']))
						{


							// get all pagination
							$max_page = 1;
							if($contents = file_get_contents($p['url']))
							{
								$pattern = $p['sitemap_pagination_pattern'];
								$pattern = str_replace('/', '\/', $pattern);
								$pattern = str_replace('[:page]', '(\d+)', $pattern);
								preg_match_all("#{$pattern}#", $contents, $matches);

								if(isset($matches[1]))
								{
									foreach($matches[1] as $page_number)
									{
										if($page_number > $max_page)
											$max_page = $page_number;
									}
								}
							}

							// page 1 always be there
							for($i=1; $i <= $max_page; $i++)
							{
								$page_priority = ($i == 1) ? '0.6' : '0.5';
								$url = str_replace('[:page]', $i, $p['sitemap_pagination_pattern']);
								$url = rtrim($website_host, '/').'/'.ltrim($url, '/');

								// add url set
								$xml .= "  <url>\n";
								$xml .= "    <loc>{$url}</loc>\n";
								$xml .= "    <priority>{$page_priority}</priority>\n";
								$xml .= "  </url>\n";

								if($i > 1)
								{
									$contents = file_get_contents($url);
								}

								// capture all article
								$capture_priority = $p['sitemap_follow_url_priority'];

								if(!empty($p['sitemap_follow_url_pattern']))
								{
									$pattern = $p['sitemap_follow_url_pattern'];
									$pattern = str_replace('/', '\/', $pattern);
									$pattern = str_replace('.', '\.', $pattern);
									$pattern = str_replace('_', '\_', $pattern);
									$pattern = str_replace('[:slug]', '([a-zA-Z0-9\-_]+)', $pattern);

									preg_match_all("#{$pattern}#", $contents, $matches);
									if(isset($matches[0]))
									{
										$matches[0] = array_unique($matches[0]);
										foreach($matches[0] as $follow_url)
										{
											$follow_url = rtrim($website_host, '/').'/'.ltrim($follow_url, '/');

											$xml .= "  <url>\n";
											$xml .= "    <loc>{$follow_url}</loc>\n";
											$xml .= "    <priority>{$capture_priority}</priority>\n";
											$xml .= "  </url>\n";
										}
									}
								}
							}
						}
					}
				}
			}
		}


		$xml .= '</urlset>'.CR.CR;

		return new \Core\Response($xml, 200, ['Content-Type' => 'application/xml']);
	}


}