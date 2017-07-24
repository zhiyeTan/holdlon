<?php

namespace z;

/**
 * 自动加载类文件
 * 
 * 与命名空间配合实现自动加载类名对应的文件
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Loader
{
	/**
	 * 数据库配置
	 */ 
	public static $dbconfig;
	/**
	 * 数据库配置的键名
	 */
	public static $dbcfgkey = array('server', 'username', 'password', 'dbname', 'charset', 'port', 'prefix');
	
	/**
	 * 设置基础常量以及数据库配置和时区
	 */
	public static function setup()
	{
		// 初始时间和内存
		define('Z_BEGIN_TIME', microtime(true));
		define('Z_BEGIN_MEM', memory_get_usage());
		
		// 加载配置
		$main = require UNIFIED_PATH . 'z' . Z_DS . 'config' . Z_DS . 'config.php';
		$local = require UNIFIED_PATH . 'z' . Z_DS . 'config' . Z_DS . '/config_local.php';
		$CFG = array_merge($main, $local);
		
		// 调试模式
		define('Z_DEBUG', $CFG['app_debug']);
		// 异步处理主机名
		define('ASYNC_DOMAIN', $CFG['async_domain']);
		// 异步处理端口
		define('ASYNC_PORT', $CFG['async_port']);
		// 数据接口入口名
		define('API_ENTRY', $CFG['api_entry']);
		// 数据接口入口对应的应用目录名
		define('API_DIR', $CFG['api_dir']);
		// 服务器数据缓存有效时间
		define('DATA_CACHE_EXPIRE', (int) $CFG['data_cache_expire']);
		// 服务器静态缓存有效时间
		define('STATIC_CACHE_EXPIRE', (int) $CFG['static_cache_expire']);
		// 本地缓存有效时间
		define('LOCAL_EXPIRE', (int) $CFG['local_expire']);
		// 路由设置
		define('ROUTE_PATTERN', $CFG['route_pattern']);
		// 站点默认语言
		define('DEFAULT_LANG', $CFG['default_lang']);
		// 入口与位置地图
		define('ENTRY_MAPS', serialize($CFG['entry_maps']));
		// session有效时间
		define('SESSION_EXPIRE', $CFG['session_expire']);
		// cookie有效时间
		define('COOKIE_EXPIRE', $CFG['cookie_expire']);
		
		// 定义MVC错误类型常量
		define('INVALID_ENTRY', 1);        // 无效的入口
		define('INVALID_MODULE', 2);       // 无效的模块
		define('INVALID_CONTRALLER', 3);   // 无效的控制器
		define('INVALID_ACTION', 4);       // 无效的操作
		
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
		if (Z_DEBUG && !class_exists($className, false) && !interface_exists($className, false))
		{
			die("Unable to find '$className' in file: $classFile. Namespace missing?");
		}
	}
	
}
