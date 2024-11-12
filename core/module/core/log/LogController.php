<?php

namespace Plugin\Core;

use Symfony\Component\HttpFoundation\JsonResponse;

class LogController extends \Core\Controller {
	
	public string $table = 'xcore_log';

	/**
	 * @route /@backend/@module/
	 */
	public function list() {

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100, '', ' table-striped');
		
		if(\Model\User::hasRight('purge'))
			$datagrid->navAddActionButton('purge', "Purge all logs", "javascript:logPurge()", "", "btn-light", ['data-message' => "<i18n>Would you like to purge all logs ?</i18n>"]);
		
		
		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('author');
		$datagrid->searchAddDatetime('date');
		$datagrid->searchAddSelectSql('level');
		$datagrid->searchAddSelectSql('application');
		$datagrid->searchAddSelectSql('plugin');
		$datagrid->searchAddSelectSql('action');
		$datagrid->searchAddText('message');
		$datagrid->searchAddNumber('record_id');
		
		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnDatetime('date', '', 'Y-m-d H:i:s');
		$datagrid->addColumnHtml('level', '', false, 'min');
		$datagrid->addColumn('application', '', false, 'min');
		$datagrid->addColumn('plugin', '', false, 'min');
		$datagrid->addColumn('action', '', false, 'min');
		$datagrid->addColumn('author', '', false, 'min');
		$datagrid->addColumn('record_id', 'record id', false, 'min center');
		$datagrid->addColumnHtml('message', '', false, 'text-wrap');
		$datagrid->addColumnNote('values', '', false);
		
		
		// hookData
		$datagrid->hookData(function($row){
			
			if($row['values'] == '[]')
			{
				$row['values'] = '';
			}
			else
			{
				$row['values'] = str_replace("\/", "\\", $row['values']);
				$row['values'] = "<pre>\n{$row['values']}</pre>";
			}
			
			if(!$row['record_id'])$row['record_id'] = '';
			
			$row['level'] = \Core\Html::Badge($row['level']);

			$row['message'] = strip_tags($row['message']);
			$row['message'] = "<small>{$row['message']}</small>";

			return $row;
		});
		
		
		$data = [];
		$data['content'] = $datagrid->render();
		return View('@plugin-content', $data);
	}

	/**
	 * @route /@backend/@module/purge/  {method:"DELETE"}
	 */
	public function purge() {
		
		DB($this->table)->query("TRUNCATE TABLE {$this->table}");
		\Core\Log::write('purge');
		
		return new JsonResponse($this->validator->result());
		
	}
}