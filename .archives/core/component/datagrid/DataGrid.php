<?php

namespace Component;

use Symfony\Component\HttpFoundation\JsonResponse;

class DataGrid {
	
	private string $table;
	private string $object_label = 'record';
	private string $no_record_message = "No record found";
	private string $delete_message = "Would you like to delete record #[ID] ?";
	private string $delete_message_warning = "";
	private string $css_id;
	private string $sql;
	private string $classes = '';
	private array $attributes = [];
	
	private int $record_per_page;
	private array $per_page_options = [25, 50, 100, 500, 1000];
	private bool $pagination = true;
	
	private bool $batch_mode = false;
	private array $batch_actions = [];
	
	private array $columns = [];
	private array $order_by_columns = [];
	
	private array $row_btn_actions = [];
	private array $nav_btn_actions = [];
	
	private array $search_columns = [];
	public mixed $hookData;
	
	private array $query = [];
	private array $query_params = [];
	
	private string $order_by_init = '';
	private string $order_by_sort_init = 'desc';
	
	public bool $user_is_searching = false;
	private bool $col_position = false;
	private string $col_position_parent = '';


	
	public function __construct(string $object_label='record', string $table='', int $record_per_page=25, string $css_id='datagrid', string $classes='table-sm table-hover table-border caption-top', $attributes=[])
	{
		$this->object_label = $object_label;
		$this->table = $table;
		$this->css_id = $css_id;
		
		if(!empty($classes) && $classes[0] == ' ')
			$classes .= " table-sm table-hover table-border caption-top";
		
		$this->classes = $classes;
		$this->attributes = $attributes;
		$this->record_per_page = $record_per_page;
		
		if(!$this->record_per_page)$this->setPagination(false);
		
		$plugin_route = App()->plugin['route_prefix_name'];
		
		if(App()->is_backend)
		{
			if(\Model\User::hasRight('add'))
			{

				$this->navAddActionButton('add', "<i18n>New</i18n> <i18n>{$this->object_label}</i18n>", "/@backend/{$plugin_route}/add/", '', 'btn-primary', ['accesskey' => 'c']);
			}
			
			if(\Model\User::hasRight('edit'))
				$this->rowAddActionButton('edit', 'Edit', 'bi bi-pencil-fill text-info', "/@backend/{$plugin_route}/edit/[ID]/");
			
			// if(\Model\User::hasRight('add') && \Model\User::hasRight('edit'))
				// $this->rowAddActionButton('duplicate', 'Duplicate', 'bi bi-files', "/@backend/{$plugin_route}/duplicate/[ID]/");
				
			if(\Model\User::hasRight('delete'))
				$this->rowAddActionButton('delete', 'Delete', 'bi bi-trash-fill text-danger', "/@backend/{$plugin_route}/delete/[ID]/");
		}
	}
	
	public function setRecordByPage(int $record):void
	{
		$this->record_per_page = 0;
		$this->setPagination(false);
	}
	
	public function rowRemoveActionButton($name)
	{
		unset($this->row_btn_actions[$name]);
	}
	
	public function navAddActionButton($name, $label, $action, $icon='', $type='btn-light', $attributes=[]):void
	{
		$tmp = [];
		$tmp['name'] = $name;
		$tmp['type'] = $type;
		$tmp['action'] = $action;
		$tmp['label'] = (strpos($label, '<i18n>') === false) ? "<i18n>{$label}</i18n>" : $label;
		$tmp['icon'] = $icon;
		$tmp['attributes'] = $attributes;
		
		$this->nav_btn_actions[$name] = $tmp;
	}

	public function navActionButtonAddUrl(string $name, string $url):void
	{
		$this->nav_btn_actions[$name]['action'] .= $url;
	}

	public function rowAddActionButton($name, $tooltip, $icon, $action, $attributes=[]):void
	{
		$tmp = [];
		$tmp['name'] = $name;
		$tmp['tooltip'] = $tooltip;
		$tmp['icon'] = $icon;
		$tmp['action'] = $action;
		$tmp['attributes'] = $attributes;
		
		$this->row_btn_actions[$name] = $tmp;
	}
	
