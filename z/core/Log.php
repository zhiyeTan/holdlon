<?php

namespace z\core;

class Log
{
	private static $path;
	private static $maxSize;
	
	public function __construct()
	{
		self::$path = Z_PATH . Z_DS . 'log' . Z_DS;
		// 设置日志大小上限为5m
		self::$maxSize = 5242880;
		// log文件夹不存在则创建并赋值权限
		if(!is_dir(self::$path))
		{
			mkdir(self::$path);
			chmod(self::$path, 0777);
		}
	}
	
	/**
	 * 保存日志
	 * @param: string $name 日志文件名
	 * @param: string $content 单条日志的内容
	 */
	public function save($name, $content)
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
