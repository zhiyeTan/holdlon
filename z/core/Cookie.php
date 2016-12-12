<?php

namespace z\core;

class Cookie
{
	private static $domain;
	private static $isSsl;
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		self::$domain = Request::cosDomain();
		self::$isSsl = Request::isSsl();
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
	
	// 设置cookie
	public static function set($key, $value)
	{
		setcookie($key, $value, COOKIE_EXPIRE, '/', self::$domain, self::$isSsl);
	}
	
	// 获取cookie值
	public static function get($key)
	{
		if(empty($_COOKIE[$key])) return false;
		return $_COOKIE[$key];
	}
	
	// 删除cookie值
	public static function delete($key)
	{
		setcookie($key, NULL);
	}
}
