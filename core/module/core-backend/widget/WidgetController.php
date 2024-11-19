<?php

namespace Plugin\Core_Backend;


use Core\Html;

class WidgetController extends \Core\Controller {
	
	public string $table = 'xcore_widget';
	public string $object_label = 'widget';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {
		
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 0);
		$datagrid->qSelectEmbed('xcore_plugin', 'name', 'parent_plugin');
		$datagrid->qOrderBy('position asc');
		
		// columns
		$datagrid->addColumnPosition();
		$datagrid->addColumn('id');
		$datagrid->addColumn('parent_plugin', 'parent plugin', false, 'min');
		$datagrid->addColumn('name', '', false, '');
		$datagrid->addColumn('refresh', '', false, 'min center');

		// $datagrid->addColumnHtml('url', '', false, 'min');
		$datagrid->addColumnBoolean('active');
		
		// hookData
		$datagrid->hookData(function($row){
			
			// $url = str_replace('[BACKEND]', \Core\Config::get('backend/dirname'), $row['url']);
			// $row['url'] = Html::A(Html::Icon("bi bi-globe"), $url, ['target' => '_blank']);

			$row['refresh'] = $row['autorefresh_seconds'];
			$row['refresh'] = (!$row['refresh']) ? '-' : "{$row['refresh']} s";

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
		
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		// $form->addText('title', '', true);
		$form->addSelectSql('xcore_plugin_id', 'Parent plugin', true, "", [], "id", "CONCAT((SELECT name FROM xcore_menu WHERE id = xcore_menu_id), '> ', name)");
		// $form->addUrl('url', '', true)->setHelp("Word '[BACKEND]' will be replaced by backend dirname");
		
		$form->addText('method', '', true)->setHelp("Method to render widget, ex: \\Core_Backend\\DashboardController::widgetRender()");
		
		
		$form->addNumber('autorefresh_seconds', 'autorefresh seconds', true, ['class' => 'text-center'])
			 ->setHelp("0 for no refresh")
			 ->setInputSize(1)
			 ->setValue(30);
		
		$form->addSwitch('active');
		
		// validation
		if($form->isSubmitted())
		{
			// valid
			if($form->isValid())
			{
				$added = [];
				if($form->is_adding)
				{
					$position = $form->getMaxPosition();
					$added['position'] = $position;
				}
				
				$form->save([], $added);
			}
			
			return $form->json();
		}
		
		
		return $form->render();
		
	}


}