	public function setPagination(bool $pagination):void {$this->pagination = $pagination;}
	
	public function setObjectLabel(string $object_label):void {$this->object_label = $object_label;}
	public function setClass(string $class):void {$this->classes = $class;}
	public function setAttributes(array $attributes):void {$this->attributes = $attributes;}
	
	public function setDeleteMessage(string $message):void {$this->delete_message = $message;}
	public function setDeleteMessageWarning(string $message):void {$this->delete_message_warning = $message;}
	
	
	public function setNoRecordMessage(string $no_record_message):void {$this->no_record_message = $no_record_message;}
	public function setBatchMode(bool $batch_mode):void{$this->batch_mode = $batch_mode;}
	
	public function addBatchAction(string $label, string $js_func):void{
		
		$this->batch_actions[] = ['label' => $label, 'js_func' => $js_func];
		
	}
	
	
	public function setOrderByInit(string $column, string $sort='desc'):void{
		$this->order_by_init = $column;
		$this->order_by_sort_init = $sort;
	}
	
	public function userIsSearching(string $column):bool {
		
		if(isset($_GET['search']) && is_array($_GET['search']))
		{
			$searches = $_GET['search'];
			foreach($searches as $search)
			{
				$v = explode('|', $search);
				if(count($v) >= 2 && $v[0] == $column)
					return true;
			}
		}
		
		return false;
	}

	public function searchSet(string $column, string $value, string $operator="")
	{
		if(!empty($operator))$operator = "{$operator}||";
		$_GET['search'][] = "{$column}||{$operator}{$value}";
	}



	private string $header_message = "";
	private string $header_message_class = "";
	private string $header_message_icon = "";
	public function addHeaderMessage(string $message, string $class='text-center bg-light', string $icon='bi bi-info-circle-fill'):void
	{
		$this->header_message = $message;
		$this->header_message_class = $class;
		$this->header_message_icon = $icon;
	}
	
	public function searchAddText(string $name, string $label='', string $operator_selected='')
	{
		if($name == 'id' && empty($label))$label = 'Id';
		
		$this->search_columns[$name] = [];
		$this->search_columns[$name]['name'] = $name;
		$this->search_columns[$name]['label'] = (empty($label)) ? $name : $label;
		$this->search_columns[$name]['type'] = 'text';
		$this->search_columns[$name]['operator_selected'] = $operator_selected;
		$this->search_columns[$name]['options'] = [];
		
		
		
		return $this;
	}
	
	public function searchForceSql(string $sql, array $params=[], string $name='')
	{
		$cols = array_keys($this->search_columns);
		if(empty($name))$name = end($cols);
		
		$this->search_columns[$name]['sql_raw'] = $sql;
		$this->search_columns[$name]['sql_raw_params'] = $params;
		
		return $this;
	}
	
	
	
	public function searchAddDatetime(string $name, string $label='', string $operator_selected='')
	{
		$this->searchAddText($name, $label, $operator_selected);
		$this->search_columns[$name]['type'] = 'datetime-local';
		return $this;
	}
	
	public function searchAddNumber(string $name, string $label='', string $operator_selected='')
	{
		$this->searchAddText($name, $label, $operator_selected);
		$this->search_columns[$name]['type'] = 'number';
		return $this;
	}
	
	public function searchAddBoolean(string $name, string $label='', string $operator_selected='')
	{
		$this->searchAddText($name, $label, $operator_selected);
		$this->search_columns[$name]['type'] = 'boolean';
		return $this;
	}
	
	
	public function searchAddSelect(string $name, string $label='', array $options=[], string $operator_selected='')
	{
		$this->searchAddText($name, $label, $operator_selected);
		$this->search_columns[$name]['type'] = 'select';
		$this->search_columns[$name]['options'] = $options;
		
		
		$options_values = [];
		foreach($options as $option)
			$options_values[] = $option['value'];
		
		$this->search_columns[$name]['options-values'] = $options_values;
		return $this;
	}
	
