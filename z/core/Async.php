<?php

namespace z\core;

class Async
{
	// 操作
	private static $eventMap = array();
	// 操作所需的参数
	private static $argsMap = array();
	
	// 构造函数声明为private, 不允许创建对象
	private function __construct(){}
	
	/**
	 * 注册操作
	 * @param string $object 对象
	 * @param string $methodName 方法名
	 * @param string $methodAlias 方法别名
	 * @param array  $args 传入的参数
	 */
	public static function on(&$object, $methodName, $methodAlias, $args = null)
	{
		self::$eventMap[$methodAlias] = array($object, $methodName);
		self::$argsMap[$methodAlias] = $args;
	}
	
	// 触发所有操作
	public static function call()
	{
		foreach(self::$eventMap as $k => $v)
		{
			call_user_func_array($v, self::$argsMap[$k]);
		}
	}
	
	// 触发异步操作
	public static function trigger()
	{
		
	}
}
