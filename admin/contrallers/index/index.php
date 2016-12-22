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
		return self::render('login', array(
			'enterUrl'	=> router::create($enterData),
			'token'		=> session::getToken()
		));
	}
}
