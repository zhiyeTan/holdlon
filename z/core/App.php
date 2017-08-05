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
		// TODO 在控制器中统一对GET参数做判断处理
		// TODO 在控制器中统一对POST参数做判断处理
		// TODO 结合Validate类
		// TODO 统一日志处理
		// TODO 缓存文件名修正为可逆
		// TODO 统一底层数据交互在basic文件夹中，但该文件夹不再在z文件夹中，而是与入口、应用目录同级，且应以模块分组
		// TODO 统一在底层数据交互中规范可接收字段以及规则，验证后才允许后续操作
		// TODO 增加PDO类的整合
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
		$moduleFileName = APP_PATH . 'controllers' . Z_DS . $_GET['m'];
		$controllerFileName = $moduleFileName . Z_DS . $_GET['c'] . '.php';
		// 获得类别名
		$alias = '\\controllers\\' . $_GET['m'] . '\\' . $_GET['c'];
		// 创建一个空对象
		$object = (object) array();
		// 检查控制器是否存在
		if(is_file($controllerFileName))
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
				$controller = new Controller();
				$controller->displayError(404, '控制器异常！');
			}
			/**
			 * TODO 这个注释掉的语句是用来修正请求参数的，它能排序一些非法提交的参数
			 *      但它设计出来并不只是为了处理参数的，而是结合参数去得到确定名称的静态缓存
			 *      同时，这个名称并不需要加密，而且是可逆的，可以从文件名上逆向得出具体请求地址及参数
			 *      最终，可以利用这一特点进行静态缓存的更新
			 * $object->fixRequestKey();
			 */
			// 分别执行GET参数、POST参数的安全校验以及主方法
			$object->keepSafeQuest();
			$object->keepSafeQuest(false);
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
