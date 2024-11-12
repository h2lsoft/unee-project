<?php

namespace Plugin\Core;

use Core\JsonResponse;
use Core\Response;


class AuthController extends \Core\Controller
{

	/**
	 * @route /@backend/login/ {name: "backend-login"}
	 */
	public function loginRender():Response
	{
		$part = App()->is_backend ? 'backend' : 'frontend';
		$key = \Core\Config::get("auth/{$part}/key");
		
		if(\Model\User::isLogon())
		{
			if($part == 'backend')
				$url = "/".\Core\Config::get('backend/dirname')."/";
			else
				$url = "/";
			
			http_redirect($url);
		}
		
		$data = [];
		$data['u_type'] =  ($key == 'email') ? 'email' : 'text';
		$data['u_key'] = $key;
		$data['u_placeholder'] = ($key == 'login') ? 'Username' : 'Email';
		$data['password_route'] = ($part == 'backend') ? "backend-password" : "frontend-password";
		
		return View('login', $data);
	}

	/**
	 * @route /@backend/login/ {method: "POST"}
	 * @event auth.user.login.error
	 * @event auth.user.login.success
	 */
	public function login():JsonResponse
	{
		$part = App()->is_backend ? 'backend' : 'frontend';
		$key = \Core\Config::get("auth/{$part}/key");
		
		$this->validator->input($key)->required();
		
		if($key == 'email')
		{
			$this->validator->input($key)->email();
			$this->validator->input($key)->maxLength(\Core\Config::get("auth/security/email/max_length"));
		}
		
		if($key == 'login')
		{
			$error_message = \Core\Config::get("auth/security/login/regex_error_message");
			$this->validator->input($key)->regex(\Core\Config::get("auth/security/login/regex"), $error_message);
		}
		
		// password
		$error_message = \Core\Config::get("auth/security/pasword/regex_error_message");
		$error_message = str_replace('[CHAR_MIN]', \Core\Config::get("auth/security/pasword/min_length"), $error_message);
		$error_message = str_replace('[CHAR_MAX]', \Core\Config::get("auth/security/pasword/max_length"), $error_message);
		
		$this->validator->input('password')->required()
											->minLength(\Core\Config::get("auth/security/password/min_length"))
											->maxLength(\Core\Config::get("auth/security/password/max_length"))
											->regex(\Core\Config::get("auth/security/password/regex"), $error_message);
		
		
		// no error
		if($this->validator->success())
		{
			if(!($user_id = \Model\User::exists($this->validator->inputGet($key), $this->validator->inputGet('password'))))
				$this->validator->input($key)->addError("User not found");
				
		}
		
		$response = $this->validator->result();
		
		if($this->validator->fails())
		{
			\Core\EventManager::emit("auth.user.login.error");
			sleep(\Core\Config::get('auth/logon_sleep_seconds'));
		}
		else
		{
			\Model\User::logon($user_id);
			\Core\EventManager::emit("auth.user.login.success", [$user_id]);
			$response['url_success'] = "/".\Core\Config::get("backend/dirname")."/";
		}
		

		
		
		return new JsonResponse($response);
	}

	/**
	 * @route /@backend/password/ {name: "backend-password"}
	 */
	public function passwordRender():Response
	{
		$part = App()->is_backend ? 'backend' : 'frontend';
		
		$data = [];
		$data['login_route'] = ($part == 'backend') ? "backend-login" : "frontend-login";
		$data['password_route'] = ($part == 'backend') ? "backend-password" : "frontend-password";
		
		return View('password', $data);
	}

	/**
	 * @route /@backend/password/ {method: "POST"}
	 * @event auth.user.password_reset.success
	 */
	public function password():JsonResponse
	{
		$part = App()->is_backend ? 'backend' : 'frontend';
		
		$this->validator->input('email')->required()->email();
		$response = $this->validator->result();
		
		$email = strtolower(post('email'));
		
		if($this->validator->success())
		{
			if(!($user = \Model\User::findOne("email = :email and active = 'yes'", [':email' => $email], '*')))
			{
				$this->validator->input('email')->addError("User not found");
			}
			else
			{
				$token = generateToken();
				
				// update user
				$token_expiration = \Core\Config::get('auth/password_reset_token_expiration_hours');
				\Model\User::update(['password_token' => $token, 'password_token_date' => now(), 'password_token_expiration_date' => now("+{$token_expiration} hour")], $user['id']);
				
				$link = \Core\Config::get('url');
				if($part == 'backend')
					$link .= "/".\Core\Config::get('backend/dirname');
				$link .= "/password-update/";
				$link .= "?token={$token}&email={$email}";
				
				$vars = [];
				$vars['LINK'] = $link;

				\Core\EventManager::emit("auth.user.password_reset.success", ['email' => $email, 'token' => $token, 'part' => $part]);
				\Core\Mailer::sendTemplate('password-reset', $email, $vars);
				\Core\Log::write('password-email', $email, [], 0, 'info', 'auth');
				
			}
		}
		
		$response = $this->validator->result();
		sleep(\Core\Config::get('auth/sleep_seconds'));
		
		return new JsonResponse($response);
	}

