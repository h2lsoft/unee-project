<?php

namespace Component;

use Core\Config;
use Core\Controller;
use Core\Session;
use h2lsoft\Data\Validator;
use Model\Plugin;

class Form {
	
	private string $table;
	public int $id = 0;
	
	private Controller $controller;
	private string $title = '';
	private string $name = 'former';
	private string $class = '';
	private array $attributes = [];
	
	private array $fields = [];
	private array $_i18n = [];
	
	private array $exceptions = [];

	private string $last_input;
	public Validator $validator;
	
	public bool $is_adding = true;
	public bool $is_editing = false;
	
	private array $values = [];
	private array $thumbnails = []; # array for image conversion

	public array $js_path = [];

	
	public function __construct(string $class="", array $attributes=[])
	{
		$this->class = $class;
		$this->attributes = $attributes;
		
		// i18n
		$_i18n = [];
		if(App()->locale != 'en' && file_exists(APP_PATH."/core/i18n/".App()->locale.".php"))
			$this->_i18n = require APP_PATH."/core/i18n/".App()->locale.".php";
		
		
		return $this;
	}

	public function loadAssetsJs(array $scripts):void
	{
		foreach($scripts as $js)
		{
			if(basename($js) == $js)
			{
				$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[0]['file'];
				$js = str_erase(APP_PATH, dirname($caller)."/assets/js/{$js}");
			}

			$this->js_path[] = $js;
		}
	}


	public function linkController(Controller $controller, int $id = 0):void
	{
		$this->controller = $controller;
		$this->validator = $controller->validator;
		$this->table = $controller->table;
		$this->id = $id;
		
		$this->title = (!$id) ? "New record" : "Edit record";
		
		if($id || (get('cid')))
		{
			if(!$id)
			{
				$id = get('cid');
			}
			else
			{
				$this->is_adding = false;
				$this->is_editing = true;
			}

			
			$record = Db($this->table)->query("SELECT * FROM {$this->table} WHERE deleted = 'no' AND id = :id", [':id' => $id])->fetch();
			
			foreach($record as $key => $val)
			{
				if(!in_array($key, ['deleted','created_at','created_by','updated_at','updated_by','deleted_at', 'deleted_by']))
					$this->values[$key] = $val;
			}
		}
	}
	
	
	public function getMaxPosition(string $sql_added="", array $binds=[]):int
	{
		if(!empty($sql_added))$sql_added = " AND {$sql_added} ";
		
		$sql = "SELECT MAX(position) FROM {$this->table} WHERE deleted = 'no' {$sql_added}";
		$pos = (int)Db()->query($sql, $binds)->fetchOne();
		$pos += 1;
		
		return $pos;
	}
	
	
	public function setTitle(string $title):void
	{
		$this->title = $title;
	}
	
	public function initValue(string $col, string|array $value):void
	{
		$this->values[$col] = $value;
	}
	public function initValues(array $values):void
	{
		$this->values = $values;
	}

	public function getValue(string $key):string {
		return isset($this->values[$key]) ? $this->values[$key] : '';
	}

	public function setValue(string $value, string $name=""):Form
	{
		$name = !empty($name) ? $name : $this->last_input;
		$this->fields[$name]['value'] = $value;
		
		return $this;
	}
	public function setHelp(string $help, string $name=""):Form
	{
		$name = !empty($name) ? $name : $this->last_input;
		$this->fields[$name]['help'] = $help;
		
		return $this;
	}
	
	
	public function setAfter(string $content, string $name=""):Form
	{
		if(empty($name))$name = $this->last_input;
		$this->fields[$name]['after'] = $content;
		return $this;
	}
	
