<?php

namespace z\core;

use z;

class Contraller
{
	protected static $db;
	public function __construct()
	{
		self::$db = Model::init(z::$dbconfig);
	}
	
	
	// 渲染404页面
	public static function render404($mixed)
	{
		// return render($template404, $mixed); // 可渲染指定的404页面
		return '<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . $mixed . '</p>';
	}
	
	// 渲染401页面
	public static function render401($mixed)
	{
		// return render($template404, $mixed); // 可渲染指定的401页面
		return '<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . $mixed . '</p>';
	}
	
	// 渲染指定页面
	public static function render($templateName, $data = null)
	{
		$templatePath = APP_PATH . 'views' . Z_DS . $_GET['m'] . Z_DS . $templateName . '.php';
		// 打开缓冲区
		ob_start();
		// 载入模板
		require $templatePath;
		// 返回缓冲内容并清空
		return ob_get_clean();
	}
}
