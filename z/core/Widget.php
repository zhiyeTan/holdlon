<?php

namespace z\core;

class widget
{
	// 小部件数据目录
	private static $widgetM;
	// 小部件数据目录
	private static $widgetV;
	// 异常类型
	private static $exceptionType = 0;
	// 异常提示
	private static $exceptionMaps = array(
		1 => '此部件的模型不存在！',
		2 => '此部件的视图不存在'
	);
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
	public function output($name)
	{
		if(self::checkWidget($name) === true)
		{
			$widgetData[$name] = require self::$widgetM . $name . '.php';
			require self::$widgetV . $name . '.php';
		}
	}
	
	// 错误处理
	public static function exception()
	{
		echo '<p>' . self::$exceptionMaps[self::$exceptionType] . '</p>';
	}
	
	// 检查小部件对应模型/视图
	public static function checkWidget($name)
	{
		$fileNameM = self::$widgetM . $name . '.php';
		$fileNameV = self::$widgetV . $name . '.php';
		if(!is_file($fileNameM)) self::$exceptionType = 1;
		elseif(!is_file($fileNameV)) self::$exceptionType = 2;
		if(self::$exceptionType) return self::exception();
		else return true;
	}
}