	public function setIconBefore(string $icon):Form
	{
		$this->fields[$this->last_input]['icon_before'] = $icon;
		return $this;
	}
	public function setInputSize(int $size, string $added_class=''):Form
	{
		$this->fields[$this->last_input]['input_col_size'] = $size;
		$this->fields[$this->last_input]['input_col_class'] = $added_class;
		return $this;
	}
	
	
	
	
	public function datalist(string $table='', string $column='', string $where='', array $params=[]):Form
	{
		$name = $this->last_input;

		if(empty($table))$table = $this->table;
		if(empty($column))$column = $name;
		if(!empty($where))$where = " AND {$where}";

		// $this->fields[$name]['attributes']['list'] = "?ajaxer=1&ajaxer-action=autocomplete&field={$name}&q=";
		$this->fields[$name]['attributes']['autocomplete'] = "off";
		$this->fields[$name]['attributes']['list'] = "datalist_{$name}";
		

		$sql = "SELECT {$column} AS value, {$column} AS label  FROM {$table} WHERE deleted = 'no' {$where} GROUP BY {$column} ORDER BY {$column}";
		$this->fields[$name]['datalist'] = DB()->query($sql, $params)->fetchAll();
		
		return $this;
	}
	
	// INPUTS *********************************************************************************************************************************
	public function addText(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		$this->last_input = $name;
		
		$this->fields[$name] = [];
		$this->fields[$name]['type'] = 'text';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = empty($label) ? str_replace('_', ' ', $name) : $label;
		$this->fields[$name]['required'] = $required;
		$this->fields[$name]['class'] = @$attributes['class'];
		$this->fields[$name]['attributes'] = $attributes;
		$this->fields[$name]['help'] = $help;
		$this->fields[$name]['after'] = $after;
		$this->fields[$name]['value'] = "";
		$this->fields[$name]['icon_before'] = "";
		
		$this->fields[$this->last_input]['input_col_size'] = '';
		$this->fields[$this->last_input]['input_col_class'] = '';
		
		return $this;
	}
	
	public function addTextarea(string $name, string $label='', bool $required=false, array $attributes=[], string $help=''):Form
	{
		$this->addText($name, $label, $required, $attributes, $help);
		$this->fields[$name]['type'] = 'textarea';
		return $this;
	}

	public function addHtmlarea(string $name, string $label='', bool $required=false, array $attributes=[], string $help=''):Form
	{
		@$attributes['class'] .= " blockee-editor";

		$attributes['data-blockee-filemanager-url'] = "/@backend/file-manager/";

		$this->addText($name, $label, $required, $attributes, $help);
		$this->fields[$name]['type'] = 'htmlarea';
		return $this;
	}
	
	public function addNumber(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'number';
		return $this;
	}
	
	public function addUrl(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		if(!isset($attributes['data-prefix']))
			$attributes['data-prefix'] = '';

		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'text';
		$this->fields[$name]['icon_before'] = 'bi bi-link';

		return $this;

	}

	public function addFileBrowser(string $name, string $label='', bool $required=false, string $path="", string $filter="", bool $upload_open=false, array $attributes=[], string $help=''):Form
	{
		$this->addText($name, $label, $required, $attributes, $help);
		$this->fields[$name]['type'] = 'file-manager';
		$this->fields[$name]['icon_before'] = 'bi bi-globe';
		$this->fields[$name]['path'] = $path;
		$this->fields[$name]['filter'] = $filter;
		$this->fields[$name]['upload_open'] = $upload_open;

		return $this;
	}
	
	public function addDate(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		if(!isset($attributes['class']))
			$attributes['class'] = 'text-center';
		else
			$attributes['class'] .= ' text-center';

		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'date';
		$this->fields[$name]['icon_before'] = 'bi bi-calendar';

		$this->setInputSize(2);


		return $this;
	}
	
	public function addDatetime(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		if(!isset($attributes['class']))
			$attributes['class'] = 'text-center';
		else
			$attributes['class'] .= ' text-center';

		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'datetime';
		$this->fields[$name]['icon_before'] = 'bi bi-calendar';

		$this->setInputSize(3);
		
		return $this;
	}
	
	
	public function addEmail(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'email';
		$this->fields[$name]['icon_before'] = 'bi bi-envelope';
		$this->fields[$name]['class'] .= ' lower ';
		
		return $this;
	}
	
	public function addTel(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'tel';
		$this->fields[$name]['icon_before'] = 'bi bi-telephone';
		return $this;
	}
	
	public function addPassword(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'password';
		$this->fields[$name]['icon_before'] = 'bi bi-lock';
		return $this;
	}
	
	public function addSwitch(string $name, string $label='', bool $required=false, array $attributes=[], string $help='', string $after=""):Form
	{
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'switch';
		return $this;
	}




