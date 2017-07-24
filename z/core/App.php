<?php

namespace z\core;

use z;
use z\lib\Core as Core;

/**
 * 应用管理
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class App
{
	// 是否使用缓存
	private static $noCache = 0;
	// 是否发生错误
	private static $error = false;
	// 构造函数
	public function __construct()
	{
		// 初始化路由器并解析当前请求
		Router::init()->parse();
	}
	/**
	 * 执行应用
	 * @access public
	 */
	public static function run()
	{
		// 初始化cookie
		Cookie::init();
		// 初始化session
		Session::init();
		// 初始化响应对象
		Response::init();
		// 若不使用缓存则设置缓存为false，否则获取真实的缓存
		$cache = self::$noCache ? !1 : Cache::get();
		// 分别获得模块、控制器文件名
		$moduleFileName = APP_PATH . 'contrallers' . Z_DS . $_GET['m'];
		$contrallerFileName = $moduleFileName . Z_DS . $_GET['c'] . '.php';
		// 获得类别名
		$alias = '\\contrallers\\' . $_GET['m'] . '\\' . $_GET['c'];
		// 创建一个空对象
		$object = (object) array();
		// 检查控制器是否存在
		if(is_file($contrallerFileName))
		{
			// 赋值为控制器对象
			$object = new $alias();
		}
		else
		{
			// 这里只记录控制不存在的状态，而不直接进行错误响应
			// 是因为有缓存的情况下，允许优先使用缓存，忽略程序变更的情况
			self::$error = !0;
		}
		// 判断缓存状态
		if(!$cache)
		{
			if(self::$error || !method_exists($object, 'main'))
			{
				// 渲染404视图
				$contraller = new Contraller();
				$contraller->display404('控制器异常！');
				// 发送404响应
				Response::setExpire(0)->setCache(0)->setCode(404)->send();
				exit(0);
			}
			/**
			 * TODO 这个注释掉的语句是用来修正请求参数的，它能排序一些非法提交的参数
			 *      但它设计出来并不只是为了处理参数的，而是结合参数去得到确定名称的静态缓存
			 *      同时，这个名称并不需要加密，而且是可逆的，可以从文件名上逆向得出具体请求地址及参数
			 *      最终，可以利用这一特点进行静态缓存的更新
			 * $object->fixRequestKey();
			 */
			$object->main();
		}
		else
		{
			Response::setCache(0)->setCode(304)->setContent($cache);
		}
		// 发送响应
		Response::send();
		// 尝试执行可能存在的延后操作
		if(method_exists($object, 'delay'))
		{
			$object->delay();
		}
		exit(0);
	}
	
}