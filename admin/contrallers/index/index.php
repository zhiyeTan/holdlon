<?php

namespace contrallers\index;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;

use z\basic\admin as admin;

class index extends \admin\adminContraller
{
	// 后台首页
	public function index()
	{
		return self::render('index');
	}
	
	// 后台登录
	public function login()
	{
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			$admin = admin::init();
			$model = $_POST['form'];
			// 验证令牌
			$account = self::gets('account');
			if(validate::is('token'))
			{
				// 这里继续验证表单元素的合法性
				if(!$account || !$admin->hasAccount($account))
				{
					$model['message'] = '用户名不存在!';
				}
				elseif(!self::gets('password') || !$admin->chkPassword($account, self::gets('password')))
				{
					$model['message'] = '密码不正确';
				}
				else
				{
					// 保存登录状态
					session::set('loginStatus', true);
					session::set('account', $account);
					$idxUrlData = array(
						'e' => $_GET['e'],
						'm' => 'index',
						'c' => 'index',
						'a' => 'index'
					);
					zHeader(router::create($idxUrlData));
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
	
	// 退出登录
	public function logout()
	{
		session::delete(array('loginStatus', 'account'));
		$idxUrlData = array(
			'e' => $_GET['e'],
			'm' => 'index',
			'c' => 'index',
			'a' => 'login'
		);
		zHeader(router::create($idxUrlData));
	}
}
