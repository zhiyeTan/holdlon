<?php

namespace z\core;

/**
 * 缓存类
 */
class Cache
{
	// 缓存路径
	private static $cache_path;
	// 缓存有效时间
	private static $cache_expire;
	// 文件名带路径
	private static $filename;
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		self::$cache_path = APP_PATH . Z_DS . 'cache' . Z_DS;
		self::$cache_expire = CACHE_EXPIRE;
		// cache文件夹不存在则创建并赋值权限
		if(!is_dir(self::$cache_path))
		{
			mkdir(self::$cache_path);
			chmod(self::$cache_path, 0777);
		}
	}
	
	// 单例方法，初始化对象
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	// 阻止用户复制对象实例
	public function __clone()
	{
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	/**
	 * 返回完整文件路径
	 * @param: string $key 唯一标识
	 * @return: string
	 */
	public function setName($key)
	{
		self::$filename = self::$cache_path.md5($key);
		return $this;
	}
	
	/**
	 * 创建缓存文件
	 * @param: args $data 内容数据
	 * @return: bool
	 */
	public function save($data)
	{
		// 序列化数据
		$file = fopen(self::$filename, 'w');
		if(flock($file, LOCK_EX))
		{
			fwrite($file, serialize($data));
			flock($file, LOCK_UN);
			fclose($file);
			return true;
		}
		else return false;
	}
	
	/**
	 * 如缓存内容可用则输出
	 * @return: mixed
	 */
	public function get()
	{
		if(!is_file(self::$filename) || !is_readable(self::$filename))
		{
			return false;
		}
		if(self::$cache_expire !== 0 && time() > (filemtime(self::$filename) + self::$cache_expire))
		{
			return false;
		}
		$file = fopen(self::$filename, "r");
		if(flock($file, LOCK_SH))
		{
			// 读取数据
			$data = unserialize(fread($file, filesize(self::$filename)));
			flock($file, LOCK_UN);
			fclose($file);
			// 发送响应但不更新缓存
			Response::init()->setCache(0)->send($data);
		}
		else return false;
	}
}
