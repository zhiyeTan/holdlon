<?php

namespace admin;

use z\core\Session as Session;
use z\core\Router as Router;

class adminContraller extends \z\core\Contraller
{
	protected static function chkLogin()
	{
		// 登录状态
		$status = Session::get('loginStatus');
		// 若不是登录状态或cookie未保存有session_name
		if(!!$status || !isset($_COOKIE[session_name()]))
		{
			$urldata = array(
				'e' => $_GET['e'],
				'm' => 'index',
				'c' => 'index',
				'a' => 'login',
			);
			$url = Router::create($urldata);
			header('Location: ' . $url . PHP_EOL);
		}
	}
}