	public function addSelectSql(string $name, string $label, bool $required=false, string $caption="", array $attributes=[], string $column_value='id', string $column_label='name', string $table='', string $where='', array $binds=[], string $order_by='', string $help='', string $after=""):Form
	{
		if(preg_match("/id$/", $name))
			if(empty($table))$table = str_erase('_id', $name);

		if(empty($table))$table = $this->table;

		if(empty($column_value))$column_value = 'id';
		if(empty($column_label))$column_label = 'name';

		if(!isset($attributes['class']) || !str_contains($attributes['class'], 'select-search'))
		{
			if(!isset($attributes['class']))$attributes['class'] = '';
			$attributes['class'] .= ' select-search';
		}

		if(empty($caption))
		{
			$attributes['data-placeholder'] = ' ';
			$caption = false;
		}
		else
		{
			$attributes['data-placeholder'] = $caption;
		}


		$sql = "SELECT {$column_value} AS value, {$column_label} AS label FROM {$table} WHERE deleted = 'no' ";
		if(!empty($where))$sql .= $where;


		if(empty($order_by))
			$sql .= " ORDER BY {$column_label}";
		else
			$sql .= " ORDER BY {$order_by}";

		
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'select';
		$this->fields[$name]['caption'] = $caption;
		$this->fields[$name]['options'] = DB()->query($sql, $binds)->fetchAll();
		return $this;
	}


	public function addSelectEnum(string $name, string $label, bool $required=false, string $caption="", bool $options_sort=true,  array $attributes=[], string $help=''):Form
	{
		$table = $this->table;
		$column = $name;

		$sql = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$column}';";
		$values = Db()->query($sql)->fetchOne();
		$values = explode("','", $values);

		$v_parsed = [];
		for($i=0; $i < count($values); $i++)
		{
			$v = str_erase([",'", "'", "',"], $values[$i]);
			if($i == 0) $v = str_erase("enum(", $v);
			if($i == count($values)-1) $v = str_erase(")", $v);

			$v = trim($v);

			$v_parsed[] = ['label' => $v, 'value' => $v];
		}

		if($options_sort)
			sort($v_parsed);

		$this->addSelect($name, $label, $required, $v_parsed, $caption, $attributes, $help);


