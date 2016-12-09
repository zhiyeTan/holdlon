<?php

namespace z\core;

class Session
{
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		// 如果在php.ini中设置了自动开启session，则临时关闭它
		ini_set('session.auto_start', 0);
		// 设置session的路径、作用域及有效时间
		ini_set('session.cookie_path', '/');
		ini_set('session.cookie_domain', Request::cosDomain());
		ini_set('session.cookie_lifetime', SESSION_EXPIRE);
		// 开启session
		session_start();
	}
	
	// 单例方法，初始化对象
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	// 阻止用户复制对象实例
	public function __clone()
	{
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	// 设置session
	public static function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}
	
	// 获得session值
	public static function get($key)
	{
		if(empty($_SESSION[$key])) return false;
		return $_SESSION[$key];
	}
	
	// 删除session
	public static function delete($key)
	{
		unset($_SESSION[$key]);
	}
	
	// 销毁session
	public static function clean()
	{
		$_SESSION = array();
		// 如果使用基于Cookie的session，则删除包含Session ID的cookie
		if(isset($_COOKIE[session_name()]))
		{
			Cookie::delete(session_name());
		}
		session_destroy();
	}
	
}
