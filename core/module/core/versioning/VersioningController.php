<?php

namespace Plugin\Core;


use Elastic\Apm\ExecutionSegmentContextInterface;

class VersioningController extends \Core\Controller {

	public string $table = 'xcore_versioning';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list()
	{
		$this->loadAssetsJs(['list.js']);

		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->qSelectEmbed('xcore_user','login', 'login');
		$datagrid->qOrderBy('id');

		// search
		$datagrid->searchAddSelectSql('application');
		$datagrid->searchAddNumber('record_id', 'record id');

		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnDatetime('date');
		$datagrid->addColumn('application', '', false, '');
		$datagrid->addColumn('record_id', 'record id', false, 'min center');
		$datagrid->addColumn('login', '', false, 'min');

		$datagrid->addColumnButton('view', ' ', 'view/[ID]/', false, 'bi bi-eye', 'btn-info', ['target' => '_popup']);
		$datagrid->addColumnButton('replace', ' ', 'replace/[ID]/', false, 'bi bi-arrow-repeat', 'btn-danger btn-versioning-replace');

		// hookData
		$datagrid->hookData(function($row){

			$row['view'] = 'visualize';
			$row['replace'] = 'replace';
			return $row;
		});

		$data = [];
		$data['content'] = $datagrid->render();
		return View('@plugin-content', $data);
	}

	/**
	 * @route /@backend/@module/view/{id}/
	 */
	public function view($id)
	{
		$data = [];

		$rec = \Model\Versioning::findById($id, 'data_json');

		if(!$rec)
		{
			$rec = [];
			$rec['data_json'] = "{}";
		}

		$data['rows'] = json_decode($rec['data_json'], true);
		@ksort($data['rows']);

		return View('view', $data);
	}

	/**
	 * @route /@backend/@module/replace/{id}/
	 */
	public function replace($id): \Core\JsonResponse
	{
		$rec = \Model\Versioning::findById($id);

		$resp = ['error' => false, 'error_message' => ""];

		if(!$rec)
		{
			$resp = ['error' => true, 'error_message' => "Record #{$id} not found"];
			return new \Core\JsonResponse($resp);
		}

		// get table info
		$table_fields_info = DB()->query("show fields from {$rec['table']}")->fetchAll();

		$table_fields = [];
		foreach($table_fields_info as $field)
		{
			if(!in_array($field['Field'], ['id', 'deleted', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))
				$table_fields[] = $field['Field'];
		}

		$rec_updatable = json_decode($rec['data_json'], true);
		if(!is_array($rec_updatable))
		{
			$resp = ['error' => true, 'error_message' => "Record #{$id} json is corrupted"];
			return new \Core\JsonResponse($resp);
		}

		$all_values = [];
		$row = [];
		foreach($rec_updatable as $key => $val)
		{
			if(!is_array($val) && in_array($key, $table_fields))
			{
				$row[$key] = $val;
			}

			$all_values[$key] = $val;
		}

		if(!count($all_values))
			return new \Core\JsonResponse($resp);

		// update
		DB($rec['table'])->update($row, $rec['record_id']);

		// update links
		if(isset($rec_updatable['_links']))
		{
			foreach($rec_updatable['_links'] as $l_table => $l_row)
			{
				// delete where before
				if(isset($l_row['_delete-where-before']) && !empty($l_row['_delete-where-before']))
					DB($l_table)->delete($l_row['_delete-where-before']);

				// _data
				foreach($l_row['_data'] as $l_r)
				{
					if($l_row['_mode'] == 'insert')
						DB($l_table)->insert($l_r);
				}

			}
		}


		// versioning
		DB()->query("delete from xcore_versioning where id = :id", [':id' => $id]);
		\Model\Versioning::add($rec['application'], $rec['table'], $rec['record_id'], $all_values, \Model\User::getUID());



		return new \Core\JsonResponse($resp);
	}



}