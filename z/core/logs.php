<?php

namespace z\core;

use z\lib\core as core;

/**
 * 日志类
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class logs
{
	private static $path;
	private static $maxSize;
	private static $_instance;
	
	// 禁止直接创建对象
	private function __construct()
	{
		self::$path = APP_PATH . 'logs' . Z_DS;
		// 设置日志大小上限（单位字节）
		self::$maxSize = 1000000;
		// log文件夹不存在则创建并赋值权限
		core::chkFolder(self::$path);
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
	 * 保存日志
	 * @access public
	 * @param  string  $name     日志文件名
	 * @param  string  $content  单条日志的内容
	 * @return boolean
	 */
	public static function save($name, $content)
	{
		$nosuffix = self::$path . $name;
		$filename = $nosuffix . '.txt';
		// 如果文件存在且超过大小上限，则以当前时间重命名该文件
		if(is_file($filename) && filesize($filename) > self::$maxSize)
		{
			$newname = $nosuffix . time() . '.txt';
			// 设置一个值，防止出现死循环(一次延迟100毫秒，30次相当于3s)
			$domax = 30;
			// 循环，直到成功或者超时
			$i = 0;
			do
			{
				++$i;
				$status = rename($filename, $newname);
				if(!$status)
				{
					usleep(100); // 延迟100毫秒
				}
			}
			while(!$status && $i < $domax);
		}
		// 保存日志
		return core::writeFile($filename, $content.PHP_EOL, false, true, false);
	}
}
