<?php

namespace z\core;

/**
 * Session管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Session
{
	// 保存实例在此属性中
	private static $_instance;
	
	// 禁止直接创建对象
	private function __construct()
	{
		// 配置session
		ini_set('session.auto_start', 0);
		ini_set('session.cache_expire', SESSION_EXPIRE);
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_cookies', 1);
		// 开启session
		session_start();
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
	 * 设置session
	 * @access public
	 * @param  string  $key    键名
	 * @param  mixed   $value  键值
	 */
	public static function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}
	
	/**
	 * 获得session值
	 * @access public
	 * @param  string  $key  键名
	 * @return mixed/boolean
	 */
	public static function get($key)
	{
		if(empty($_SESSION[$key]))
		{
			return false;
		}
		return $_SESSION[$key];
	}
	
	/**
	 * 设置并返回安全令牌
	 * @access public
	 * @return string
	 */
	public static function getToken()
	{
		$token = self::get('__token__');
		if($token)
		{
			return $token;
		}
		$token = md5(session_name() . time());
		self::set('__token__', $token);
		return $token;
	}
	
	/**
	 * 删除session
	 * @access public
	 * @param  string  $key  键名
	 */
	public static function delete($key)
	{
		if(is_array($key))
		{
			foreach($key as $v)
			{
				unset($_SESSION[$v]);
			}
		}
		else
		{
			unset($_SESSION[$key]);
		}
	}
	
	/**
	 * 销毁session
	 * @access public
	 */
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
