<?php

namespace z\core;

use z;

class Driver
{
	// 异常处理类型
	private static $exceptionType = 0;
	
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
		ENTRYNOTFOUND			=> '无效的入口！',
		M_MODULENOTFOUND		=> '无效的数据模块！',
		V_MODULENOTFOUND		=> '无效的视图模块！',
		C_MODULENOTFOUND		=> '无效的控制器模块！',
		M_CONTRALLERNOTFOUND	=> '无效的M控制器！',
		V_GROUPNOTFOUND			=> '无效的视图分组！',
		C_CONTRALLERNOTFOUND	=> '无效的C控制器！',
		M_ACTIONNOTFOUND		=> '无效的M操作！',
		V_TEMPLATENOTFOUND		=> '无效的视图模板！',
		C_ACTIONNOTFOUND		=> '无效的C操作！',
	);
	
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		// 初始化路由器并解析当前请求
		Router::init()->parse();
		// 初始化cookie
		Cookie::init();
		// 设置当前emca属性
		self::setEMCA();
		// 配置应用位置
    	if(isset(self::$e))
		{
			$entryMaps = unserialize(ENTRY_MAPS);
			// 若存在映射关系则设置为指定应用位置
			if(isset($entryMaps[self::$e])) define('APP_PATH', dirname(ENTRY_PATH) . Z_DS . $entryMaps[self::$e] . Z_DS); 
			// 否则设置为当前路径
			else self::setAppPath();
		}
		else self::setAppPath();
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
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	// 保存EMCA到类属性中
	private static function setEMCA()
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
	public function trigger()
	{
		// 检查缓存，若可用则输出
		Cache::init()->setName(Router::getCacheKey())->get();
		// 分别对API和站点请求做检查处理
		if(Router::isAPI()) self::checkMC();
		else self::checkMVC();
		// 检查控制器，若存在并执行操作
		self::checkA('c');
		// 初始化数据库
		Model::init(z::$dbconfig);
		// 检查模型操作，若存在并执行
		$data = self::checkA('m');
		// 分别对API和站点请求做输出处理
		if(Router::isAPI()) Response::init()->sendJSON($data);
		else self::render($data);
	}
	
	// 渲染视图
	public static function render($data = null)
	{
		// 设置是否使用缓存 [默认使用缓存]
		$cache = isset($data['cache']) ? $data['cache'] : 1;
		// 组装模板路径
		$template = APP_PATH . 'views' . Z_DS . self::$m . Z_DS . self::$c . Z_DS . self::$a . '.php';
		// 打开缓冲区
		ob_start();
		// 载入模板
		require $template;
		// 获得缓冲内容并清空
		$content = ob_get_clean();
		// 发送响应
		Response::init()->setCache($cache)->send($content);
	}
	
	// 错误处理
	public static function exception()
	{
		$content = '<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . self::$exceptionMaps[self::$exceptionType] . '</p>';
		Response::init()->setExpire(0)->setCache(0)->send($content);
	}
	
	// 检查操作
	public static function checkA($which = 'c')
	{
		// 定义别名
		$alias = '\\' . ($which == 'c' ? 'contrallers' : 'models') . '\\' . self::$m . '\\' . self::$c;
		// 定义操作名
		$method = self::$a;
		// 初始化对象
		$object = new $alias();
		// 检查操作
		if(!method_exists($object, $method))
		{
			// 若不存在模型操作，友好地提示异常
			self::$exceptionType = $which == 'c' ? C_ACTIONNOTFOUND : M_ACTIONNOTFOUND;
			self::exception();
		}
		// 存在则执行该操作
		// 返回执行结果
		return $object->$method();
	}
	
	// 检测mvc文件和文件夹是否存在 [针对非API入口]
	public static function checkMVC()
	{
		if(!self::checkE()) self::$exceptionType = ENTRYNOTFOUND;
		elseif(!self::checkM('m')) self::$exceptionType = M_MODULENOTFOUND;
		elseif(!self::checkM('v')) self::$exceptionType = V_MODULENOTFOUND;
		elseif(!self::checkM('c')) self::$exceptionType = C_MODULENOTFOUND;
		elseif(!self::checkC('m')) self::$exceptionType = M_CONTRALLERNOTFOUND;
		elseif(!self::checkC('v')) self::$exceptionType = V_GROUPNOTFOUND;
		elseif(!self::checkC('c')) self::$exceptionType = C_CONTRALLERNOTFOUND;
		elseif(!self::checkTemplate()) self::$exceptionType = V_TEMPLATENOTFOUND;
		
		if(self::$exceptionType !== 0)
		{
			self::exception();
		}
	}
	
	// 检测mc文件和文件夹是否存在 [针对API入口]
	public static function checkMC()
	{
		if(!self::checkM('m')) self::$exceptionType = M_MODULENOTFOUND;
		elseif(!self::checkM('c')) self::$exceptionType = C_MODULENOTFOUND;
		elseif(!self::checkC('m')) self::$exceptionType = M_CONTRALLERNOTFOUND;
		elseif(!self::checkC('c')) self::$exceptionType = C_CONTRALLERNOTFOUND;
		
		if(self::$exceptionType !== 0)
		{
			self::exception();
		}
	}
	
	/**
	 * 检查入口是否存在
	 * @param: string $which [m/v/c]
	 * @return bool
	 */
	public static function checkE()
	{
		$filename = ENTRY_PATH . Z_DS . self::$e . '.php';
		if(is_file($filename)) return true;
		else return false;
	}
	
	/**
	 * 检查模块是否存在
	 * @param: string $which [m/v/c]
	 * @return bool
	 */
	public static function checkM($which = 'c')
	{
		$filename = APP_PATH;
		switch($which)
		{
			case 'm':
				$filename .= 'models';
				break;
			case 'v':
				$filename .= 'views';
				break;
			default:
				$filename .= 'contrallers';
		}
		$filename .= Z_DS . self::$m;
		if(is_dir($filename)) return true;
		else return false;
	}
	
	/**
	 * 检查控制器/对应逻辑模型/对应模板组是否存在
	 * @param: string $which [m/v/c]
	 * @return bool
	 */
	public static function checkC($which = 'c')
	{
		$filename = APP_PATH;
		switch($which)
		{
			case 'm':
				$filename .= 'models' . Z_DS . self::$m . Z_DS . self::$c . '.php';
				break;
			case 'v':
				// 此处视图对应的是模板组文件夹而并非是文件
				$filename .= 'views' . Z_DS . self::$m . Z_DS . self::$c;
				break;
			default:
				$filename .= 'contrallers' . Z_DS . self::$m . Z_DS . self::$c . '.php';
		}
		if(($which == 'v' && is_dir($filename)) || ($which != 'v' && is_file($filename))) return true;
		else return false;
	}
	
	/**
	 * 检查模板文件是否存在
	 * @return bool
	 */
	public static function checkTemplate()
	{
		$filename = APP_PATH . 'views' . Z_DS . self::$m . Z_DS . self::$c . Z_DS . self::$a . '.php';
		if(is_file($filename)) return true;
		else return false;
	}
	
}
