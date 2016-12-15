<?php

namespace z\core;

class Async
{
	private static $eventMaps = array();
	// 构造函数声明为private, 不允许创建对象
	private function __construct(){}
	
	/**
	 * 绑定参数
	 * @param string $method [get/post]参数提交方式
	 * @param string $key [get时作为键名，post时作为对象别名]
	 * @param string $value [get时作为键值，post时作为方法名]
	 * @param array  $args [get时无效，post时作为传入方法的参数]
	 */
	public static function on($method, $key, $value, $args = null)
	{
		if($method == 'get')
		{
			self::$eventMaps['get'][] = array($key => $value);
		}
		else
		{
			self::$eventMaps['post'][] = array(
				'objectName'	=> $key,
				'methodName'	=> $value,
				'args'			=> $args
			);
		}
	}
	
	// 触发异步请求
	public static function trigger()
	{
		$host = ASYNC_DOMAIN ? ASYNC_DOMAIN : $_SERVER['HTTP_HOST'];
		$port = ASYNC_PORT ? ASYNC_PORT : Request::port();
		$timeout = 100;
		// 默认为get请求
		$method = 'GET';
		$target = '/async';
		// 如果没有异步请求，直接退出
		if(empty(self::$eventMaps))
		{
			return;
		}
		// 如果存在get参数，取出并拼接地址（参考路由模式3）
		if(isset(self::$eventMaps['get']))
		{
			$tmpStr = http_build_query(self::$eventMaps['get']);
			$target .= $tmpStr ? ('/' . strtr($tmpStr, '=&', '//')) : '';
		}
		// 如果存在post参数
		if(isset(self::$eventMaps['post']))
		{
			// 设置请求方式为post
			$method = 'POST';
			// 序列化数据后放到data里面
			$postData = 'data=' . serialize(self::$eventMaps['post']);
		}
		// 拼接header
		$request  = "$method $target HTTP/1.1" . PHP_EOL;
		$request .= "Host: $host" . PHP_EOL;
		// 拼接post相关的header
		if($method == 'POST')
		{
			$lenght = mb_strlen($postData);
			$request .= 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL; 
			$request .= 'Content-Length: ' . $lenght . PHP_EOL; 
			$request .= PHP_EOL;
			$request .= $postData;
		}
		// 发送异步请求
		$socket = fsockopen($host, $port, $errno, $errstr, $timeout);
		// 设置为非阻塞模式
		stream_set_blocking($socket, 0);
		fputs($socket, $request);
		fclose($socket);
	}
}
