<?php

namespace z\core;

class Log
{
	private $path;
	private $maxSize;
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		$this->path = Z_PATH . Z_DS . 'log' . Z_DS;
		// 设置日志大小上限为5m
		$this->maxSize = 5242880;
		// log文件夹不存在则创建并赋值权限
		if(!is_dir($this->path))
		{
			@mkdir($this->path);
			@chmod($this->path, 0777);
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
	
	// 
	public function save($name, $content)
	{
		$nosuffix = $this->path . $name;
		$filename = $nosuffix . '.txt';
		// 如果文件存在且超过大小上限，则以当前时间重命名该文件
		if(is_file($filename) && filesize($filename) > $this->maxSize)
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
		// 打开
		$file = fopen($filename, "ab");
		if(flock($file, LOCK_EX))
		{
			fwrite($file, $content.PHP_EOL);
			flock($file, LOCK_UN);
			fclose($file);
		}
	}
}
