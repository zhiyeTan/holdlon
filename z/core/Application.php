<?php

namespace z\core;

use z;

class Application
{
	// 异常处理类型
	private static $exceptionType = 0;
	// 是否使用缓存
	private static $noCache = 0;
	// 是否异步
	private static $isAsync = 0;
	// 入口名
	private static $e;
	// 模块名
	private static $m;
	// 控制器名
	private static $c;
	// 操作名
	private static $a;
	// 类型和提示映射
	private static $exceptionMaps = array(
		INVALID_ENTRY		=> '无效的入口！',
		INVALID_MODULE		=> '无效的模块！',
		INVALID_CONTRALLER	=> '无效的控制器！',
		INVALID_ACTION		=> '无效的操作！'
	);
	
	// 构造函数声明为private,防止直接创建对象
	public function __construct()
	{
		// 初始化路由器并解析当前请求
		Router::init()->parse();
		// 获得当前emca属性
		self::getEMCA();
		// 加载入口与应用位置映射
		$entryMaps = unserialize(ENTRY_MAPS);
		// 若为异步入口，重设$e为$m(异步请求下$m为提交异步请求的来源入口)
		if(self::$e == 'async')
		{
			self::$isAsync = 1;
			self::$e = self::$m;
		}
		// 若存在映射关系则设置为指定应用位置
		if(isset($entryMaps[self::$e]))
		{
			define('APP_PATH', dirname(ENTRY_PATH) . Z_DS . $entryMaps[self::$e] . Z_DS); 
		}
		// 否则设置为当前路径
		else
		{
			self::setAppPath();
		}
		// 判断是否为异步操作入口
		if(self::$isAsync)
		{
			// 执行异步操作，完成后终止程序
			Tunnel::runAsync();
			exit(0);
		}
		// 加载类名映射
		z::$classMap = require(Z_PATH . Z_DS . 'ClassMaps.php');
	}
	
	// 保存EMCA到类属性中
	private static function getEMCA()
	{
		self::$e = $_GET['e'];
		self::$m = $_GET['m'];
		self::$c = $_GET['c'];
		self::$a = $_GET['a'];
	}
	
	// 设置APP_PATH为当前路径CURR_PATH
	private static function setAppPath()
	{
		define('APP_PATH', CURR_PATH);
	}
	
	/**
	 * 调度控制器并判断是否具有aciton操作
	 * @param: string $aname 操作名
	 * 
	 */
	public static function run()
	{
		//*
		$i = Upload::init();
		
		$f = Array
		(
		    'imgurl' => Array
		        (
		            0 => Array
		                (
		                    'name' => '主图011.jpg',
		                    'type' => 'image/jpeg',
		                    'tmp_name' => 'F:\xampp\tmp\phpDE4A.tmp',
		                    'error' => '0',
		                    'size' => '136895'
		                ),
		
		            1 => Array
		                (
		                    'name' => '主图33006-9.jpg',
		                    'type' => 'image/jpeg',
		                    'tmp_name' => 'F:\xampp\tmp\phpDE5B.tmp',
		                    'error' => '0',
		                    'size' => '182224'
		                )
		
		        ),
		
		    'cover' => Array
		        (
		            0 => Array
		                (
		                    'name' => '主图011-2.jpg',
		                    'type' => 'image/jpeg',
		                    'tmp_name' => 'F:\xampp\tmp\phpDE6B.tmp',
		                    'error' => '0',
		                    'size' => '122483'
		                )
		
		        )
		
		);
		//echo $i->upload($_FILES['img_url']);
		echo '<pre>';
		print_r($i::uploadFileBatch($f));
		exit;
		//*/
		
		
		// 绑定一个异步的关于访问时间和ip的日志记录操作的post请求到通道中
		Tunnel::onAsync('post', 'z\core\log', 'save', 1, array('iplog', date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . Request::ip(0)));
		// 初始化cookie
		Cookie::init();
		// 初始化session
		Session::init();
		// 初始化响应对象
		Response::init();
		// 若不使用缓存则设置缓存为false，否则获取真实的缓存
		$cache = self::$noCache ? !1 : Cache::init()->setName(Router::getCacheKey())->get();
		// 分别获得入口、模块、控制器文件名
		$entryFileName = ENTRY_PATH . Z_DS . self::$e . '.php';
		$moduleFileName = APP_PATH . 'contrallers' . Z_DS . self::$m;
		$contrallerFileName = $moduleFileName . Z_DS . self::$c . '.php';
		// 分别获得类别名和操作名
		$alias = '\\contrallers\\' . self::$m . '\\' . self::$c;
		$method = self::$a;
		// 判断缓存状态
		if(!$cache)
		{
			// 检查文件是否存在
			self::chkFile($entryFileName, 'file',   INVALID_ENTRY);
			self::chkFile($moduleFileName, 'folder', INVALID_MODULE);
			self::chkFile($contrallerFileName, 'file', INVALID_CONTRALLER);
			// 如无异常则初始化控制器并检查操作是否存在
			if(!self::$exceptionType)
			{
				$object = new $alias();
				if(!method_exists($object, $method))
				{
					// 操作无效时标记异常类型
					self::$exceptionType = INVALID_ACTION;
				}
			}
			// 检查异常
			if(self::$exceptionType)
			{
				// 渲染404视图
				$content = Contraller::render404(self::$exceptionMaps[self::$exceptionType]);
				// 发送404响应
				Response::setExpire(0)->setCache(0)->setCode(404)->send($content);
				exit(0);
			}
			$content = $object->$method();
			unset($object);
		}
		else
		{
			$content = &$cache;
			Response::setCache(0);
			Response::setCode(304);
		}
		// TODO 在控制器中设置是否使用缓存、格式化JSON/JSONP、设置响应内容的类型
		// 发送响应
		Response::send($content);
		// 尝试执行可能存在的延后操作
		$delayMethod = $method . 'DelayAction';
		$object = new $alias();
		if(method_exists($object, $delayMethod))
		{
			$object->$delayMethod();
		}
		// 执行放进通道中的操作
		Tunnel::trigger();
		exit(0);
	}
	
	
	/**
	 * 检查入口是否存在
	 * @param: string $filename 文件名
	 * @param: string $fileType 文件类型
	 * @param: string $errorType 错误类型
	 */
	private static function chkFile($filename, $fileType, $errorType)
	{
		if(self::$exceptionType)
		{
			return;
		}
		if($fileType == 'folder')
		{
			if(!is_dir($filename))
			{
				self::$exceptionType = $errorType;
			}
		}
		else
		{
			if(!is_file($filename))
			{
				self::$exceptionType = $errorType;
			}
		}
	}
	
}