	public function searchAddSelectSql(string $name, string $label='', string $table='', string $column='', string $where_added='', string $operator_selected='')
	{
		$distinct_mode = false;
		
		if(empty($table) && empty($column) && !(preg_match('/id$/', $name)))
		{
			$table = $this->table;
			$column = $name;
			$distinct_mode = true;
		}
		
		if(empty($table))
		{
			$table = $this->table;
			if(preg_match('/_id$/', $name))
			{
				$table = str_erase('_id', $name);
				if(empty($column))$column = 'name';
			}
		}
		
		if($distinct_mode)
		{
			$sql = "SELECT DISTINCT {$column} AS label FROM {$table} WHERE deleted = 'no' {$where_added} ORDER BY {$column}";
		}
		else
		{
			$sql = "SELECT id AS value, {$column} AS label FROM {$table} WHERE deleted = 'no' {$where_added} ORDER BY {$column}";
		}
		
		$recs = DB()->query($sql)->fetchAll();
		
		$options = [];
		foreach($recs as $rec)
		{
			if($distinct_mode)
				$options[] = ['label' => $rec['label'], 'value' => $rec['label'], 'selected' => false];
			else
				$options[] = ['label' => $rec['label'], 'value' => $rec['value'], 'selected' => false];
		}
		
		$this->searchAddSelect($name, $label, $options, $operator_selected);
		
	}

	public function searchAddTagManager(string $signature='', string $table='')
	{
		if(empty($signature))$signature = $this->table;
		if(empty($table))$table = $this->table;

		$name = 'xTags';


		$recs = DB()->query("SELECT tag AS label FROM xcore_tag WHERE deleted = 'no' AND signature = :signature GROUP BY tag ORDER BY tag", [':signature' => $signature])->fetchAll();

		$options = [];
		foreach($recs as $rec)
		{
			$options[] = ['label' => $rec['label'], 'value' => $rec['label'], 'selected' => false];
		}

		$this->searchAddSelect('xTags', 'Tag', $options);

		$this->search_columns[$name]['sql_raw'] = "{$table}.id IN(SELECT record_id FROM xcore_tag WHERE deleted = 'no' AND signature = :signature AND tag [@operator_input])";
		$this->search_columns[$name]['sql_raw_params'] = [':signature' => $signature];

		return $this;
	}

	
	private function ajaxerPositionExecute()
	{
		if(get('ajaxer') == 1 && get('ajaxer-action') == 'position')
		{
			$resp = ['error' => false, 'error_message' => ""];
			
			// ids
			$ids = get('ids');
			$ids = explode(';', trim($ids));
			
			$ids2 = [];
			foreach($ids as $id)
			{
				if($id == (int)$id && $id)
					$ids2[] = (int)$id;
			}
			
			if(count($ids2))
			{
				$current = 1;
				foreach($ids2 as $id)
				{
					Db($this->table)->update(['position' => $current], $id);
					$current++;
				}
			}
			
			return die(json_encode($resp));
		}
	}
	
	public function addColumnPosition()
	{
		$this->col_position = true;
		// $this->col_position_parent = $col_position_parent;
		
		$this->ajaxerPositionExecute();
	}
	
	public function addColumn(string $name, string $label='', bool $order_by=false, string $classes=''):void
	{
		if($name == 'id')$classes .= ' min text-end ';
		
		$this->columns[$name] = [];
		$this->columns[$name]['type'] = 'normal';
		$this->columns[$name]['name'] = $name;
		$this->columns[$name]['label'] = (empty($label)) ? $name : $label;
		$this->columns[$name]['classes'] = $classes;
		$this->columns[$name]['order_by'] = $order_by;
		$this->columns[$name]['order_by_sort'] = "";
		
		if($order_by)
			$this->order_by_columns[] = $name;
	}
	public function addColumnHtml(string $name, string $label='', bool $order_by=false, string $classes=''):void
	{
		if($name == 'email')$classes .= ' min center';
		
		$this->addColumn($name, $label, $order_by, $classes);
		$this->columns[$name]['type'] = 'html';
	}
	
