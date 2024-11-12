<?php

namespace Plugin\Core_Backend;

use Core\Html;

class PluginController extends \Core\Controller {
	
	public string $table = 'xcore_plugin';
	public string $object_label = 'plugin';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {
		
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 0);
		$datagrid->qSelectEmbed('xcore_menu', 'name', 'menu');
		$datagrid->qWhere("type != 'core'");
		$datagrid->qOrderBy('menu, position asc');
		
		// search
		$datagrid->searchAddSelectSql('xcore_menu_id', 'menu');
		
		
		// columns
		if(!$datagrid->userIsSearching('xcore_menu_id'))
			$datagrid->addHeaderMessage("Please, search by menu to reorder plugins");
		else
			$datagrid->addColumnPosition('xcore_menu_id');
		
		$datagrid->addColumn('id');
		$datagrid->addColumn('menu', '', false, 'min');
		$datagrid->addColumnHtml('name', '', false, '');
		$datagrid->addColumnHtml('url', '', false, 'min');
		$datagrid->addColumnBoolean('visible');
		$datagrid->addColumnBoolean('active');
		
		
		// hookData
		$datagrid->hookData(function($row){
			
			$row['name'] = "<i class=\"{$row['icon']}\"></i> <i18n>{$row['name']}</i18n>";
			
			$url = "/".\Core\Config::get('backend/dirname')."/".$row['route_prefix_name']."/";
			if($row['type'] == 'url')
				$url = $row['url'];
			
			$row['url'] = Html::A(Html::Icon("bi bi-globe"), $url, ['target' => '_blank']);
			
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
		
		
		$form->addSelectSql('xcore_menu_id', 'menu', true);
		$form->addText('name', '', true, ['class' => 'ucfirst']);
		$form->addText('icon', '', true);
		
		$options = [];
		$options[] = ['label' => 'normal', 'value' => 'normal'];
		$options[] = ['label' => 'url', 'value' => 'url'];
		$form->addSelect('type', '', true, $options, "", ["data-parent-root" => true]);
		
		$form->addText('route_prefix_name', 'Route prefix', false, ['data-parent' => 'type', 'data-parent-value' => 'normal', 'data-parent-wrapper' => '.row-route_prefix_name'], "name of your plugin");
		$form->addTextarea('actions', '', false, ['data-parent' => 'type', 'data-parent-value' => 'normal', 'data-parent-wrapper' => '.row-actions'], "one action by line")->setValue("list\nadd\nedit\ndelete");
		$form->addUrl('url', '', false, ['data-parent' => 'type', 'data-parent-value' => 'url', 'data-parent-wrapper' => '.row-url']);
		
		$form->addSwitch('versioning');
		$form->addSwitch('visible')->setValue('yes');
		$form->addSwitch('active')->setValue('yes');
		
		if($form->is_editing)
			$form->addNumber('position', '', true, ['class' => 'text-center'])->setInputSize(1);
		
		
		// validation
		if($form->isSubmitted())
		{
			// toggle required
			$form->validator->input('actions')->requiredIf('type', 'normal');
			$form->validator->input('route_prefix_name')->requiredIf('type', 'normal');
			$form->validator->input('url')->url()->requiredIf('type', 'url');
			
			
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