<?php

namespace Plugin\Core;

use Core\Config;
use Symfony\Component\HttpFoundation\JsonResponse;

class My_ProfileController extends \Core\Controller
{
	public string $table = 'xcore_user';

	/**
	 * @route /@backend/my-profile/ {method: "GET|PUT"}
	 */
	public function index()
	{
		$id = \Model\User::getUID();
		
		$form = new \Component\Form("", [
											'data-onvalidate' => "userBadgeUpdate()",
											"data-success-notification" => "ok"
										]);
		
		$form->linkController($this, $id);
		
		$form->setTitle("");
		
		// force clear password
		$form->initValue('password', "");
		
		// form
		$form->addFileImage('avatar', "", !$id)->createThumbnail();
		
		$options = [];
		foreach(Config::get('backend/langs') as $l)
			$options[] = ['value' => $l[0], 'label' => $l[1]];
		
		$form->addSelect('language', '', true, $options);
		$form->addText('lastname', '', true, ['class' => "ucfirst"]);
		$form->addText('firstname', '', true, ['class' => "ucfirst"]);
		$form->addEmail('email', '', true);
		
		$form->addHeader('credentials');
		$form->addText('login', '', true, ['class' => 'col-2'])->setIconBefore('bi bi-person')->setInputSize(3);
		$form->addText('password', '', !$id, ['class' => 'col-2'])->setIconBefore('bi bi-lock')
			 ->setAfter(\Core\Html::Button('btn_password_generate', "<i18n>Generate</i18n>", "button", ['onclick' => "generatePassword('#password')"]))
			 ->setInputSize(4); # force in add mode
		
		
		$form->addHeader('information');
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
		
		
		
		// validation
		if($form->isSubmitted())
		{
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
		
		
		$data = [];
		$data['content'] = $form->render();
		return View('@plugin-content', $data);
		
	}

	/**
	 * @route /@backend/my-info/
	 */
	public function info()
	{
		$resp = [];
		$resp['error'] = false;
		$resp['error_message'] = "";
		
		$data = \Model\User::findOne(\Model\User::getUID(), [], 'id, language, firstname, lastname, avatar');
		
		if($data)
		{
			if(empty($data['avatar']))
				$data['avatar'] = get_absolute_path(\Core\Config::get('dir/avatar')."/0.png");
			
			$resp['data'] = $data;
			
			\Core\Session::set('auth.avatar', $data['avatar']);
			\Core\Session::set('auth.firstname', $data['firstname']);
			\Core\Session::set('auth.lastname', $data['lastname']);
			\Core\Session::set('auth.language', $data['language']);
		}
		
		return die(json_encode($resp));
	}
}