	public function addColumnNote(string $name, string $label='', bool $order_by=false, string $icon='bi bi-sticky-fill', string $icon_classes="text-warning"):void
	{
		$classes = ' min center';
		$this->addColumn($name, $label, $order_by, $classes);
		$this->columns[$name]['type'] = 'note';
		$this->columns[$name]['text_icon'] = $icon;
		$this->columns[$name]['text_icon_classes'] = $icon_classes;
	}
	
	
	public function addColumnImage(string $name, string $label='', bool $order_by=false, string $img_classes='', bool $img_zoomable=false):void
	{
		$this->addColumn($name, $label, $order_by);
		$this->columns[$name]['type'] = 'image';
		$this->columns[$name]['classes'] = 'min center';
		$this->columns[$name]['image_classes'] = $img_classes;
		$this->columns[$name]['image_zoomable'] = $img_zoomable;
	}
	
	public function addColumnButton(string $name, string $text, string $url, bool $order_by=false, string $icon='', string $type='btn-light', array $attributes=[]):void
	{
		if(empty($type))$type = 'btn-light';
		
		$this->addColumnHtml($name, $text, $order_by, 'min center');
		
		$this->columns[$name]['type'] = 'button';
		$this->columns[$name]['button_url'] = $url;
		$this->columns[$name]['button_icon'] = $icon;
		$this->columns[$name]['button_type'] = $type;
		$this->columns[$name]['button_attributes'] = $attributes;
	}
	public function addColumnBoolean(string $name, string $label='', bool $order_by=false, string $classes=''):void
	{
		$this->addColumn($name, $label, $order_by, "min center {$classes}");
		$this->columns[$name]['type'] = 'boolean';
	}
	public function addColumnDate(string $name, string $label='', string $format='', bool $order_by=false, string $classes=''):void
	{
		if(empty($format))$format = \Core\Session::get('auth.format_date');
		if(empty($format))$format = \Core\Config::get('format/date');
		
		$classes .= " min";
		$this->addColumn($name, $label, $order_by, $classes);
		$this->columns[$name]['type'] = 'date';
		$this->columns[$name]['format'] = $format;
	}
	
	public function addColumnDatetime(string $name, string $label='', string $format='', bool $order_by=false, string $classes=''):void
	{
		if(empty($format))$format = \Core\Session::get('auth.format_datetime', "");
		if(empty($format))$format = \Core\Config::get('format/datetime', "");
		
		$classes .= " min";
		$this->addColumn($name, $label, $order_by, $classes);
		$this->columns[$name]['type'] = 'datetime';
		$this->columns[$name]['format'] = $format;
	}


	public function addColumnTags(string $signature='', string $table=''):void{
		$this->addColumn('xTags', 'Tags', false, "min");
		$this->columns['xTags']['type'] = 'tags';

		if(empty($signature))$signature = $this->table;
		if(empty($table))$table = $this->table;

		$sql = "(select GROUP_CONCAT(tag SEPARATOR ', ') FROM xcore_tag WHERE deleted = 'no' AND signature = '{$signature}' AND {$table}.id = record_id ORDER BY tag) AS `xTags`";
		$this->qSelect($sql);

	}
	
	public function hookData(callable $callback):void
	{
		$this->hookData = $callback;
	}
	
	public function qSelect(string $raw):void {
		$this->query['SELECT'][] = $raw;
	}
	
	public function qSelectEmbed(string $table, string $column='name', string $alias='', string $where_added=''):void {
		if(empty($alias))$alias = "{$table}__{$column}";
		if(!empty($where_added)) $where_added = " AND {$where_added}";
		$sql = "(select {$column} FROM {$table} WHERE deleted = 'no' AND {$this->table}.{$table}_id = {$table}.id {$where_added}) AS `{$alias}`";
		
		$this->qSelect($sql);
	}
	
