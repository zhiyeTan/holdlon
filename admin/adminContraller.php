<?php

namespace admin;

use z\core\Session as Session;
use z\core\Router as Router;

class adminContraller extends \z\core\Contraller
{
	// 统一对非登录页进行登录和目录权限检查
	public function __construct()
	{
		if($_GET['a'] != 'login')
		{
			self::chkLogin();
			self::chkPermission();
		}
	}
	// 检查登录状态
	protected static function chkLogin()
	{
		// 登录状态
		$status = Session::get('loginStatus');
		// 若不是登录状态或cookie未保存有session_name
		if(!$status)
		{
			$urldata = array(
				'e' => $_GET['e'],
				'm' => 'index',
				'c' => 'index',
				'a' => 'login',
			);
			$url = Router::create($urldata);
			zHeader($url);
			exit(0);
		}
	}
	
	// 检查访问权限
	protected static function chkPermission($getMap = 0)
	{
		$maps = require(APP_PATH . 'adminMaps.php');
		// 优先处理可访问地图的获取
		if($getMap)
		{
			// TODO 这里遍历地图做权限校验
			return $maps;
		}
		// TODO 对当前访问的栏目的权限进行判断，若没有权限则终止访问，跳转回首页
	}
	
	// 获取表单元素
	protected static function gets($key)
	{
		return isset($_POST['form'][$key]) ? $_POST['form'][$key] : !1;
	}
	
	// 获取二级栏目名称
	protected static function getColName()
	{
		$name = '';
		$maps = require(APP_PATH . 'adminMaps.php');
		foreach($maps as $k => $v)
		{
			if($v['module'] == $_GET['m'])
			{
				foreach($v['list'] as $kk => $vv)
				{
					if($_GET['a'] == $kk)
					{
						$name = $vv['name'];
					}
				}
			}
		}
		return $name;
	}
	
	// 检查登录以及权限
	protected static function chkAll()
	{
		self::chkLogin();
		self::chkPermission();
	}
}
