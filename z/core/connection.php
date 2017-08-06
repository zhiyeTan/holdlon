<?php

namespace z\core;

use PDO;
use mysqli;

/**
 * 数据库连接类
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class connection
{
	// 是否发生错误
	protected static $error = false;
	// 错误信息
	protected static $errstr = array();
	// 私有构造方法
	public static function dbConnect($options, $pdo_connect = true)
	{
		$conn = false;
		// 修正端口
		if(!isset($options['port']))
		{
			$options['port'] = 3306;
		}
		elseif(!is_int($options['port']))
		{
			$options['port'] = (int)$options['port'];
			if(!$options['port'])
			{
				$options['port'] = 3306;
			}
		}
		// 不使用PDO连接时，使用mysqli连接
		if(!$pdo_connect)
		{
			// 设置以抛出异常的方式替换警告级的错误
			mysqli_report(MYSQLI_REPORT_STRICT);
			// 尝试进行mysqli连接
			try
			{
				$conn = new mysqli($options['server'], $options['username'], $options['password'], '', $options['port']);
			}
			catch(Exception $e)
			{
				self::$error = true;
				self::$errstr = $e->getMessage();
			}
			// 连接成功则设置字符集
			if(!self::$error)
			{
				$conn->set_charset($options['charset']);
			}
		}
		else
		{
			// 拼接dsn
			$dsn = $options['dbtype'] . ':host=' . $options['server'] . ';port=' . $options['port'];
			try
			{
				$conn = new PDO($dsn, $options['username'], $options['password']);
			}
			catch(Exception $e)
			{
				self::$error = true;
				self::$errstr = $e->getMessage();
			}
			// 连接成功则设置字符集
			if(!self::$error)
			{
				$conn->query('SET NAMES ' . $options['charset']);
			}
		}
		return $conn;
	}
}
