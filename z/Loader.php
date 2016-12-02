<?php

namespace z;

class Loader
{	
	// 数据库配置
	public static $dbconfig = array();
	
	// 数据库配置的键名
	public static $dbcfgkey = array('database_type', 'database_name', 'server', 'username', 'password', 'charset', 'port', 'prefix', 'option');
	
	// 基本设置
	public static function setup()
	{
		// 初始时间和内存
		define('Z_BEGIN_TIME', microtime(true));
		define('Z_BEGIN_MEM', memory_get_usage());
		// 路径
		define('Z_PATH', __DIR__);
		define('LOAD_PATH', dirname(Z_PATH) . Z_DS);
		
		$main = require Z_PATH . Z_DS . 'config' . Z_DS . 'config.php';
		$local = require Z_PATH . Z_DS . 'config' . Z_DS . '/config_local.php';
		$CFG = array_merge($main, $local);
		
		// 调试模式
		define('Z_DEBUG', $CFG['app_debug']);
		// 多点部署
		define('MULTIPOINT_ENABLE', $CFG['multipoint_enable']);
		// 静态目录
		define('STATIC_PATH', $CFG['static_path']);
		// 静态主机名
		define('STATIC_DOMAIN', $CFG['static_domain']);
		// 服务器缓存有效时间
		define('CACHE_EXPIRE', (int) $CFG['cache_expire']);
		// 本地缓存有效时间
		define('LOCAL_EXPIRE', (int) $CFG['local_expire']);
		// 路由设置
		define('ROUTE_PATTERN', $CFG['route_pattern']);
		// 站点默认语言
		define('DEFAULT_LANG', $CFG['default_lang']);
		// 入口与位置地图
		define('ENTRY_MAPS', serialize($CFG['entry_maps']));
		
		// 定义MVC错误类型常量
		defined('ENTRYNOTFOUND')        || define('ENTRYNOTFOUND', 1);        // 入口不存在
		defined('M_MODULENOTFOUND')     || define('M_MODULENOTFOUND', 2);     // M模块不存在
		defined('V_MODULENOTFOUND')     || define('V_MODULENOTFOUND', 3);     // V模块不存在
		defined('C_MODULENOTFOUND')     || define('C_MODULENOTFOUND', 4);     // C模块不存在
		defined('M_CONTRALLERNOTFOUND') || define('M_CONTRALLERNOTFOUND', 5); // M控制器不存在
		defined('V_GROUPNOTFOUND')      || define('V_GROUPNOTFOUND', 6);      // V模块子分组不存在
		defined('C_CONTRALLERNOTFOUND') || define('C_CONTRALLERNOTFOUND', 7); // C控制器不存在
		defined('M_ACTIONNOTFOUND')     || define('M_ACTIONNOTFOUND', 8);     // M操作不存在
		defined('V_TEMPLATENOTFOUND')   || define('V_TEMPLATENOTFOUND', 9);   // V模板不存在
		defined('C_ACTIONNOTFOUND')     || define('C_ACTIONNOTFOUND', 10);     // C操作不存在
		
		// 数据库配置项
		foreach(self::$dbcfgkey as $v)
		{
			if(isset($CFG[$v]) && !empty($CFG[$v]))
			{
				self::$dbconfig[$v] = $CFG[$v];
			}
		}
	}
	
	// 自动加载
	public static function autoload($className)
	{
		// 检查类名是否包含"\"
		if(strpos($className, '\\') !== false)
		{
			if(strpos($className, 'z') === false)
			{
				$classFile = APP_PATH . str_replace('\\', Z_DS, $className) . '.php';
			}
			else
			{
				$classFile = LOAD_PATH . str_replace('\\', Z_DS, $className) . '.php';
			}
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
		if (Z_DEBUG && !class_exists($className, false) && !interface_exists($className, false))
		{
			throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}
	
}
