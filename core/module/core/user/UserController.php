<?php

namespace Plugin\Core;

use Core\Config;
use Core\Session;

class UserController extends \Core\Controller {

	public string $table = 'xcore_user';
	public string $object_label = 'user';

	/**
	 * @route /@backend/@module/
	 * @route /@backend/@module/delete/{id}/ {method:"DELETE", controller:"delete"}
	 */
	public function list() {
		
		$datagrid = new \Component\DataGrid($this->object_label, $this->table, 100);
		$datagrid->qSelectEmbed('xcore_group','name','group');
		
		$priority = \Core\Session::get('auth.group_priority');
		$datagrid->qWhere("xcore_group_id in(select id from xcore_group WHERE deleted = 'no' AND priority >= :priority)", [':priority' => $priority]);
		
		// search
		$datagrid->searchAddNumber('id');
		$datagrid->searchAddSelectSql('xcore_group_id', 'Group');
		$datagrid->searchAddSelectSql('language');
		$datagrid->searchAddSelectSql('company');
		$datagrid->searchAddText('lastname');
		$datagrid->searchAddText('email');
		$datagrid->searchAddText('login');
		$datagrid->searchAddBoolean('active');
		$datagrid->searchAddTagManager('xcore_user');

		
		// columns
		$datagrid->addColumn('id', '', true);
		$datagrid->addColumnImage('avatar', '', false, 'img-avatar border', false);
		$datagrid->addColumn('group', '', false, 'min');
		$datagrid->addColumn('fullname', "full name", false);
		$datagrid->addColumn('login', "", false);
		$datagrid->addColumnHtml('email', "", false);
		$datagrid->addColumnBoolean('active');
		$datagrid->addColumnDatetime('last_connection_date', 'Last connection');
		$datagrid->addColumnBoolean('active');
		$datagrid->addColumnTags('xcore_user');
		
		// hookData
		$datagrid->hookData(function($row){
			
			if(empty($row['avatar']))
			{
				$row['avatar'] = get_absolute_path(Config::get('dir/avatar'))."/0.png";
			}
			else
			{
				$row['avatar'] = filename_add_suffix('_thumb', get_absolute_path($row['avatar']));
			}
			
			$row['fullname'] = "{$row['lastname']} {$row['firstname']}";

			if($row['id'] == \Model\User::getUID())
				$row['btn_delete_class'] = 'invisible';
			
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
		$form_attr = ($id != \Model\User::getUID()) ? [] : ['data-onvalidate' => "userBadgeUpdate()"];
		
		$form = new \Component\Form("", $form_attr);
		$form->linkController($this, $id);
		
		$this->loadAssetsJs(['func.js']);
		
		// force clear password
		$form->initValue('password', "");
		
		// form
		$form->addFileImage('avatar')->createThumbnail();
		
		$priority = Session::get('auth.group_priority');
		$form->addSelectSql('xcore_group_id', 'group', true, "", [], 'id', "CONCAT(name,' (#',id,')')", '', " and priority >= ".$priority);
		
		$options = [];
		foreach(Config::get('backend/langs') as $l)
			$options[] = ['value' => $l[0], 'label' => $l[1]];
		
		$form->addSelect('language', '', true, $options);
		$form->addText('lastname', '', true, ['class' => "ucfirst"]);
		$form->addText('firstname', '', true, ['class' => "ucfirst"]);
		$form->addEmail('email', '', true);
		
		$form->addHeader('information');
		$form->addText('company', '', false, ['class' => "ucfirst"])->datalist();
		$form->addText('address', '', false, ['class' => "ucfirst"]);
		$form->addText('address2', 'address 2', false, ['class' => "ucfirst"]);
		$form->addText('address3', 'address 3', false, ['class' => "ucfirst"]);
		$form->addText('zip_code', '', false, ['class' => "text-center"])->setInputSize(2);
		$form->addText('city', '', false, ['class' => "ucfirst"])->datalist();
		$form->addText('country', '', false, ['class' => "upper"])->datalist();
		
		$form->addTel('phone', '', false)->setInputSize(3);
		$form->addTel('mobile', '', false)->setIconBefore('bi bi-phone')->setInputSize(3);
		$form->addDate('birthdate', '', false)->setInputSize(3);
		
		$form->addText('service', '', false, ['class' => "upper"])->datalist();
		$form->addText('job', '', false, ['class' => "upper"])->datalist();
		
		
		$form->addHeader('credentials');
		$form->addText('login', '', true, ['class' => 'col-2'])->setIconBefore('bi bi-person')->setInputSize(3);
		$form->addText('password', '', !$id, ['class' => 'col-2'])->setIconBefore('bi bi-lock')
																  ->setAfter(\Core\Html::Button('btn_password_generate', "<i18n>Generate</i18n>", "button", ['onclick' => "generatePassword('#password')"]))
																  ->setInputSize(4); # force in add mode

		$form->addHr();
		$form->addTextarea('bio');

		$form->addHr();
		$form->addTextarea('note');

		$form->addHr();
		$form->addTagManager();
		$form->addHr();
		$form->addSwitch('active', '', true)->setValue('yes');

		
		// validation
		if($form->isSubmitted())
		{
			// check group priority
			$xcore_group_id = (int)$form->validator->inputGet('xcore_group_id', false);
			$group_selected = \Model\Group::findById($xcore_group_id);
			if($group_selected && $group_selected['priority'] < Session::get('auth.group_priority'))
			{
				$form->addError('xcore_group_id', "`priority`: group must be sub");
			}
			
			// email
			$email = $form->validator->inputGet('email');
			if(!empty($email))
			{
				if(\Model\User::found("email = :email", [':email' => $email], $form->id))
					$form->addError('email', "`email`: already exists");
			}
			
			// login
			$login = $form->validator->inputGet('login');
			$form->validator->input('login')->minLength(Config::get('auth/security/login/min_length'))
											->maxLength(Config::get('auth/security/login/max_length'));
			
			if(!empty($login))
			{
				if(!preg_match(Config::get('auth/security/login/regex'), $login))
				{
					$form->addError('login', Config::get('auth/security/login/regex_error_message'), Config::get('auth/security/login/regex_error_message_added'));
				}
				else
				{
					if(\Model\User::found("login = :login", [':login' => $login], $form->id))
					{
						$form->addError('login', "`login`: already exists");
					}
					
				}
			}
			
			// password
			$passw = $form->validator->inputGet('password');
			if(!empty($passw))
			{
				$form->validator->input('password')->minLength(Config::get('auth/security/password/min_length'))
												   ->maxLength(Config::get('auth/security/password/max_length'));
				
				if(!preg_match(Config::get('auth/security/password/regex'), $passw))
					$form->addError('password', Config::get('auth/security/password/regex_error_message'), Config::get('auth/security/password/regex_error_message_added'));
			}
			
			// valid
			if($form->isValid())
			{
				$password = $form->inputGet('password');
				
				$exceptions = [];
				if($form->is_editing && empty($password))
					$exceptions[] = 'password';
				
				if(!empty($password))
				{
					$password = password_hash($password, \Core\Config::get('auth/security/password/algo'), \Core\Config::get('auth/security/password/algo_options'));
					$form->inputSet('password', $password);
				}
				
				$form->save($exceptions);
			}
			
			return $form->json();
		}
		
		
		return $form->render();
		
	}
	
	
	
	
	public function onDeleteBefore():void
	{
		if(\Model\User::getUID() == $this->id)
		{
			$this->validator->addError("You can't delete yourself");
		}
		
	}
	
	


}