<?php

namespace z\core;

/**
 * 请求类
 */
class Request
{
	// 保存例实例在此属性中
	private static $_instance;
	
	/**
     * @var array 请求参数
     */
    protected $param   = array();
    protected $get     = array();
    protected $post    = array();
    protected $request = array();
    protected $route   = array();
    protected $put;
    protected $session = array();
    protected $file    = array();
    protected $cookie  = array();
    protected $server  = array();
    protected $header  = array();
	
	/**
     * @var array 资源类型
     */
    protected $mimeType = array(
        'xml'  => 'application/xml,text/xml,application/x-xml',
        'json' => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'   => 'text/javascript,application/javascript,application/x-javascript',
        'css'  => 'text/css',
        'rss'  => 'application/rss+xml',
        'yaml' => 'application/x-yaml,text/yaml',
        'atom' => 'application/atom+xml',
        'pdf'  => 'application/pdf',
        'text' => 'text/plain',
        'png'  => 'image/png',
        'jpg'  => 'image/jpg,image/jpeg,image/pjpeg',
        'gif'  => 'image/gif',
        'csv'  => 'text/csv',
        'html' => 'text/html,application/xhtml+xml,*/*',
    );
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct(){}
	
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
	 * 获取客户端IP地址
	 * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	public function ip($type = 0, $adv = false)
	{
		$type      = $type ? 1 : 0;
		static $ip = null;
		if(null !== $ip) return $ip[$type];
		if($adv)
		{
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				$pos = array_search('unknown', $arr);
				if(false !== $pos) unset($arr[$pos]);
				$ip = trim(current($arr));
			}
			elseif(isset($_SERVER['HTTP_CLIENT_IP']))
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			elseif(isset($_SERVER['REMOTE_ADDR']))
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		elseif (isset($_SERVER['REMOTE_ADDR'])) {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u", ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
	
}
