<?php

namespace models\index;
use z\basic\admin as admin;
use z\core\Router as router;
use z\core\Session as session;

class index
{
	public function index()
	{
		$data = array();
		$data = admin::R();
		return $data;
	}
	public function login()
	{
		$enterData = array(
			'e' => 'admin',
			'm' => 'index',
			'c' => 'index',
			'a' => 'enter'
		);
		$data = array(
			'enterUrl'	=> router::create($enterData),
			'token'		=> session::getToken()
		);
		return $data;
	}
}
