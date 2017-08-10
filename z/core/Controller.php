<?php

namespace z\core;

use z;

/**
 * 控制器
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class controller extends template
{
	// 受许可的GET参数的键名数组，如：array('cid', 'keyword', 'page')
	// 需验证的GET参数的键名及规则值对数组，如：array('cid'=>'int', 'keyword'=>'addslashes', 'page'=>'int')
	// 需过滤的GET参数的键名，默认值，规则数组，如：array(array('cid', 0, 'int'), array('keyword', '', 'addslashes', array('page', 1, 'int')))
	// POST同理
	protected static $allowGetKeys		= array();
	protected static $verifyGetValues	= array();
	protected static $filterGetValues	= array();
	protected static $allowPostKeys		= array();
	protected static $verifyPostValues	= array();
	protected static $filterPostValues	= array();
	
	/**
	 * 校验请求
	 * @access public
	 * @param  boolean  $isGet  目标函数是否为GET
	 */
	public function keepSafeQuest($isGet = true)
	{
		// 分别确定目标数组、许可键名数组、验证数组、过滤数组、基础键名数组、异常日志文件名
		$target		= $isGet ? $_GET : $_POST;
		$allows		= $isGet ? self::$allowGetKeys : self::$allowPostKeys;
		$verifys	= $isGet ? self::$verifyGetValues : self::$verifyPostValues;
		$filters	= $isGet ? self::$filterGetValues : self::$filterPostValues;
		$basics		= $isGet ? array('e', 'm', 'c') : array('token');
		$logName	= $isGet ? 'illegalGetLog' : 'abnormalPostLog';
		
		$error = false;
		// 获得不被允许的参数键名
		$diff = array_diff(array_keys($target), array_merge($basics, array_keys($allows)));
		// 验证参数的合法性
		foreach($verifys as $k => $rule)
		{
			// 存在且不合法时标记错误
			if(isset($target[$k]) && !safe::verify($target[$k], $rule))
			{
				$error = true;
				break;
			}
		}
		// 若存在差异键名或非法验证，记录请求信息到日志中
		if($diff || $error)
		{
			$content  = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ';
			$content .= request::ip(0) . ' ';
			$content .= $isGet ? request::realUrl() : var_export($_POST);
			logs::init()->save($logName, $content);
			// 参数不合法时直接输出错误
			if($error)
			{
				$this->displayError(405, '非法参数');
			}
			// 删除多余的参数
			array_map(function($v){
				if($isGet) unset($_GET[$v]);
				else unset($_POST[$v]);
			}, $diff);
			// 如果GET参数存在差异，重设静态与动态缓存的文件名
			if($isGet)
			{
				cache::setCacheName($_GET);
				cache::setCacheName($_GET, 0);
			}
		}
		// 过滤参数
		foreach($filters as $row)
		{
			if(isset($target[$row[0]]))
			{
				$tmpValue = safe::filter($target[$row[0]], $row[2]);
				$tmpValue = empty($tmpValue) ? $row[1] : $tmpValue;
				if($isGet) $_GET[$row[0]] = $tmpValue;
				else $_POST[$row[0]] = $tmpValue;
			}
		}
	}
	
	/**
	 * 读取API提供的数据
	 * @access public
	 * @param  string  $module      模块名称
	 * @param  string  $controller  控制器名称
	 * @param  array   $args        请求参数
	 */
	public function getApiData($module, $controller, $args = array())
	{
		// 设置api对应的缓存名，并尝试获取缓存
		$apiEMC = array('e'=>z::$configure['api_entry'], 'm'=>$module, 'c'=>$controller);
		$cache = cache::setCacheName(array_merge($apiEMC, $args), 2)->get(2);
		if($cache)
		{
			return $cache;
		}
		// 没有缓存则执行api接口函数
		$apiPath = dirname(APP_PATH) . Z_DS . z::$configure['api_dir'] . Z_DS . 'controllers' . Z_DS . $module . Z_DS . $controller . '.php';
		$alias = '\\controllers\\' . $module . '\\' . $controller;
		include $apiPath;
		$object = new $alias();
		if(method_exists($object, 'main'))
		{
			$tmpGet = $_GET; // 存放当前的GET参数
			$_GET = $args; // 将请求参数放进GET中，以应用参数
			$object->main();
			$data = response::getContent();
			// 重置响应内容和类型
			response::setContentType('html')->setContent('');
			// 保存数据缓存
			cache::save($data, 2);
			$_GET = $tmpGet; // 重置为当前GET参数
			return $data;
		}
		return false;
	}
	/**
	 * 异常页面渲染
	 * @access public
	 * @param  number  $code     状态码
	 * @param  string  $content  内容
	 */
	public function displayError($code, $content)
	{
		$content = '<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . $content . '</p>';
		response::init()
			->setExpire(0)
			->setCache(0)
			->setCode($code)
			->setContent($content)
			->send();
		exit(0);
	}
	/**
	 * 渲染指定页面
	 * @access public
	 */
	public function display($templateName)
	{
		$this->render($templateName);
	}
}
