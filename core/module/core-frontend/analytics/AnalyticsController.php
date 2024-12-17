<?php

namespace Plugin\Core_Frontend;

class AnalyticsController extends \Core\Controller {

	public string $table = 'xcore_page_visitor';

	/**
	 * @route /@backend/@module/ {method:"GET|POST"}
	 */
	public function exec()
	{
		$date_yesterday = date('Y-m-d', strtotime('-1 day'));
		$date_start = (get('date_start') && !empty(get('date_start')) ? get('date_start') : date('Y-m-d', strtotime('-30 days')));
		$date_end = (get('date_end') && !empty(get('date_end')) ? get('date_end') : date('Y-m-d', strtotime('-1 day')));

		$data = [];
		$data['websites'] = \Core\Config::get('frontend/analytics/domains_allowed_collect');
		$data['date_yesterday'] = $date_yesterday;
		$data['date_start'] = $date_start;
		$data['date_end'] = $date_end;

		$data['date_7'] = date('Y-m-d', strtotime('-7 days'));
		$data['date_30'] = date('Y-m-d', strtotime('-30 days'));
		$data['date_90'] = date('Y-m-d', strtotime('-90 days'));
		$data['year'] = date('Y');
		$data['year_last'] = date('Y')-1;
		$data['date_12_month'] = date('Y-m-d', strtotime('-12 month'));


		$website = get('website', '');



		// détermine le groupement (par jour ou par mois)
		$date_diff = (new \DateTime($date_end))->diff(new \DateTime($date_start))->days;
		$date_alias = $date_diff > 31 ? "DATE_FORMAT(`date`, '%b %y')" : "DATE_FORMAT(`date`, '%d %b')";
		$group_by = $date_diff > 31 ? "DATE_FORMAT(`date`, '%Y-%m')" : "DATE_FORMAT(`date`, '%Y-%m-%d')";



		// get visitor
		$binds = [':date_start' => "{$date_start} 00:00:00", ':date_end' => "{$date_end} 23:59:59"];
		$sql_added = "`date` >= :date_start and `date` <= :date_end";
		if($website)
		{
			$sql_added .= " and url_domain = :website ";
			$binds[':website'] = $website;
		}

		$sql = "SELECT 
					    {$date_alias} as 'Date',
					    COUNT(DISTINCT `user_ip`) as 'Visitors',
					    COUNT(*) as 'Visits'					    
				FROM 
				        xcore_page_visitor
				WHERE 
					    `deleted` = 'no' and					    
					    {$sql_added}
				GROUP BY 
				        {$group_by}";


		$totals = Db()->query($sql, $binds)->fetchAll();

		$data_visitors = [];
		$data_visitors_total_visitor = 0;
		$data_visitors_total_visits = 0;
		// visitors
		foreach($totals as $t)
		{
			$data_visitors[] = ["{$t['Date']}", $t['Visitors'], $t['Visits']];
			$data_visitors_total_visitor += $t['Visitors'];
			$data_visitors_total_visits += $t['Visits'];
		}

		/*
		for($i=0; $i < count($data_visitors); $i++)
		{
			$data_visitors[$i][3] = round(($data_visitors[$i][1] / $data_visitors_total_visitor), 0);
		}*/



		$data['data_visitors'] = json_encode($data_visitors, true);
		$data['data_visitors_total_visitor'] = $data_visitors_total_visitor;
		$data['data_visitors_total_visits'] = $data_visitors_total_visits;

		// websites
		$sql = "SELECT 
					 	url_domain as 'Website',	
						COUNT(DISTINCT `user_ip`) as 'Visitors'
				FROM
						xcore_page_visitor
				WHERE 
					    `deleted` = 'no' and					    
					    {$sql_added}
				GROUP BY 
				        url_domain";
		$totals = DB()->query($sql, $binds)->fetchAll();

		$data_websites = [];
		foreach($totals as $t)
		{
			$data_websites[] = [$t['Website'], $t['Visitors']];
		}
		$data['data_websites'] = json_encode($data_websites, true);

		// devices
		$sql = "SELECT 
					 	user_device as 'Device',	
						COUNT(DISTINCT `user_ip`) as 'Total'
				FROM
						xcore_page_visitor
				WHERE 
					    `deleted` = 'no' and					    
					    {$sql_added}
				GROUP BY 
				        user_device";
		$totals = DB()->query($sql, $binds)->fetchAll();

		$data_devices = [];
		foreach($totals as $t)
		{
			$data_devices[] = [ucfirst($t['Device']), $t['Total']];
		}
		$data['data_devices'] = json_encode($data_devices, true);

		// top pages
		$sql = "SELECT 
					 	url as 'Url',	
						COUNT(*) as 'Total'
				FROM
						xcore_page_visitor
				WHERE 
					    `deleted` = 'no' and					    
					    {$sql_added}
				GROUP BY 
				        url
				ORDER BY
						`Total` DESC         
				LIMIT
						15";
		$totals = DB()->query($sql, $binds)->fetchAll();
		$data_top_pages = [];
		foreach($totals as $t)
		{
			$data_top_pages[] = [$t['Url'], $t['Total']];
		}
		$data['data_top_pages'] = json_encode($data_top_pages, true);

		// top referer
		$sql = "SELECT 
						REPLACE(REPLACE(referer, 'https://', ''), 'www.', '') AS Referer,					 		
						COUNT(*) as 'Total'
				FROM
						xcore_page_visitor
				WHERE 
					    `deleted` = 'no' and
					    referer != '' and					    
					    {$sql_added}
				GROUP BY 
				        Referer
				ORDER BY
						`Total` DESC         
				LIMIT
						15";
		$totals = DB()->query($sql, $binds)->fetchAll();
		$data_top_referers = [];
		foreach($totals as $t)
		{
			$data_top_referers[] = [rtrim($t['Referer'], '/'), $t['Total']];
		}
		$data['data_top_referers'] = json_encode($data_top_referers, true);


		// user browser




		return View('exec', $data);
	}


	/**
	 * @route /@backend/analytics/widget/
	 */
	public static function widgetRender(): string
	{
		$data = [];

		$data['last_visitors'] = (int)DB()->query("select count(DISTINCT user_ip) from xcore_page_visitor where deleted = 'no' and date >= date_sub(NOW(), interval 5 MINUTE)")->fetchOne();


		$date_start = date('Y-m-d 00:00:00', strtotime('-7 day'));
		$date_end = date('Y-m-d 23:59:00', strtotime('-1 day'));

		$sql = "SELECT
						DATE_FORMAT(date, '%d %b') as Date,
						COUNT(DISTINCT user_ip) as Total
				FROM
						xcore_page_visitor
				WHERE
						deleted = 'no' and
						date BETWEEN '{$date_start}' AND '{$date_end}'
				GROUP BY
						DATE(date)
				ORDER BY
						Date";
		$totals = DB()->query($sql)->fetchAll();

		$data_last_days = [];
		foreach($totals as $t)
		{
			$data_last_days[] = ["{$t['Date']}", $t['Total']];
		}


		$data['data_json'] = json_encode($data_last_days);


		$content = View('widget', $data, false);
		return $content;
	}


}
