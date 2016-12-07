<?php

namespace z\core;

/**
 * 缓存类
 */
class Cache
{
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct(){}
	
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
	
	private function getDomain()
	{
		return substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.'));
	}
}
