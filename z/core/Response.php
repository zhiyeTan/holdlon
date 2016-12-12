<?php

namespace z\core;

class Response
{
	// 本地缓存时间
	private static $expire;
	
	// 保存例实例在此属性中
	private static $_instance;
	
	// 状态码地图
	private static $codeMap = array(
		100 => "HTTP/1.1 100 Continue",
		101 => "HTTP/1.1 101 Switching Protocols",
		200 => "HTTP/1.1 200 OK",
		201 => "HTTP/1.1 201 Created",
		202 => "HTTP/1.1 202 Accepted",
		203 => "HTTP/1.1 203 Non-Authoritative Information",
		204 => "HTTP/1.1 204 No Content",
		205 => "HTTP/1.1 205 Reset Content",
		206 => "HTTP/1.1 206 Partial Content",
		300 => "HTTP/1.1 300 Multiple Choices",
		301 => "HTTP/1.1 301 Moved Permanently",
		302 => "HTTP/1.1 302 Found",
		303 => "HTTP/1.1 303 See Other",
		304 => "HTTP/1.1 304 Not Modified",
		305 => "HTTP/1.1 305 Use Proxy",
		307 => "HTTP/1.1 307 Temporary Redirect",
		400 => "HTTP/1.1 400 Bad Request",
		401 => "HTTP/1.1 401 Unauthorized",
		402 => "HTTP/1.1 402 Payment Required",
		403 => "HTTP/1.1 403 Forbidden",
		404 => "HTTP/1.1 404 Not Found",
		405 => "HTTP/1.1 405 Method Not Allowed",
		406 => "HTTP/1.1 406 Not Acceptable",
		407 => "HTTP/1.1 407 Proxy Authentication Required",
		408 => "HTTP/1.1 408 Request Time-out",
		409 => "HTTP/1.1 409 Conflict",
		410 => "HTTP/1.1 410 Gone",
		411 => "HTTP/1.1 411 Length Required",
		412 => "HTTP/1.1 412 Precondition Failed",
		413 => "HTTP/1.1 413 Request Entity Too Large",
		414 => "HTTP/1.1 414 Request-URI Too Large",
		415 => "HTTP/1.1 415 Unsupported Media Type",
		416 => "HTTP/1.1 416 Requested range not satisfiable",
		417 => "HTTP/1.1 417 Expectation Failed",
		500 => "HTTP/1.1 500 Internal Server Error",
		501 => "HTTP/1.1 501 Not Implemented",
		502 => "HTTP/1.1 502 Bad Gateway",
		503 => "HTTP/1.1 503 Service Unavailable",
		504 => "HTTP/1.1 504 Gateway Time-out" 
	);
	
	// 内容类型地图
	private static $contentTypeMap = array(
		'html'			=> 'Content-Type: text/html; charset=utf-8',
		'plain'			=> 'Content-Type: text/plain',
		'jpeg'			=> 'Content-Type: image/jpeg',
		'zip'			=> 'Content-Type: application/zip',
		'pdf'			=> 'Content-Type: application/pdf',
		'mpeg'			=> 'Content-Type: audio/mpeg',
		'css'			=> 'Content-type: text/css',
		'javascript'	=> 'Content-type: text/javascript',
		'json'			=> 'Content-type: application/json',
		'xml'			=> 'Content-type: text/xml',
		'flash'			=> 'Content-Type: application/x-shockw**e-flash'
	);
	
	// 状态码
	private static $code;
	
	// 内容类型
	private static $contentType;
	
	// 动态缓存状态
	private static $cache;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		self::$expire = LOCAL_EXPIRE;
		self::$code = 200;
		self::$contentType = 'html';
		self::$cache = true;
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
		trigger_error('Clone is not allow' ,E_USER_ERROR);
	}
	
	// 设置本地缓存时间
	public static function setExpire($timeStamp)
	{
		self::$expire = (int) $timeStamp;
		return self::$_instance;
	}
	
	// 设置是否使用动态缓存
	public static function setCache($status)
	{
		self::$cache = !!$status;
		return self::$_instance;
	}
	
	// 设置响应状态码
	public static function setCode($code)
	{
		if(in_array($code, array_keys($codeMap))) self::$code = $code;
		return self::$_instance;
	}
	
	// 设置内容类型
	public static function setContentType($type)
	{
		if(in_array($type, array_keys($contentTypeMap))) self::$contentType = $type;
		return self::$_instance;
	}
	
	/**
	 * 发送数据到客户端
	 * @param: mixed $data 数据
	 * @return: mixed
	 */
	public static function send(&$data)
	{
		// 检查 HTTP 表头是否已被发送
		if(!headers_sent())
		{
			// 发送头部信息
			header(self::$codeMap[self::$code]);
			header('Content-language: ' . DEFAULT_LANG);
			header('Cache-Control: max-age=' . self::$expire . ',must-revalidate');
			header('Last-Modified:' . gmdate('D,d M Y H:i:s') . ' GMT');
			header('Expires:' . gmdate('D,d M Y H:i:s',$_SERVER['REQUEST_TIME'] + self::$expire) . ' GMT');
			header(self::$contentTypeMap[self::$contentType]);
		}
		if(200 == self::$code && self::$cache)
		{
			Cache::save($data);
		}
		echo $data;
		if(function_exists('fastcgi_finish_request'))
		{
			// 提高页面响应
			fastcgi_finish_request();
		}
		exit(0);
	}
	
	/**
	 * 对变量进行 JSON 编码 [不转义中文]
	 * TODO 当前仅接受 UTF-8 编码的数据
	 * @param mixed $value 待编码的 value [除了resource 类型之外]
	 * @return string 返回JSON形式
	 * 
	 */
	public static function sendJSON(&$data)
	{
		// 兼容5.3，处理编码时不转义中文
		if(version_compare(PHP_VERSION,'5.4.0','<'))
		{
			$json = json_encode($data);
			$json = preg_replace_callback(
				"#\\\u([0-9a-f]{4})#i",
				function($matchs)
				{
					return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
				},
				$json
			);
		}
		else
		{
			$json = json_encode($data, JSON_UNESCAPED_UNICODE);
		}
		unset($data);
		// 分别对jsonp和json做处理
		$json = isset($_GET['callback']) ? (trim($_GET['callback']) . '(' . $json . ')') : $json;
		// 设置内容类型为jsonp或json
		self::$contentType = isset($_GET['callback']) ? 'javascript' : 'json';
		self::send($json);
	}
}