	public function qSelectEmbedCount(string $table, string $alias='', string $column='*', string $where_added=''):void {
		if(empty($alias))$alias = "{$table}__{$column}";
		if(!empty($where_added)) $where_added = " AND {$where_added}";
		$sql = "(select COUNT({$column}) FROM {$table} WHERE deleted = 'no' AND {$this->table}_id = {$this->table}.id {$where_added}) AS `{$alias}`";
		$this->qSelect($sql);
	}
	public function qWhere(string $where, array $params=[]):void
	{
		$this->query['WHERE'][] = $where;
		$this->query_params = array_merge($this->query_params, $params);
	}
	public function qOrderBy(string $col):void
	{
		$this->query['ORDER-BY'] = $col;
	}
	
	public function getSQL():string {return $this->sql;}
	
	public function render()
	{

		// position
		$this->ajaxerPositionExecute();

		// $this->controller->loadAssetsJs(['/core/component/datagrid/assets/js/datagrid.js']);
		
		// add search parameters
		if(isset($_GET['search']) && is_array(($_GET['search'])))
		{
			$current = 0;
			foreach($_GET['search'] as $vs)
			{
				$str = "";
				$bind = [];
				
				
				$vs = explode("||", $vs);
				$col = $vs[0];
				$current_bind_name = ":search_value_{$current}";
				
				if(isset($this->search_columns[$col]))
				{
					if(count($vs) == 2)
					{
						if(!in_array($vs[1], ['empty', '!empty']))
						{
							$vs[2] = $vs[1];
							$vs[1] = "eq";
						}
					}
					
					// check operator value
					if(!in_array($vs[1], ['eq', '!eq', 'gte', 'lte', 'in', '!in', 'like', '!like', 'empty', '!empty']))
						$vs[1] = "eq";
					
					
					// datime-local
					if($this->search_columns[$col]['type'] == 'datetime-local')
					{
						$vs[2] = str_replace('T', ' ', $vs[2]);
					}

					// @todo> tags


					
					$this->user_is_searching = true;
					
					// normal search
					if(empty($this->search_columns[$col]['sql_raw']))
					{
						// eq
						if($vs[1] == 'eq' || $vs[1] == '!eq')
						{
							$exclam = ($vs[1] == 'eq') ? '' : '!';
							
							// $str = "{$col} {$exclam}= :search_value";
							$str = "{$col} {$exclam}= {$current_bind_name}";
							
							// $bind[':search_value'] = $vs[2];
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == 'gte')
						{
							// $str = "{$col} >= :search_value";
							$str = "{$col} >= {$current_bind_name}";
							
							// $bind[':search_value'] = $vs[2];
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == 'lte')
						{
							// $str = "{$col} <= :search_value";
							$str = "{$col} <= {$current_bind_name}";
							// $bind[':search_value'] = $vs[2];
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == 'like' || $vs[1] == '!like')
						{
							$not = ($vs[1] == 'like') ? '' : 'NOT';
							// $str = "{$col} {$not} LIKE :search_value";
							$str = "{$col} {$not} LIKE {$current_bind_name}";
							$vs[2] = str_erase(['%', '_'], $vs[2]);
							// $bind[':search_value'] = "%{$vs[2]}%";
							$bind[$current_bind_name] = "%{$vs[2]}%";
						}
						elseif($vs[1] == 'empty')
						{
							$str = "({$col} = '' OR {$col} = NULL)";
						}
						elseif($vs[1] == '!empty')
						{
							$str = "({$col} != '' AND {$col} != NULL)";
						}
						// @todo> only parameters accepted
						elseif($vs[1] == 'in' || $vs[1] == '!in')
						{
							$values = explode(';', $vs[2]);
							
							$tmp = "";
							foreach($values as $v)
							{
								if(in_array($v, $this->search_columns[$col]['options-values']))
								{
									if(!empty($tmp))$tmp .= ",";
									$tmp .= "'".addslashes($v)."'";
								}
							}
							
							if(empty($tmp))$tmp = 0; # prevent bug
							
							$not = ($vs[1] == 'in') ? '' : 'NOT';
							$str = "{$col} {$not} IN({$tmp})";
						}
						else
						{
							$exclam = ($vs[1] == 'eq') ? '' : '!';
							// $str = "{$col} {$exclam}= :search_value";
							$str = "{$col} {$exclam}= {$current_bind_name}";
							// $bind[':search_value'] = $vs[2];
							$bind[$current_bind_name] = $vs[2];
						}
					}
					else
					{
						$str = $this->search_columns[$col]['sql_raw'];

						$params = $this->search_columns[$col]['sql_raw_params'];
						foreach($params as $pkey => $pval)
							$bind[$pkey] = $pval;
						
						if($vs[1] == 'eq')
						{
							$str = str_replace('[@operator_input]', " = {$current_bind_name}", $str);
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == '!eq')
						{
							$str = str_replace('[@operator_input]', " != {$current_bind_name}", $str);
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == 'gte')
						{
							$str = str_replace('[@operator_input]', " >= {$current_bind_name}", $str);
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == 'lte')
						{
							$str = str_replace('[@operator_input]', " <= {$current_bind_name}", $str);
							$bind[$current_bind_name] = $vs[2];
						}
						elseif($vs[1] == 'like')
						{
							$str = str_replace('[@operator_input]', " like {$current_bind_name}", $str);
							$vs[2] = str_erase(['%', '_'], $vs[2]);
							$bind[$current_bind_name] = "%{$vs[2]}%";
						}
						elseif($vs[1] == '!like')
						{
							$str = str_replace('[@operator_input]', "not like {$current_bind_name}", $str);
							$vs[2] = str_erase(['%', '_'], $vs[2]);
							$bind[$current_bind_name] = "%{$vs[2]}%";
						}
						elseif($vs[1] == 'empty')
						{
							$str = str_replace('[@operator_input]', " IN('', NULL) ", $str);
						}
						elseif($vs[1] == '!empty')
						{
							$str = str_replace('[@operator_input]', " NOT IN('', NULL) ", $str);
						}
						elseif($vs[1] == 'in' || $vs[1] == '!in')
						{
							$not = ($vs[1] == 'in') ? '' : 'NOT';
							
							$values = explode(';', $vs[2]);
							
							$tmp = "";
							foreach($values as $v)
							{
								if(in_array($v, $this->search_columns[$col]['options-values']))
								{
									if(!empty($tmp))$tmp .= ",";
									$tmp .= "'".addslashes($v)."'";
								}
							}
							
							if(empty($tmp))$tmp = 0; # prevent bug
							
							$str = str_replace('[@operator_input]', " {$not} IN($tmp)", $str);
						}
					}

					$this->qWhere($str, $bind);
					$current++;
				}
			}
		}
		
		$this->record_per_page = get('per_page', $this->record_per_page, $this->per_page_options);
		
		
		// grid properties
		$grid = [];
		$grid['object_label'] = $this->object_label;
		$grid['css_id'] = $this->css_id;
		$grid['classes'] = $this->classes;
		$grid['attributes'] = $this->attributes;
		$grid['user_is_searching'] = $this->user_is_searching;
		$grid['header_message'] = $this->header_message;
		$grid['header_message_class'] = $this->header_message_class;
		$grid['header_message_icon'] = $this->header_message_icon;
		$grid['delete_message'] = $this->delete_message;
		$grid['delete_message_warning'] = $this->delete_message_warning;
		$grid['batch_mode'] = $this->batch_mode;
		$grid['batch_actions'] = $this->batch_actions;
		$grid['pagination'] = $this->pagination;
		$grid['col_position'] = $this->col_position;
		$grid['record_per_page'] = $this->record_per_page;
		$grid['order_by_columns'] = $this->order_by_columns;
		$grid['search_columns'] = $this->search_columns;
		$grid['columns'] = $this->columns;
		$grid['row_btn_actions'] = $this->row_btn_actions;
		$grid['nav_btn_actions'] = $this->nav_btn_actions;
		
		$grid['no_record_message'] = $this->no_record_message;
		$grid['per_page_options'] = $this->per_page_options;
		
		$grid['order_by_init'] = (empty($this->order_by_init)) ? @$this->order_by_columns[0] : $this->order_by_init;
		$grid['order_by_sort_init'] = $this->order_by_sort_init;
		$grid['user_searches'] = [];
		
		
		// generate col order by url
		$uri = App()->request->getRequestUri();
		$grid['per_page_url'] = http_query_replace(['per_page' => null], $uri)."&per_page=";
		$grid['page_direct_url'] = http_query_replace(['page' => null], $uri)."&page=";
		$grid['order_by_url'] = [];
		
		foreach($this->order_by_columns as $col)
		{
			if(!isset($_GET['order_by_sort']) || !in_array($_GET['order_by_sort'], ['asc', 'desc']))
				$sort = $this->order_by_sort_init;
			elseif($_GET['order_by_sort'] == 'desc')
				$sort = 'asc';
			else
				$sort = 'desc';
			
			$grid['order_by_url'][$col] = http_query_replace(['order_by' => $col, 'order_by_sort' => $sort], $uri);
		}
		
		
		// get rows
		$select = '*';
		if(isset($this->query['SELECT']))
		{
			$select .= ",\n".join(',', $this->query['SELECT']);
		}
		
		$obj = Db($this->table)->select($select);
		
		$sql_where = "";
		if(isset($this->query['WHERE']) && count($this->query['WHERE']))
		{
			foreach($this->query['WHERE'] as $w)
			{
				if(!empty($sql_where))  $sql_where .= "\n AND ";
				$sql_where .= $w;
			}
		}
		
		if(!empty($sql_where))
			$obj->where($sql_where);
		
		// order by
		if(!count($this->order_by_columns))
		{
			if(isset($this->query['ORDER-BY']))
			{
				$obj->orderBy("{$this->query['ORDER-BY']}");
			}
		}
		else
		{
			$order_by = get('order_by', $this->order_by_init);
			if(!in_array($order_by, $this->order_by_columns))
				$order_by = $this->order_by_columns[0];
			
			$order_by_sort = get('order_by_sort', $this->order_by_sort_init);
			if(!in_array($order_by_sort, ['asc', 'desc']))
				$order_by_sort = $this->order_by_sort_init;
				
			$obj->orderBy("{$order_by} {$order_by_sort}");
		}

		$this->sql = $obj->getSQL();

		$cur_page = App()->request->query->get('page') ?? 1;
		$per_page = (!$this->record_per_page) ? 9999 : $this->record_per_page;
		$pager = Db()->paginate($this->sql, $this->query_params, $cur_page, $per_page, true);

		$page_prev = $pager['current_page'] - 1;
		if($page_prev < 1)$page_prev = 1;
		
		$page_next = $pager['current_page'] + 1;
		if($page_next > $pager['page_end'])$page_next = $pager['page_end'];
		
		$grid['previous_url'] = http_query_replace(['page' => null], $uri)."&page={$page_prev}";
		$grid['next_url'] = http_query_replace(['page' => null], $uri)."&page={$page_next}";
		
		if(!count($pager['data']))
			$grid['classes'] = str_erase(['table-hover', 'table-striped'], $grid['classes']);
		

		$data = [];
		$data['grid'] = $grid;
		$data['pager'] = $pager;
		
		for($i=0; $i < count($data['pager']['data']); $i++)
		{
			// xTags
			if(isset($data['pager']['data'][$i]['xTags']))
			{
				$data['pager']['data'][$i]['xTags'] = explode(', ', $data['pager']['data'][$i]['xTags']);
				sort($data['pager']['data'][$i]['xTags']);

			}

			if(is_callable($this->hookData))
				$data['pager']['data'][$i] = call_user_func($this->hookData, $data['pager']['data'][$i]);
		}
		
		
		$content = App()->twig->render('core/component/datagrid/view/datagrid.twig', $data);
		return $content;
	}

}