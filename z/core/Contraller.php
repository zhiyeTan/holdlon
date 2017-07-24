<?php

namespace z\core;

/**
 * 控制器
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Contraller extends Template
{
	// 可接受的GET参数
	protected $allowRequestKey = array();
	// 移除不合法的请求参数
	public function fixRequestKey()
	{
		$diff = array_diff(array_keys($_GET), array_merge(array('e', 'm', 'c'), self::$allowRequestKey));
		foreach($diff as $v)
		{
			unset($_GET[$v]);
		}
	}
	/**
	 * 读取API提供的数据
	 * @access public
	 * @param  string  $module      模块名称
	 * @param  string  $contraller  控制器名称
	 * @param  array   $args        请求参数
	 */
	public function getApiData($module, $contraller, $args = array())
	{
		// 设置api对应的缓存名，并尝试获取缓存
		$apiEMC = array('e'=>API_ENTRY, 'm'=>$module, 'c'=>$contraller);
		$apiStaticName = Cache::formatCacheTag(array_merge($apiEMC, $args));
		$cache = Cache::setApiCacheName($apiStaticName)->get(true, true);
		if($cache)
		{
			return $cache;
		}
		// 没有缓存则执行api接口函数
		$apiPath = dirname(APP_PATH) . Z_DS . API_DIR . Z_DS . 'contrallers' . Z_DS . $module . Z_DS . $contraller . '.php';
		$alias = '\\contrallers\\' . $module . '\\' . $contraller;
		include $apiPath;
		$object = new $alias();
		if(method_exists($object, 'main'))
		{
			$object->main();
			$data = Response::getContent();
			// 重置响应内容和类型
			Response::setContentType('html')->setContent('');
			// 保存数据缓存
			Cache::save($data, true, true);
			return $data;
		}
		return false;
	}
	/**
	 * 渲染404页面
	 * @access public
	 * @param  string  $mixed
	 */
	public function display404($str)
	{
		// $this->render($template404); // 可渲染指定的404页面
		Response::setContent('<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . $str . '</p>');
	}
	/**
	 * 渲染401页面
	 * @access public
	 * @param  string  $mixed
	 */
	public function display401($str)
	{
		// $this->render($template404); // 可渲染指定的401页面
		Response::setContent('<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . $str . '</p>');
	}
	/**
	 * 渲染指定页面
	 * @access public
	 * @param  string  $mixed
	 */
	public function display($templateName)
	{
		$this->render($templateName);
	}
}
