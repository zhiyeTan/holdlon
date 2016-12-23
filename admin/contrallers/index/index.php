<?php

namespace contrallers\index;

use z\core\Router as router;
use z\core\Session as session;

class index extends \admin\adminContraller
{
	public function index()
	{
		self::chkLogin();
	}
	
	public function login()
	{
		$enterData = array(
			'e' => 'admin',
			'm' => 'index',
			'c' => 'index',
			'a' => 'enter'
		);
		$model = array();
		if(isset($_POST['form']))
		{
			
			//$model = 
		}
		$model = array_merge($model, array(
			'enterUrl'	=> router::create($enterData),
			'token'		=> session::getToken()
		));
		return self::render('login', $model);
	}
}