		return $this;
	}


	
	public function addSelect(string $name, string $label, bool $required=false, array $options=[], string $caption="", array $attributes=[], string $help='', string $after="", bool $dynamic=false):Form
	{
		// format options
		$options2 = [];
		foreach($options as $option)
		{
			if(!isset($option['value']))
			{
				if(!is_array($option))
				{
					$options2[] = ['label' => ucfirst($option), 'value' => $option];
				}
				elseif(count($option) >= 2)
				{
					$options2[] = ['label' => $option[0], 'value' => $option[1]];
				}
			}
			else
			{
				$options2[] = $option;
			}
		}
		
		$options = $options2;
		
		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'select';
		$this->fields[$name]['caption'] = $caption;
		$this->fields[$name]['options'] = $options;
		$this->fields[$name]['dynamic'] = $dynamic;
		return $this;
	}


	public function addSelectColor(string $name, string $label, bool $required=false, string $caption="", array $attributes=[], string $help='', string $after=""):Form
	{
		if(!isset($attributes['class']))$attributes['class'] = '';
		$attributes['class'] .= " select-color";

		$options = \Plugin\Core_Frontend\PageController::getColorsOptions();
		$this->addSelect($name, $label, $required, $options, $caption, $attributes, $help, $after);
		$this->setInputSize(2);
		$this->fields[$name]['icon_before'] = ' ';


		return $this;
	}


	public function addRadio(string $name, string $label, bool $required=false, array $options=[], string $caption="", array $attributes=[], string $help='', string $after=""):Form
	{
		// format options
		$options2 = [];
		foreach($options as $option)
		{
			if(!isset($option['value']))
			{
				if(!is_array($option))
				{
					$options2[] = ['label' => ucfirst($option), 'value' => $option];
				}
				elseif(count($option) >= 2)
				{
					$options2[] = ['label' => $option[0], 'value' => $option[1]];
				}
			}
			else
			{
				$options2[] = $option;
			}
		}

		$options = $options2;

		$this->addText($name, $label, $required, $attributes, $help, $after);
		$this->fields[$name]['type'] = 'radio';
		$this->fields[$name]['caption'] = $caption;
		$this->fields[$name]['options'] = $options;
		return $this;
	}



	public function addTagManager(string $signature='', bool $required=false, array $attributes=[]):Form
	{
		$name = 'xTags';

		if(empty($signature))$signature = $this->table;

		$attributes['placeholder'] = "Press enter to add your tag";

		$this->addText($name, 'Tag', $required, $attributes);
		$this->fields[$name]['type'] = 'text';
		$this->fields[$name]['signature'] = $signature;
		$this->fields[$name]['class'] = 'input-tag-manager';

		$this->datalist('xcore_tag', 'tag', "signature = :signature", [':signature' => $signature]);


		if($this->id)
		{
			$sql = "SELECT tag FROM xcore_tag WHERE deleted = 'no' AND signature = :signature AND record_id = {$this->id} GROUP BY tag ORDER BY tag";
			$tags = DB()->query($sql, [':signature' => $signature])->fetchAllOne();
			$tags = join(',', $tags);

			$this->setValue($tags);
		}



		return $this;
	}


	public function addHeader(string $name, string $label="", array $attributes=[]):Form
	{
		$this->fields[$name]['type'] = 'header';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = empty($label) ? str_replace('_', ' ', $name) : $label;
		$this->fields[$name]['class'] = @$attributes['class'];
		$this->fields[$name]['attributes'] = $attributes;
		return $this;
	}

	public function addTab(string $name, string $label="", array $attributes=[]):Form
	{
		$this->fields[$name]['type'] = 'tab';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['label'] = empty($label) ? str_replace('_', ' ', $name) : $label;
		$this->fields[$name]['class'] = @$attributes['class'];
		$this->fields[$name]['attributes'] = $attributes;



		return $this;
	}

	public function addTabEnd():Form
	{
		$name = uniqid('tab_');
		$this->fields[$name]['type'] = 'tab-close';
		return $this;
	}

	public function addTabMenu():Form
	{
		$name = uniqid('tab_');
		$this->fields[$name]['type'] = 'tab-menu';
		return $this;
	}

	public function addTabMenuEnd():Form
	{
		$name = uniqid('tab_');
		$this->fields[$name]['type'] = 'tab-menu-close';
		return $this;
	}


	
	public function addHr(array $attributes=[]):Form
	{
		if(empty($name))$name = uniqid('hr_');
		
		$this->fields[$name]['type'] = 'hr';
		$this->fields[$name]['name'] = $name;
		return $this;
	}
	public function addHtml(string $html):Form
	{
		$name = uniqid('html_');
		$this->fields[$name]['type'] = 'html';
		$this->fields[$name]['name'] = $name;
		$this->fields[$name]['html'] = $html;

		return $this;
	}
	
	
	public function addFile(
		string $name, string $label="", bool $required=false,
		string $upload_dir="", string $max_upload_size="",
		array $allowed_exts=[], array $allowed_mimes=[],
		string $help="",
		array $attributes=[],
		string $file_url=""
	):Form
	{
		
		if(empty($upload_dir))
		{
			$upload_dir = Config::get("dir/{$name}");
		}
		
		$upload_dir = rtrim($upload_dir, "/");
		
		if(empty($max_upload_size))
		{
			$max_upload_size = ini_get('upload_max_filesize');
			$max_upload_size = str_replace(['K','M','G'], [' Ko', ' Mo', ' Go'], $max_upload_size);
		}
		
		$allowed_mimes = array_unique($allowed_mimes);
		
		$size_octet = strtolower($max_upload_size);
		$size_octet = str_erase(' ', strtolower($size_octet));
		
		// unit conversion
		$unit = 1000;
		if(strpos($size_octet, 'mo') !== false || strpos($size_octet, 'mb') !== false || strpos($size_octet, 'm') !== false) $unit = 1000*1000;
		elseif(strpos($size_octet, 'go') !== false || strpos($size_octet, 'gb') !== false  || strpos($size_octet, 'g')) $unit = 1000*1000*1000;
		$size_octet = (int)(str_replace(['ko','kb','k','mo','mb','m','go','gb','g'], '', $size_octet)) * $unit;
		
		if(empty($help))
		{
			$allowed_exts_str = join(' ', $allowed_exts);
			$help = "<i18n>Files must be less than</i18n> : <strong>{$max_upload_size}</strong><br>\n
					 <i18n>Allowed file types</i18n> :  <strong>{$allowed_exts_str}</strong>";
		}
		
		$this->addText($name, $label, $required, $attributes, $help);
		$this->fields[$name]['type'] = 'file';
		$this->fields[$name]['max_upload_size'] = $max_upload_size;
		$this->fields[$name]['max_file_size'] = $size_octet;
		$this->fields[$name]['accept'] = ".".join(', .', $allowed_exts);
		$this->fields[$name]['extension'] = $allowed_exts;
		$this->fields[$name]['mimes'] = $allowed_mimes;
		$this->fields[$name]['upload_dir'] = $upload_dir;
		$this->fields[$name]['image_url'] = str_erase(APP_PATH, $upload_dir);
		$this->fields[$name]['file_url'] = $file_url;

		return $this;
	}
	
	public function addFileImage(
									string $name,
									string $label="",
									bool $required=false,
								 	string $upload_dir="",
									string $max_upload_size="",

									array $allowed_exts=['jpg', 'jpeg', 'png', 'gif', 'webp'],
									array $allowed_mimes=[],

									int|bool $width=false,
									bool $width_constraint=false,

									int|bool $height=false,
									bool $height_constraint=false,

									string $help="",
									array $attributes=[]
								):Form
	{
		
		
		if(empty($upload_dir))
		{
			$upload_dir = Config::get("dir/{$name}");
		}
		
		$upload_dir = rtrim($upload_dir, "/");
		
		
		if(empty($max_upload_size))
		{
			// $max_upload_size = ini_get('upload_max_filesize');
			// $max_upload_size = str_replace(['K','M','G'], [' Ko', ' Mo', ' Go'], $max_upload_size);
			$max_upload_size = Config::get("frontend/blog/image/max_size");
		}



		foreach($allowed_exts as $ext)
		{
			$allowed_mimes[] = "image/".strtolower($ext);
		}
		
		$allowed_mimes = array_unique($allowed_mimes);
		
		
		$size_octet = strtolower($max_upload_size);
		$size_octet = str_erase(' ', strtolower($size_octet));
		
		// unit conversion
		$unit = 1000;
		if(strpos($size_octet, 'mo') !== false || strpos($size_octet, 'mb') !== false || strpos($size_octet, 'm') !== false) $unit = 1000*1000;
		elseif(strpos($size_octet, 'go') !== false || strpos($size_octet, 'gb') !== false  || strpos($size_octet, 'g')) $unit = 1000*1000*1000;
		$size_octet = (int)(str_replace(['ko','kb','k','mo','mb','m','go','gb','g'], '', $size_octet)) * $unit;

		if(empty($help))
		{
			$allowed_exts_str = join(' ', $allowed_exts);
			$help = "<i18n>Files must be less than</i18n> : <strong>{$max_upload_size}</strong><br>\n
					 <i18n>Allowed file types</i18n> :  <strong>{$allowed_exts_str}</strong><br>\n";

			if($width || $height)
			{
				$help .= "<i18n>Dimensions</i18n> : ";
				$help .= "<b>";
				$help .= ($width) ? $width : "(none)";
				$help .= " x ";
				$help .= ($height) ? $height : "(none)";
				$help .= "</b>";
				$help .= "<br />";
			}

		}




		
		$this->addText($name, $label, $required, $attributes, $help);
		$this->fields[$name]['type'] = 'file-image';
		$this->fields[$name]['max_upload_size'] = $max_upload_size;
		$this->fields[$name]['max_file_size'] = $size_octet;
		$this->fields[$name]['accept'] = ".".join(', .', $allowed_exts);
		$this->fields[$name]['extension'] = $allowed_exts;
		$this->fields[$name]['mimes'] = $allowed_mimes;
		$this->fields[$name]['upload_dir'] = $upload_dir;
		$this->fields[$name]['image_url'] = str_erase(APP_PATH, $upload_dir);
		$this->fields[$name]['image_width'] = $width;
		$this->fields[$name]['image_width_constraint'] = $width_constraint;
		$this->fields[$name]['image_height'] = $height;
		$this->fields[$name]['image_height_constraint'] = $height_constraint;

		return $this;
	}
	

	
	
	
	public function isSubmitted():bool
	{
		$submitted = (requestIs('post') && !$this->id) || (requestIs('put') && $this->id);
		
		if($submitted)
		{
			$this->autoRulesCompile();
		}
		else
		{
			foreach($this->values as $f => $value)
			{
				if(isset($this->fields[$f]))
					$this->fields[$f]['value'] = $value;
			}
		}
		
		return $submitted;
	}
	private function autoRulesCompile():void
	{
		// auto compile
		foreach($this->fields as $field => $attributes)
		{
			if(in_array($this->fields[$field]['type'], ['text','radio', 'textarea', 'htmlarea', 'number', 'tel', 'password', 'email', 'select', 'switch', 'file-manager', 'file-image', 'date', 'datetime', 'time']))
			{
				$label = isset($this->_i18n[$this->fields[$field]['label']]) ? $this->_i18n[$this->fields[$field]['label']] : $this->fields[$field]['label'];
				
				// required
				if($this->fields[$field]['required'])
				{
					if($this->fields[$field]['type'] == 'file-image')
					{
						if($this->is_adding || ($this->is_editing && empty($this->values[$field])))
							$this->validator->input($field, $label)->fileRequired();
					}
					else
					{
						// force no if empty
						if($this->fields[$field]['type'] == 'switch' && !$this->validator->inputGet($field, false))
							$this->validator->inputSet($field, 'no');

						$this->validator->input($field, $label)->required();
					}

				}
				// file-image
				if($this->fields[$field]['type'] == 'file-image')
				{
					// dir is writable
					if(!is_writable($this->fields[$field]['upload_dir']))
					{
						$label = isset($this->_i18n[$this->fields[$field]['label']]) ? $this->_i18n[$this->fields[$field]['label']] : $this->fields[$field]['label'];

						$this->validator->input($field, $label)->addError("`[FIELD]`: directory is not writable => `{$this->fields[$field]['upload_dir']}`");
					}

					if($this->fields[$field]['max_upload_size'])
						$this->validator->input($field, $label)->fileMaxSize($this->fields[$field]['max_upload_size']);

					$this->validator->input($field, $label)->fileExtension($this->fields[$field]['extension']);

					if(count($this->fields[$field]['mimes']))
						$this->validator->input($field, $label)->fileMime($this->fields[$field]['mimes']);

					if($this->fields[$field]['image_width'])
						$this->validator->input($field, $label)->fileImageWidth($this->fields[$field]['image_width'], $this->fields[$field]['image_width_constraint']);

					if($this->fields[$field]['image_height'])
						$this->validator->input($field, $label)->fileImageHeight($this->fields[$field]['image_height'], $this->fields[$field]['image_height_constraint']);

				}
				
				// date
				if($this->fields[$field]['type'] == 'date')
				{
					$this->validator->input($field, $label)->date();
				}
				
				// switch
				if($this->fields[$field]['type'] == 'switch')
				{
					if(!$this->validator->inputGet($field, false))
						$this->validator->inputSet($field, 'no');
					
					$this->validator->input($field, $label)->in(['yes','no']);
				}
				
				// number
				if($this->fields[$field]['type'] == 'number')
				{
					$this->validator->input($field, $label)->integer();
				}
				
				// email
				if($this->fields[$field]['type'] == 'email')
				{
					$this->validator->input($field, $label)->email();
				}
				
				// select + radio
				if(
					($this->fields[$field]['type'] == 'select' && isset($this->fields[$field]['dynamic']) && !$this->fields[$field]['dynamic']) ||
					$this->fields[$field]['type'] == 'radio'
				)
				{
					$list = [];
					foreach($this->fields[$field]['options'] as $o)
					{
						if(strlen($o['value']) > 0)
							$list[] = $o['value'];
					}
					
					$this->validator->input($field, $label)->in($list);
				}
				
			}
		}
	}
	public function isValid():bool {
		return $this->validator->success();
	}
	
	public function createThumbnail(string $suffix='_thumb', int $width=250, int $height=0, string $background_color=''):Form
	{
		if(!isset($this->thumbnails[$this->last_input]))
			$this->thumbnails[$this->last_input] = [];
		
		$tmp = [];
		$tmp['suffix'] = $suffix;
		$tmp['width'] = $width;
		$tmp['height'] = $height;
		$tmp['background_color'] = $background_color;
		
		$this->thumbnails[$this->last_input][] = $tmp;
		
		return $this;
	}
	
	public function input(string $name, string $label=""):Validator
	{
		$this->validator->input($name, $label);
		return $this->validator;
	}
	public function inputGet(string $name, string $default=''):string|array
	{
		return $this->validator->inputGet($name, $default);
	}
	
	public function inputSet(string $name, string|array $value):void
	{
		$this->validator->inputSet($name, $value);
	}
	
	
	public function addError(string $name, string $message, array $params=[])
	{
		$message = $this->_i18n[$message] ?? $message;
		return $this->validator->input($name)->addError($message, $params);
	}

	public function save(array $exceptions=[], array $dynamic_fields=[], array $xss_exceptions=[]):array
	{
		global $app;

		// exclude field
		$this->exceptions[] = 'xTags';
		$this->exceptions[] = 'xTags_raw';
		$this->exceptions = array_merge($exceptions, $this->exceptions);
		$this->exceptions = array_unique($this->exceptions);
		
		$f = [];
		foreach($this->fields as $field)
		{
			if(isset($field['name']) && !in_array($field['name'], $this->exceptions) && in_array($field['type'], ['text', 'radio', 'textarea', 'htmlarea', 'select', 'number', 'switch', 'email', 'tel', 'date', 'datetime', 'time', 'file-manager']))
			{
				$f[$field['name']] = $this->inputGet($field['name']);
				
				// switch
				if($field['type'] == 'switch')
				{
					$f[$field['name']] = strtolower($f[$field['name']]);
					if($f[$field['name']] == '')$f[$field['name']] = 'no';
				}

				// htmlarea (auto-exception)
				if($field['type'] == 'htmlarea')
					$xss_exceptions[] = $field['name'];
			}
		}
		
		// add dynamic field added
		foreach($dynamic_fields as $key => $value)
		{
			$f[$key] = $this->inputGet($key, $value);
		}

		// xss protection
		foreach($f as $key => $value)
		{
			if(in_array($key, $xss_exceptions))continue;
			$f[$key] = XSSProtection($value);
		}
		
		$f = $this->controller->onSaveDatabaseBefore($f);
		if(!$this->id)
		{
			$f = $this->controller->onSaveDatabaseAddBefore($f);
			$this->id = DB($this->table)->insert($f, Session::get('auth.login'));
			$this->controller->onSaveDatabaseAddAfter($f);

			$log_action = 'add';
		}
		else
		{
			$f = $this->controller->onSaveDatabaseEditBefore($f);
			DB($this->table)->update($f, $this->id, 1, Session::get('auth.login'));
			$this->controller->onSaveDatabaseEditAfter($f);

			$log_action = 'edit';
		}

		// add logo action
		$log_plugin = \Model\Plugin::extractName();
		\Core\Log::write($log_action, '', $f, $this->id, 'info', $log_plugin);

		$this->controller->onSaveDatabaseAfter($f);

		// tag manager
		if(isset($this->fields['xTags']))
		{
			$tags = $this->inputGet('xTags_raw');


			$tags = explode('[@]', $tags);
			$tags = array_map('trim', $tags);


			// erase all and recreate
			$sql = "DELETE FROM xcore_tag WHERE deleted = 'no' AND signature = :signature and record_id = {$this->id}";
			DB()->query($sql, [':signature' => $this->fields['xTags']['signature']]);

			$f['_links']['xcore_tag'] = ['_data' => [], '_mode' => 'insert', '_delete-where-before' => "signature = '{$this->fields['xTags']['signature']}' and record_id = {$this->id}"];
			foreach($tags as $tag)
			{
				$tag = trim($tag);
				if(empty($tag))continue;

				$tmp_f = ['signature' => $this->fields['xTags']['signature'], 'tag' => $tag, 'record_id' => $this->id];
				DB('xcore_tag')->insert($tmp_f);
				$f['_links']['xcore_tag']['_data'][] = $tmp_f;
			}
		}
		
		
		// upload files
		foreach($this->fields as $field)
		{
			if(isset($field['name']) && !in_array($field['name'], $this->exceptions) && in_array($field['type'], ['file', 'file-image']))
			{
				// delete filename
				if($this->inputGet("{$field['name']}__delete") == 'yes' && isset($this->values[$field['name']]) && !empty($this->values[$field['name']]) && file_exists(APP_PATH.$this->values[$field['name']]))
				{
					unlink(APP_PATH.$this->values[$field['name']]);
					\Core\Log::write('unlink file', $this->values[$field['name']], [], $this->id, 'info', $log_plugin);


					DB($this->table)->update([$field['name'] => ""], $this->id, 1);
					$f[$field['name']] = '';
					
					if(isset($this->thumbnails[$field['name']]))
					{
						foreach($this->thumbnails[$field['name']] as $thumb)
						{
							$thumb_file = APP_PATH.filename_add_suffix($thumb['suffix'], $this->values[$field['name']]);
							
							if(file_exists($thumb_file))
							{
								\Core\Log::write('unlink thumbnail', str_erase(APP_PATH, $thumb_file), [], $this->id, 'info', $log_plugin);
								unlink($thumb_file);
							}
						}
					}
				}
				
				if(isset($_FILES[$field['name']]) && $_FILES[$field['name']]['size'] > 0)
				{
					$ext = file_get_extension($_FILES[$field['name']]['name']);
					$new_name = "{$this->id}.{$ext}";
					
					if(@move_uploaded_file($_FILES[$field['name']]['tmp_name'], $field['upload_dir']."/{$new_name}"))
					{
						// update db
						$abs_path = str_erase(APP_PATH, $field['upload_dir']."/{$new_name}");
						DB($this->table)->update([$field['name'] => $abs_path], $this->id, 1);
						$f[$field['name']] = $abs_path;

						\Core\Log::write('upload file', "", [$field['name'] => $abs_path], $this->id, 'info', $log_plugin);
						
						// thumbnail
						if(isset($this->thumbnails[$field['name']]))
						{
							foreach($this->thumbnails[$field['name']] as $thumb)
							{
								$thumb_name = "{$this->id}{$thumb['suffix']}.{$ext}";
								$image = new \Gumlet\ImageResize($field['upload_dir']."/{$new_name}");
								
								if($thumb['width'] && !$thumb['height'])
									$image->resizeToWidth($thumb['width']);
								elseif(!$thumb['width'] && $thumb['height'])
									$image->resizeToHeight($thumb['height']);
								else
									$image->resize($thumb['width'], $thumb['height'], true);
								
								$image->save($field['upload_dir']."/{$thumb_name}");

								\Core\Log::write('upload thumbnail', $field['upload_dir']."/{$thumb_name}", [], $this->id, 'info', $log_plugin);
							}
						}
					}
				}
			}
		}
		
		
		$f = $this->controller->onSaveAfter($f);

		// add versioning
		$application = \Model\Plugin::extractName();
		$plugin_info = \Model\Plugin::findOne("route_prefix_name = :name", [':name' => $application]);
		if($plugin_info && $plugin_info['versioning'] == 'yes')
		{
			$record_id = $this->id;
			$values = $f;
			$table = $this->table;

			\Model\Versioning::add($application, $table, $record_id, $values, \Model\User::getUID());
		}



		return $f;
	}
	
	private array $response_added = [];
	public function responseAdd(string $key, mixed $val):void
	{
		$this->response_added[$key] = $val;
	}
	
	public function json():string
	{
		$response = $this->validator->result();
		$response['data'] = $this->response_added;
		
		die(json_encode($response));
	}
	public function render():string
	{

		
		
		$data = [];
		$data['form']['is_addind'] = $this->is_adding;
		$data['form']['is_editing'] = $this->is_editing;
		$data['form']['id'] = $this->id;
		$data['form']['name'] = $this->name;
		$data['form']['class'] = $this->class;
		$data['form']['title'] = $this->title;
		$data['form']['attributes'] = $this->attributes;
		
		$data['form']['fields'] = $this->fields;

		$data['form']['js_files'] = $this->js_path;
		
		$content = App()->twig->render('core/component/form/view/form.twig', $data);
		return $content;
	}
	
}