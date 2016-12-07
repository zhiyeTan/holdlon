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
	 * @return string
	 */
	public function ip()
	{
		if('/'==DIRECTORY_SEPARATOR) return $_SERVER['SERVER_ADDR'];
		else return @gethostbyname($_SERVER['SERVER_NAME']);
	}
}
