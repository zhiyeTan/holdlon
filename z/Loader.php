<?php

namespace z;

class Loader
{
	// 类名映射
	public static $classMap = array();	
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
		// 异步处理主机名
		define('ASYNC_DOMAIN', $CFG['async_domain']);
		// 异步处理端口
		define('ASYNC_PORT', $CFG['async_port']);
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
		// 不使用缓存的入口英文
		define('NO_CACHE_ENTRY', serialize($CFG['no_cache_entry']));
		// session有效时间
		define('SESSION_EXPIRE', $CFG['session_expire']);
		// cookie有效时间
		define('COOKIE_EXPIRE', $CFG['cookie_expire']);
		
		// 定义MVC错误类型常量
		define('ENTRYNOTFOUND', 1);        // 入口不存在
		define('M_MODULENOTFOUND', 2);     // M模块不存在
		define('V_MODULENOTFOUND', 3);     // V模块不存在
		define('C_MODULENOTFOUND', 4);     // C模块不存在
		define('M_CONTRALLERNOTFOUND', 5); // M控制器不存在
		define('V_GROUPNOTFOUND', 6);      // V模块子分组不存在
		define('C_CONTRALLERNOTFOUND', 7); // C控制器不存在
		define('M_ACTIONNOTFOUND', 8);     // M操作不存在
		define('V_TEMPLATENOTFOUND', 9);   // V模板不存在
		define('C_ACTIONNOTFOUND', 10);     // C操作不存在
		
		// 数据库配置项
		foreach(self::$dbcfgkey as $v)
		{
			if(isset($CFG[$v]) && !empty($CFG[$v]))
			{
				self::$dbconfig[$v] = $CFG[$v];
			}
		}
		
		// 设置时区
		date_default_timezone_set($CFG['default_timezone']);
	}
	
	// 自动加载
	public static function autoload($className)
	{
		// 检查是否存在类名地图中
		if(isset(static::$classMap[$className]))
		{
			$classFile = static::$classMap[$className];
		}
		// 检查类名是否包含"\"
		elseif(strpos($className, '\\') !== false)
		{
			$classFile = (strpos($className, 'z') === false ? APP_PATH : LOAD_PATH) . strtr($className, array('\\'=>Z_DS)) . '.php';
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
			die("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}
	
}
