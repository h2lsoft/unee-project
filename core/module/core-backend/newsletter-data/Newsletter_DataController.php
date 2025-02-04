<?php

namespace Plugin\Core_Backend;

use Core\Html;
use Core\JsonResponse;
use Core\Response;
use h2lsoft\Data\Validator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Newsletter_DataController extends \Core\Controller {

	public string $table = 'xcore_newsletter_data';

	/**
	 * @route /@backend/@module/
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 1000);
		
		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddDatetime('date');
		$datagrid->searchAddNumber('xcore_newsletter_id', 'newsletter id');
		$datagrid->searchAddNumber('xcore_mailinglist_subscriber_id', 'subscriber id');
		$datagrid->searchAddText('email');
		$datagrid->searchAddSelectSql('action');

		// columns
		$datagrid->addColumn('id', '',  true, 'min');
		$datagrid->addColumnDatetime('date', '', '', true, 'min');
		$datagrid->addColumn('xcore_newsletter_id', 'nid', true, 'min center');
		$datagrid->addColumn('xcore_mailinglist_subscriber_id', 'sid', true, 'min center');
		$datagrid->addColumn('email', '', false, 'min');
		$datagrid->addColumn('action', '', false, 'min center');
		$datagrid->addColumnHtml('url', '', false, 'min center');
		$datagrid->addColumnHtml('ip', 'ip', false, 'wrap');
		// $datagrid->addColumnHtml('user_agent', 'ua', false, 'wrap');
		$datagrid->addColumnHtml('error_message', 'error', false, '');


		// hookData
		$datagrid->hookData(function($row){


			if(!empty($row['url']))
				$row['url'] = "<a href='{$row['url']}' target='_blank'><i class='bi bi-globe'></i></a>";


			$row['user_agent'] = "<small class='text-wrap'>{$row['user_agent']}</small>";
			$row['error_message'] = "<small class='text-wrap'>{$row['error_message']}</small>";


			return $row;
		});


		$data = [];
		$data['content'] = $datagrid->render();
		return View('@plugin-content', $data);
	}


}