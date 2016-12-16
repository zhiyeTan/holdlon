<?php

namespace z\core;

class Tunnel
{
	private static $syncMaps = array();
	private static $asyncMaps = array();
	// 构造函数声明为private, 不允许创建对象
	private function __construct(){}
	
	/**
	 * 同步操作绑定
	 * @param string $objectName 对象别名
	 * @param string $methodName 方法名
	 * @param bool   $singleton  单例模式
	 * @param array  $args       传入方法的参数
	 */
	public static function onSync($objectName, $methodName, $singleton = 0, $args = null)
	{
		self::$syncMaps[] = array(
			'singleton'		=> $singleton,
			'objectName'	=> $objectName,
			'methodName'	=> $methodName,
			'args'			=> $args
		);
	}
	
	/**
	 * 异步操作绑定
	 * @param string $method    [get/post]参数提交方式
	 * @param string $key       [get时作为键名，post时作为对象别名]
	 * @param string $value     [get时作为键值，post时作为方法名]
	 * @param bool   $singleton [get时无效，post时表示对象是否单例模式]
	 * @param array  $args      [get时无效，post时作为传入方法的参数]
	 */
	public static function onAsync($method, $key, $value, $singleton = 0, $args = null)
	{
		if($method == 'get')
		{
			self::$asyncMaps['get'][] = array($key => $value);
		}
		else
		{
			self::$asyncMaps['post'][] = array(
				'singleton'		=> $singleton,
				'objectName'	=> $key,
				'methodName'	=> $value,
				'args'			=> $args
			);
		}
	}
	
	// 触发操作
	public static function trigger()
	{
		self::runSync();
		self::sendAsync();
	}
	
	// 发送异步请求
	private static function sendAsync()
	{
		$host = ASYNC_DOMAIN ? ASYNC_DOMAIN : $_SERVER['HTTP_HOST'];
		$port = ASYNC_PORT ? ASYNC_PORT : Request::port();
		$timeout = 100;
		// 默认为get请求
		$method = 'GET';
		// 设置异步请求的emca参数分别为：async，来源入口，来源模块，来源控制器
		$target = '/async/' . $_GET['e'] . '/' . $_GET['m'] . '/' . $_GET['c'];
		// 如果没有异步请求，直接退出
		if(empty(self::$asyncMaps))
		{
			return;
		}
		// 如果存在get参数，取出并拼接地址（参考路由模式3）
		if(isset(self::$asyncMaps['get']))
		{
			$tmpStr = http_build_query(self::$asyncMaps['get']);
			$target .= $tmpStr ? ('/' . strtr($tmpStr, '=&', '//')) : '';
		}
		// 如果存在post参数
		if(isset(self::$asyncMaps['post']))
		{
			// 设置请求方式为post
			$method = 'POST';
			// 序列化数据后放到data里面
			$postData = 'data=' . serialize(self::$asyncMaps['post']);
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
		/*
		// 异步调试
		while(!feof($socket))
		{
			echo fgets($socket, 4096), PHP_EOL;
		}
		//*/
		fclose($socket);
	}
	
	// 执行同步操作
	private static function runSync()
	{
		// 没有同步操作，退出
		if(empty(self::$syncMaps))
		{
			return;
		}
		// 遍历执行同步操作
		self::runAction(self::$syncMaps);
	}
	
	// 执行异步操作
	public static function runAsync()
	{
		// TODO 此处添加对于GET请求的处理（目前尚未出现需要通过GET请求去处理的操作，所以是个不定式）
		if(empty($_POST['data']))
		{
			return;
		}
		// 反序列化POST传递的操作后，交给操作函数去依次执行
		self::runAction(unserialize($_POST['data']));
	}
	
	// 执行操作
	private static function runAction($actions)
	{
		foreach($actions as $act)
		{
			// 判断模式、类别名、方法名是否存在
			if(isset($act['singleton']) && isset($act['objectName']) && isset($act['methodName']))
			{
				// 根据模式构建对象 [强制单例的构建方法为init]
				if(!!$act['singleton'])
				{
					$object = $act['objectName']::init();
				}
				else
				{
					$object = new $act['objectName']();
				}
				// 利用回调函数执行操作
				call_user_func_array(array($object, $act['methodName']), $act['args']);
				// 立即释放对象
				unset($object);	
			}
		}
	}
}
