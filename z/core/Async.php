<?php

namespace z\core;

class Async
{
	// 操作
	private static $eventMap = array();
	// 操作所需的参数
	private static $argsMap = array();
	
	// 构造函数声明为private, 不允许创建对象
	private function __construct(){}
	
	/**
	 * 注册操作
	 * @param string $object 对象
	 * @param string $methodName 方法名
	 * @param string $methodAlias 方法别名
	 * @param array  $args 传入的参数
	 */
	public static function on(&$object, $methodName, $methodAlias, $args = null)
	{
		self::$eventMap[$methodAlias] = array($object, $methodName);
		self::$argsMap[$methodAlias] = $args;
	}
	
	// 触发所有操作
	public static function call()
	{
		foreach(self::$eventMap as $k => $v)
		{
			call_user_func_array($v, self::$argsMap[$k]);
		}
	}
	
	// 触发异步操作
	public static function trigger()
	{
		$repeat  = 100;  // How many times repeat the test 
		$timeout = 100;  // Max time for stablish the conection 
		$size    = 16;   // Bytes will be read (and display). 0 for read all 

		$server  = '127.0.0.1';            // IP address 
		$host    = 'www.example.net';             // Domain name 
		$target  = '/poll/answer.asp';        // Specific program 
		$referer = 'http://www.example.com/';    // Referer 
		$port    = 80;
		
		// Setup an array of fields to get with then create the get string 
		$gets = array ( 'get_field_1' => 'somevalue', 
		                'get_field_2' => 'somevalue' ); 
		
		// Setup an array of fields to post with then create the post string 
		$posts = array ( 'post_field_1' => 'somevalue', 
		                 'post_field_2' => 'somevalue' ); 
		
		// That's all. Now the program proccess $repeat times 

		$method = 'GET';
		if(is_array($gets))
		{
			$getValues = '?';
			foreach($gets AS $name => $value)
			{
				$getValues .= urlencode($name) . '=' . urlencode($value) . '&';
			}
			$getValues = substr( $getValues, 0, -1 );
		}
		else
		{
			$getValues = '';
		}
		
		if(is_array($posts))
		{
			foreach($posts AS $name => $value)
			{
				$postValues .= urlencode($name) . '=' . urlencode($value) . '&';
			}
			$postValues = substr($postValues, 0, -1);
			$method = 'POST';
		}
		else
		{
			$postValues = '';
		}
	
		$request  = "$method $target$getValues HTTP/1.1" . PHP_EOL;
		$request .= "Host: $host" . PHP_EOL;
		$request .= 'User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) ';
		$request .= 'Gecko/20021204' . PHP_EOL;
		$request .= 'Accept: text/xml,application/xml,application/xhtml+xml,';
		$request .= 'text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,';
		$request .= 'image/jpeg,image/gif;q=0.2,text/css,*/*;q=0.1' . PHP_EOL;
		$request .= 'Accept-Language: en-us, en;q=0.50' . PHP_EOL;
		$request .= 'Accept-Encoding: gzip, deflate, compress;q=0.9' . PHP_EOL;
		$request .= 'Accept-Charset: ISO-8859-1, utf-8;q=0.66, *;q=0.66' . PHP_EOL;
		$request .= 'Keep-Alive: 300' . PHP_EOL;
		$request .= 'Connection: keep-alive' . PHP_EOL;
		$request .= 'Referer: $referer' . PHP_EOL;
		$request .= 'Cache-Control: max-age=0' . PHP_EOL;
		
		if($method == 'POST')
		{
			$lenght = strlen($postValues);
			$request .= 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL; 
			$request .= 'Content-Length: $lenght' . PHP_EOL; 
			$request .= PHP_EOL;
			$request .= $postValues;
		}
		
		for($i = 0; $i < $repeat; ++$i)
		{
			$socket = fsockopen($server, $port, $errno, $errstr, $timeout);
			fputs($socket, $request);
			fclose($socket);
		}
	}
}
