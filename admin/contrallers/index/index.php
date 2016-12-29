<?php

namespace contrallers\index;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;

use z\basic\admin as admin;

class index extends \admin\adminContraller
{
	public function index()
	{
		self::chkLogin();
	}
	
	public function login()
	{
		//echo \z\core\Medoo::setTable('admin')->count(array('account' => 'admin', 'password' => md5('admin')));
		echo \z\core\Medoo::setTable('admin')->get('id', array('account' => 'admin', 'password' => md5('admin')));
		exit;
		$idxUrlDate = array(
			'e' => 'admin',
			'm' => 'index',
			'c' => 'index',
			'a' => 'index'
		);
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			$model = $_POST['form'];
			// 验证令牌
			if(validate::is('token'))
			{
				// 这里继续验证表单元素的合法性
				if(!self::gets('account') || !admin::hasAccount(self::gets('account')))
				{
					$model['message'] = '用户名不存在!';
				}
				elseif(!self::gets('password') || !admin::chkPassword(self::gets('account'), self::gets('password')))
				{
					$model['message'] = '密码不正确';
				}
				else
				{
					// 保存登录状态
					session::set('loginStatus', true);
					header('Location: ' . router::create($idxUrlDate) . PHP_EOL);
					exit(0);
				}
			}
			else
			{
				// 这里是重复提交了
				$model['message'] = '请勿重复提交!';
			}
		}
		else
		{
			// 加载表单初始化数据
			$model['token'] = session::getToken();
		}
		return self::render('login', $model);
	}
}