	/**
	 * @route /@backend/password-update/ {name: "backend-password-update"}
	 */
	public function passwordUpdateRender():Response
	{
		$part = App()->is_backend ? 'backend' : 'frontend';
		
		$data = [];
		$data['login_route'] = ($part == 'backend') ? "backend-login" : "frontend-login";
		
		return View('password-update', $data);
	}

	/**
	 * @route /@backend/password-update/ {method: "POST"}
	 * @event auth.user.password_update.success
	 */
	public function passwordUpdate():JsonResponse
	{
		// password
		$error_message = \Core\Config::get("auth/security/pasword/regex_error_message");
		$error_message = str_replace('[CHAR_MIN]', \Core\Config::get("auth/security/pasword/min_length"), $error_message);
		$error_message = str_replace('[CHAR_MAX]', \Core\Config::get("auth/security/pasword/max_length"), $error_message);
		
		$this->validator->input('password')->required()
						->minLength(\Core\Config::get("auth/security/password/min_length"))
						->maxLength(\Core\Config::get("auth/security/password/max_length"))
						->regex(\Core\Config::get("auth/security/password/regex"), $error_message);
		
		$this->validator->input('password2')->required()->sameAs('password', "passwords must be the same");
		
		$token = get('token');
		$email = get('email');
		
		$this->validator->inputSet('token', $token);
		$this->validator->inputSet('email', $email);
		
		
		$this->validator->input('token')->required();
		$this->validator->input('email')->required()->email();
		
		if($this->validator->success())
		{
			$user = \Model\User::findOne("email = :email and active = 'yes'", [':email' => $email]);
			if(!$user)
			{
				$this->validator->addError("User email not found");
			}
			elseif($user['password_token'] != $token)
			{
				$this->validator->addError("Token not found");
			}
			elseif($user['password_token_expiration_date'] < now())
			{
				$this->validator->addError("Token has expired");
			}
			else
			{
				$password = $this->validator->inputGet('password');
				$password_hashed = password_hash($password, \Core\Config::get('auth/security/password/algo'), \Core\Config::get('auth/security/password/algo_options'));
				
				
				// update password
				$f = [];
				$f['password_token'] = '';
				$f['password_token_date'] = '';
				$f['password_token_expiration_date'] = '';
				$f['password'] = $password_hashed;
				
				\Model\User::update($f, $user['id']);
				
				// log
				$info = [];
				$info['user_id'] = $user['id'];
				$info['email'] = $user['email'];
				\Core\Log::write('password-update', "", $info, 0, 'info', 'auth');

				\Core\EventManager::emit("auth.user.password_update.success", ['id' => $user['id']]);

			}
		}
		
		
		$response = $this->validator->result();
		sleep(\Core\Config::get('auth/sleep_seconds'));
		
		return new JsonResponse($response);
	}

	/**
	 * @route /@backend/logout/
	 * @event auth.user.logout
	 */
	public function logout():void
	{
		\Core\EventManager::emit("auth.user.logout", ['id' => \Model\User::getUID()]);
		\Model\User::logout();
		http_redirect("/".\Core\Config::get('backend/dirname')."/login/");
	}

	/**
	 * @route /@backend/logon-checker/
	 */
	public function logonChecker():JsonResponse
	{
		$resp = ['logon' => false];
		
		// check if user is still active before
		if(\Core\Session::get(\Core\Config::get('auth/key_name'), false))
		{
			$where = "id = :id AND active = 'yes'";
			$found = \Model\User::findOne($where, [':id' => \Model\User::getUID()]);
			if(!$found)
			{
				\Core\Session::set(\Core\Config::get('auth/key_name'), false);
				$resp['logon'] = false;
			}
			else
			{
				$resp['logon'] = true;
			}
		}
		
		return new JsonResponse($resp);
	}
	
	
}

