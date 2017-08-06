<?php
/**
 * 框架引导机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class z
{
	public static $configure = array();
	
	/**
	 * 自动加载类名文件
	 * 结合命名空间使用
	 */
	public static function autoload($className)
	{
		// 检查类名是否包含"\"
		if(strpos($className, '\\') !== false)
		{
			$classFile = (strpos($className, 'z') === false ? APP_PATH : UNIFIED_PATH) . strtr($className, array('\\'=>Z_DS)) . '.php';
			if(!is_file($classFile))
			{
				return;
			}
		}
		// 类名不合法
		else
		{
			return;
		}
		include($classFile);
		if (z::$configure['app_debug'] && !class_exists($className, false) && !interface_exists($className, false))
		{
			die("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}
}

// 加载全局基本配置
z::$configure = require UNIFIED_PATH . 'z' . Z_DS . 'config.php';
// 若存在本地全局配置，则覆盖对应选项
$localConfigPath = UNIFIED_PATH . 'z' . Z_DS . '/config_local.php';
if(is_file($localConfigPath))
{
	z::$configure = array_merge(z::$configure, require $localConfigPath);
}

// 设置时区
date_default_timezone_set(z::$configure['default_timezone']);

// 使用自定义的类加载机制
spl_autoload_register(array('z', 'autoload'), true, true);

// 初始化并运行应用
$app = new z\core\app();

$app->run();