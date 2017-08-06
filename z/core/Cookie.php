<?php

namespace z\core;

use z;

/**
 * cookie管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class cookie
{
	private static $domain;
	private static $isSsl;
	private static $_instance;
	
	// 禁止直接创建对象
	private function __construct()
	{
		self::$domain = request::cosDomain();
		self::$isSsl = request::isSsl();
	}
	
	/**
	 * 单例构造方法
	 * @access public
	 * @return this
	 */
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * 禁止用户复制对象实例
	 */
	public function __clone()
	{
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	/**
	 * 设置cookie
	 * @access public
	 * @param  string  $key    键名
	 * @param  string  $value  键值
	 * @return boolean
	 */
	public static function set($key, $value)
	{
		return setcookie($key, $value, z::$configure['cookie_expire'], '/', self::$domain, self::$isSsl);
	}
	
	/**
	 * 获取cookie值
	 * @access public
	 * @param  string  $key    键名
	 * @return value/boolean
	 */
	public static function get($key)
	{
		if(empty($_COOKIE[$key]))
		{
			return false;
		}
		return $_COOKIE[$key];
	}
	
	/**
	 * 删除cookie值
	 * @access public
	 * @param  string  $key    键名
	 * @return boolean
	 */
	public static function delete($key)
	{
		return setcookie($key, NULL);
	}
}
