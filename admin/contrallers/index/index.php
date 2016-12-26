<?php

namespace contrallers\index;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;

class index extends \admin\adminContraller
{
	public function index()
	{
		self::chkLogin();
	}
	
	public function login()
	{
		phpinfo();
		exit;
		$enterData = array(
			'e' => 'admin',
			'm' => 'index',
			'c' => 'index',
			'a' => 'enter'
		);
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			// 验证令牌
			if(validate::is('token'))
			{
				// 这里处理表单
				
			}
			else
			{
				// 这里是重复提交了
			}
		}
		else
		{
			// 加载表单初始化数据
		}
		$model = array_merge($model, array(
			'enterUrl'	=> router::create($enterData),
			'token'		=> session::getToken()
		));
		return self::render('login', $model);
	}
}
