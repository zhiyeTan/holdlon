<?php

namespace z\core;

/**
 * 控制器
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Controller extends Template
{
	// 可接受的GET参数
	protected $allowGetKey = array();
	// 可接受的POST参数
	protected $allowPostKey = array();
	/**
	 * 校验请求
	 * @access public
	 */
	public function fixQuest()
	{
		$this->fixGet();
		$this->fixPost();
	}
	
	/**
	 * 移除不合法的请求参数
	 * @access private
	 */
	private function fixGet()
	{
		$diff = array_diff(array_keys($_GET), array_merge(array('e', 'm', 'c'), self::$allowRequestKey));
		// 若存在差异，记录请求信息到日志中，并删除不合法的参数
		if($diff)
		{
			$content  = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ';
			$content .= Request::ip(0) . ' ';
			$content .= Request::realUrl();
			Log::init()->save('illegalGetLog', $content);
			array_map(function($v){unset($_GET[$v]);}, $diff);
		}
	}
	/**
	 * 修正不合法的POST参数
	 * @access private
	 */
	private function fixPost()
	{
		
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
		$apiEMC = array('e'=>API_ENTRY, 'm'=>$module, 'c'=>$controller);
		$apiStaticName = Cache::formatCacheTag(array_merge($apiEMC, $args));
		$cache = Cache::setApiCacheName($apiStaticName)->get(true, true);
		if($cache)
		{
			return $cache;
		}
		// 没有缓存则执行api接口函数
		$apiPath = dirname(APP_PATH) . Z_DS . API_DIR . Z_DS . 'controllers' . Z_DS . $module . Z_DS . $controller . '.php';
		$alias = '\\controllers\\' . $module . '\\' . $controller;
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
