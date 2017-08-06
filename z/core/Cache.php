<?php

namespace z\core;

use z;
use z\lib\core as core;

/**
 * 缓存机制
 * 
 * 静态缓存和动态缓存将保存到当前应用的目录中
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class cache
{
	// 数据接口缓存路径
	private static $api_cache_path;
	// 静态缓存路径
	private static $static_cache_path;
	// 动态缓存路径
	private static $dynamic_cache_path;
	// 数据缓存文件名（含路径）
	private static $api_cache_name;
	// 静态文件名（含路径）
	private static $static_cache_name;
	// 动态文件名（含路径）
	private static $dynamic_cache_name;
	// 作者密钥
	private static $authorKey = 'zhiyeTan';
	// 保存实例在此属性中
	private static $_instance;
	
	// 私有构造函数
	private function __construct()
	{
		$tmpCachePath              = APP_PATH . 'cache' . Z_DS;
		self::$api_cache_path      = dirname(APP_PATH) . Z_DS . z::$configure['api_dir'] . Z_DS . 'cache' . Z_DS . 'static' . Z_DS;
		self::$static_cache_path   = $tmpCachePath . 'static' . Z_DS;
		self::$dynamic_cache_path  = $tmpCachePath . 'dynamic' . Z_DS;
		
		core::chkFolder($tmpCachePath);
		core::chkFolder(self::$api_cache_path);
		core::chkFolder(self::$static_cache_path);
		core::chkFolder(self::$dynamic_cache_path);
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
	 * 设置完整缓存文件名
	 * @access public
	 * @param  array    $array   键值对数组，通常为$_GET
	 * @param  number   $type    缓存类型（0动态，1静态，2数据接口）
	 * @return this
	 */
	public static function setCacheName($array, $type = 1)
	{
		$array['m'] = empty($array['m']) ? 'index' : $array['m'];
		$array['c'] = empty($array['c']) ? 'index' : $array['c'];
		// 根据类型获得对应的缓存路径
		$tmpPath = $type == 2 ? self::$api_cache_path : ($type == 1 ? self::$static_cache_path : self::$dynamic_cache_path);
		// 增加一级以模块命名的文件夹
		$tmpPath .= $array['m'] . Z_DS;
		// 检查文件夹
		core::chkFolder($tmpPath);
		// 由于动态缓存需要require，所以必须为.php后缀，其他则不需要后缀
		$tmpPath .= http_build_query($array) . ($type ? '' : '.php');
		// 根据类型设置缓存名（含路径）
		if($type == 1)
		{
			self::$static_cache_name = $tmpPath;
		}
		elseif($type == 2)
		{
			self::$api_cache_name = $tmpPath;
		}
		else
		{
			self::$dynamic_cache_name = $tmpPath;
		}
		return self::$_instance;
	}
	
	/**
	 * 获取指定缓存类型对应的文件路径
	 * @access private
	 * @param  number    $type   缓存类型（0动态，1静态，2数据接口）
	 * @return path
	 */
	private static function getCachePath($type)
	{
		return $type == 2 ? self::$api_cache_name : ($type == 1 ? self::$static_cache_name : self::$dynamic_cache_name);
	}
	public static function showCachePath($type)
	{
		return $type == 2 ? self::$api_cache_name : ($type == 1 ? self::$static_cache_name : self::$dynamic_cache_name);
	}
	
	/**
	 * 检查指定缓存类型对应的文件是否过期
	 * @access private
	 * @param  path      $filename   文件路径
	 * @param  number    $type       缓存类型（0动态，1静态，2数据接口）
	 * @return boolean
	 */
	private static function chkCacheExpire($filename, $type = 1)
	{
		// 检查是否存在或可读
		if(!is_file($filename) || !is_readable($filename))
		{
			return false;
		}
		// 若应用入口为API入口，修正状态
		$type = $_GET['e'] == z::$configure['api_entry'] ? 2 : $type;
		// 动态缓存没有过期时间
		if(!$type)
		{
			return true;
		}
		// 数据接口使用数据缓存的有效时间，静态使用默认
		$tmpExpire = $type == 2 ? z::$configure['data_cache_expire'] : z::$configure['static_cache_expire'];
		// 检查是否过期
		if($tmpExpire !== 0 && time() > (filemtime($filename) + $tmpExpire))
		{
			return false;
		}
		return true;
	}
	
	/**
	 * 创建缓存文件
	 * @access public
	 * @param  mixed    $data    需要保存的内容
	 * @param  number   $type    缓存类型（0动态，1静态，2数据接口）
	 * @return path
	 */
	public static function save($data, $type = 1)
	{
		$filename = self::getCachePath($type);
		core::fastWriteFile($filename, $data, !!$type);
		return $filename;
	}
	
	/**
	 * 如缓存内容可用则返回静态内容或动态缓存的文件路径
	 * @access public
	 * @param  number   $type    缓存类型（0动态，1静态，2数据接口）
	 * @return string/boolean
	 */
	public static function get($type = 1)
	{
		$filename = self::getCachePath($type);
		if(self::chkCacheExpire($filename, $type))
		{
			return !$type ? $filename : core::fastReadFile($filename, !!$type);
		}
		return false;
	}
	
	/**
	 * 清楚指定缓存文件
	 * 
	 * @todo   这里还应该可以增加根据时间段删除文件
	 * 
	 * @access public
	 * @param  number    $type       缓存类型（0动态，1静态，2数据接口）
	 * @param  boolean   $dealType   处理类型（默认false删除指定，true删除全部）
	 * @return 
	 */
	public static function clean($type = 1, $dealType = false)
	{
		$filename = self::getCachePath($type);
		if($dealType)
		{
			core::recursiveDealDir(dirname($filename));
		}
		else
		{
			@unlink($filename);
		}
	}
}
