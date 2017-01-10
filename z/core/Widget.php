<?php

namespace z\core;

class Widget
{
	// 小部件数据目录
	private static $widgetM;
	// 小部件数据目录
	private static $widgetV;
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct($name)
	{
		$widgetfile = APP_PATH . ($name ? $name : 'widgets') . Z_DS;
		self::$widgetM = $widgetfile . 'models' . Z_DS;
		self::$widgetV = $widgetfile . 'views' . Z_DS;
	}
	
	// 单例方法，初始化对象
	public static function init($name = null)
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c($name);
		}
		return self::$_instance;
	}
	
	// 阻止用户复制对象实例
	public function __clone()
	{
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	// 输出
	public static function output($name)
	{
		$fileNameM = self::$widgetM . $name . '.php';
		$fileNameV = self::$widgetV . $name . '.php';
		if(is_file($fileNameM))
		{
			$widgetData[$name] = require $fileNameM;
		}
		if(is_file($fileNameV))
		{
			require $fileNameV;
		}
	}
	
}
