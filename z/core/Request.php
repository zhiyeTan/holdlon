<?php

namespace z\core;

/**
 * 请求管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class request
{
	/**
	 * 当前是否ssl
	 * @access public
	 * @return bool
	 */
	public static function isSsl()
	{
		if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS'])))
		{
			return true;
		}
		elseif(isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME'])
		{
			return true;
		}
		elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT']))
		{
			return true;
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 获得当前端口
	 * @access public
	 * @return number
	 */
	public static function port()
	{
		return $_SERVER['SERVER_PORT'];
	}
	
	/**
	 * 当前URL地址中的scheme参数
	 * @access public
	 * @return string
	 */
	public static function scheme()
	{
		return self::isSsl() ? 'https' : 'http';
	}
	
	/**
	 * 获取当前包含协议的域名
	 * @access public
	 * @return string
	 */
	public static function domain()
	{
		return self::scheme() . '://' . $_SERVER['HTTP_HOST'];
	}
	
	/**
	 * 获取当前完整的URL
	 * @access public
	 * @return string
	 */
	public static function realUrl()
	{
		return self::domain() . $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * 获取cookie/session需设置的域
	 * @access public
	 * @return string
	 */
	public static function cosDomain()
	{
		return substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.'));
	}
	
	/**
	 * 获取客户端IP地址
	 * @access public
	 * @param  integer  $type  返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param  boolean  $adv   是否进行高级模式获取（有可能被伪装）
	 * @return array
	 */
	public static function ip($type = 0, $adv = false)
	{
		$type = $type ? 1 : 0;
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
		elseif(isset($_SERVER['REMOTE_ADDR']))
		{
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u", ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
}
