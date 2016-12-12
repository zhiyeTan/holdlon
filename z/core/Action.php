<?php

namespace z\core;

class Action
{
	// 操作
	private static $eventMap = array();
	// 操作所需的参数
	private static $argsMap = array();
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
	
	/**
	 * 注册操作
	 * @param string $object 对象
	 * @param string $methodName 方法名
	 * @param string $methodAlias 方法别名
	 * @param array  $args 传入的参数
	 */
	public function on(&$object, $methodName, $methodAlias, $args = null)
	{
		self::$eventMap[$methodAlias] = array($object, $methodName);
		self::$argsMap[$methodAlias] = $args;
	}
	
	// 触发异步操作
	public static function trigger()
	{
		
	}
	
	/**
	 * 触发器
	 * @param string $methodName 驱动的方法名
	 * @param array $args 方法参数
	 */
	public static function call($methodName, $args = null)
	{
		call_user_func_array($this->eventMap[$methodName] , $args);
	}
}
