<?php

namespace z\core;

use z\lib\Core as Core;

/**
 * 缓存机制
 * 
 * 静态缓存和动态缓存将保存到当前应用的目录中
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Cache
{
	// 数据接口缓存路径
	private static $api_cache_path;
	// 静态缓存路径
	private static $static_cache_path;
	// 动态缓存路径
	private static $dynamic_cache_path;
	// 静态缓存有效时间
	private static $static_cache_expire;
	// 数据缓存有效时间
	private static $data_cache_expire;
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
		self::$api_cache_path      = dirname(APP_PATH) . Z_DS . API_DIR . Z_DS . 'cache' . Z_DS . 'static' . Z_DS;
		self::$static_cache_path   = $tmpCachePath . 'static' . Z_DS;
		self::$dynamic_cache_path  = $tmpCachePath . 'dynamic' . Z_DS;
		self::$static_cache_expire = STATIC_CACHE_EXPIRE;
		self::$data_cache_expire   = DATA_CACHE_EXPIRE;
		
		Core::chkFolder($tmpCachePath);
		Core::chkFolder(self::$api_cache_path);
		Core::chkFolder(self::$static_cache_path);
		Core::chkFolder(self::$dynamic_cache_path);
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
	 * 格式化缓存标记（作为文件名）
	 * @access public
	 * @param  array   $array     静态时包含所有请求参数，动态时仅需模块和控制器2个参数
	 * @param  number  $isStatic  是否为静态缓存
	 * @return string
	 */
	public static function formatCacheTag($array, $isStatic = true)
	{
		$array['m'] = empty($array['m']) ? 'index' : $array['m'];
		$array['c'] = empty($array['c']) ? 'index' : $array['c'];
		// 动态缓存将格式化为md5(self::$authorKey/$_GET['m']/$_GET['c'])
		// 静态缓存将格式化为md5(slef::$authorKey/http_build_query($_GET))
		return md5(self::$authorKey . '/' . $isStatic ? http_build_query($array) : ($array['m'] . '/' . $array['c']));
	}
	
	/**
	 * 设置完整缓存文件名
	 * @access public
	 * @param  string   $key       唯一标识
	 * @param  number   $isStatic  是否为静态缓存
	 * @return this
	 */
	public static function setCacheName($key, $isStatic = true)
	{
		if($isStatic)
		{
			self::$static_cache_name  = self::$static_cache_path . $key;
		}
		else
		{
			self::$dynamic_cache_name = self::$dynamic_cache_path . $key . '.php';
		}
		return self::$_instance;
	}
	
	/**
	 * 设置完整的数据接口缓存文件名
	 * @access public
	 * @param  string  $key
	 * @return this
	 */
	public static function setApiCacheName($key)
	{
		self::$api_cache_name = self::$api_cache_path . $key;
		return self::$_instance;
	}
	
	/**
	 * 获取指定缓存类型对应的文件路径
	 * @access private
	 * @param  boolean  $isStatic  是否为静态缓存
	 * @return path
	 */
	private static function getCachePath($isStatic = true)
	{
		return $isStatic ? self::$static_cache_name : self::$dynamic_cache_name;
	}
	
	/**
	 * 检查指定缓存类型对应的文件是否过期
	 * @access private
	 * @param  path      $filename   文件路径
	 * @param  boolean   $isStatic   是否为静态缓存
	 * @return boolean
	 */
	private static function chkCacheExpire($filename, $isStatic = true, $isApi = false)
	{
		// 检查是否存在或可读
		if(!is_file($filename) || !is_readable($filename))
		{
			return false;
		}
		// 动态缓存没有过期时间
		if(!$isApi && !$isStatic)
		{
			return true;
		}
		// 若应用入口为API入口，修正状态
		if($_GET['e'] == API_ENTRY)
		{
			$isApi = true;
		}
		// 数据接口使用数据缓存的有效时间，静态使用默认
		$tmpExpire = $isApi ? self::$data_cache_expire : self::$static_cache_expire;
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
	 * @param  mixed     $data       需要保存的内容
	 * @param  boolean   $isStatic   是否为静态缓存
	 * @param  boolean   $isApi      是否为静态缓存
	 * @return path
	 */
	public static function save($data, $isStatic = true, $isApi = false)
	{
		$filename = $isApi ? self::$api_cache_name : self::getCachePath($isStatic);
		Core::writeFile($filename, $data, $isApi || $isStatic);
		return $filename;
	}
	
	/**
	 * 如缓存内容可用则返回静态内容或动态缓存的文件路径
	 * @access public
	 * @param  boolean   $isStatic   是否为静态缓存
	 * @return string/boolean
	 */
	public static function get($isStatic = true, $isApi = false)
	{
		$filename = $isApi ? self::$api_cache_name : self::getCachePath($isStatic);
		if(self::chkCacheExpire($filename, $isStatic, $isApi))
		{
			return !$isApi && !$isStatic ? $filename : Core::readFile($filename, $isStatic);
		}
		return false;
	}
	
	/**
	 * 清楚指定缓存文件
	 * 
	 * @todo   这里还可以增加根据时间段删除文件
	 * 
	 * @access public
	 * @param  boolean   $isStatic   是否为静态缓存
	 * @param  boolean   $dealType   处理类型（默认false删除指定，true删除全部）
	 * @return 
	 */
	public static function clean($isStatic = true, $isApi = false, $dealType = false)
	{
		$filename = $isApi ? self::$api_cache_name : self::getCachePath($isStatic);
		if($dealType)
		{
			Core::recursiveDealDir(dirname($filename));
		}
		else
		{
			@unlink($filename);
		}
	}
}
