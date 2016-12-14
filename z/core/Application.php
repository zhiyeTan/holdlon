<?php

namespace z\core;

use z;

class Application
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
	
	// 构造函数声明为private,防止直接创建对象
	public function __construct()
	{
		// 初始化路由器并解析当前请求
		Router::init()->parse();
		// 判断是否为异步操作入口
		if(Router::getEntryType() == 'async')
		{
			// 执行异步操作，完成后终止程序
			self::asyncRun();
			exit(0);
		}
		// 获得当前emca属性
		self::getEMCA();
		// 配置应用位置
    	if(isset(self::$e))
		{
			$entryMaps = unserialize(ENTRY_MAPS);
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
		}
		else
		{
			self::setAppPath();
		}
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
		// 绑定一个异步的关于访问时间和ip的日志记录操作的post请求
		Async::on('post', 'z\core\log', 'save', array('iplog', date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . Request::ip(0)));
		// 取得入口类型
		$type = Router::getEntryType();
		// 初始化cookie
		Cookie::init();
		// 初始化session
		Session::init();
		// 获取缓存
		$cache = Cache::init()->setName(Router::getCacheKey())->get();
		// 若不存在缓存数据，进行常规检查
		if(!$cache)
		{
			// 若是API请求则只检查MC
			if($type == 'api')
			{
				self::checkMC();
			}
			// 若是非API请求则对MVC都做检查
			else
			{
				self::checkMVC();
			}
			// 检查控制器，若存在并执行操作
			self::checkA('c');
		}
		// TODO 若存在缓存数据，则认为请求的参数是合法的，直接跳过常规检查，进行数据库初始化
		Model::init(z::$dbconfig);
		// 初始化响应对象
		Response::init();
		// 若存在缓存数据，传给$content，并设置不更新缓存
		if($cache)
		{
			$content = &$cache;
			Response::setCache(0);
		}
		// 若不存在缓存数据，从模型中读取数据
		else
		{
			// 检查模型操作，若存在并执行
			$data = self::checkA('m');
			// 初始化响应对象并设置是否使用缓存 [默认使用缓存]
			Response::setCache(isset($data['cache']) ? $data['cache'] : 1);
			// 处理API请求
			if($type == 'api')
			{
				// 移除cache状态
				unset($data['cache']);
				// 格式化数据
				Response::formatToJSON($data);
				// 设置响应的内容类型为jsonp或json
				Response::setContentType(isset($_GET['callback']) ? 'javascript' : 'json');
				// 传给$content
				$content = &$data;
			}
			// 处理非API请求
			else
			{
				// 组装模板路径
				$template = APP_PATH . 'views' . Z_DS . self::$m . Z_DS . self::$c . Z_DS . self::$a . '.php';
				// 打开缓冲区
				ob_start();
				// 载入模板
				require $template;
				// 释放变量
				unset($data);
				// 获得缓冲内容并清空
				$content = ob_get_clean();
			}
		}
		// 把$a指向可能存在的记录更新操作并尝试执行
		/**
		 * TODO 这里有一个原始的想法
		 *      就是用一个类的成员去记录需要操作的对象、方法以及参数
		 *      然后在输出响应之后再去遍历执行这些操作
		 *      优点是能把所有非可视化的操作优先执行，输出给用户，提高响应速度
		 *      缺点是代码较为混乱，且不好分离访问性的更新操作（如点击数等）和其他操作（如表单等）
		 * 
		 *      目前的做法是统一访问性的更新操作写在逻辑模型里面，再去尝试执行之
		 *      方法名由原action+Record组成
		 */
		self::$a .= 'Record';
		self::checkA('m', false);
		// 触发可能存在的异步请求
		Async::trigger();
		// 发送响应
		Response::send($content);
		exit(0);
	}
	
	// 错误处理
	private static function exception()
	{
		$content = '<div style="padding: 24px 48px;"><h1>&gt;_&lt;|||</h1><p>' . self::$exceptionMaps[self::$exceptionType] . '</p>';
		Response::init()->setExpire(0)->setCache(0)->send($content);
		exit(0);
	}
	
	// 检查操作
	private static function checkA($which = 'c', $throw = true)
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
			if(!$throw)
			{
				return false;
			}
			// 友好地提示异常
			self::$exceptionType = $which == 'c' ? C_ACTIONNOTFOUND : M_ACTIONNOTFOUND;
			self::exception();
		}
		else
		{
			// 存在操作则执行并返回结果
			$result = $object->$method();
			unset($object);
			return $result;
		}
	}
	
	// 检测mvc文件和文件夹是否存在 [针对非API入口]
	private static function checkMVC()
	{
		if(!self::checkE())
		{
			self::$exceptionType = ENTRYNOTFOUND;
		}
		elseif(!self::checkM('m'))
		{
			self::$exceptionType = M_MODULENOTFOUND;
		}
		elseif(!self::checkM('v'))
		{
			self::$exceptionType = V_MODULENOTFOUND;
		}
		elseif(!self::checkM('c'))
		{
			self::$exceptionType = C_MODULENOTFOUND;
		}
		elseif(!self::checkC('m'))
		{
			self::$exceptionType = M_CONTRALLERNOTFOUND;
		}
		elseif(!self::checkC('v'))
		{
			self::$exceptionType = V_GROUPNOTFOUND;
		}
		elseif(!self::checkC('c'))
		{
			self::$exceptionType = C_CONTRALLERNOTFOUND;
		}
		elseif(!self::checkTemplate())
		{
			self::$exceptionType = V_TEMPLATENOTFOUND;
		}
		if(self::$exceptionType !== 0)
		{
			self::exception();
		}
	}
	
	// 检测mc文件和文件夹是否存在 [针对API入口]
	private static function checkMC()
	{
		if(!self::checkM('m'))
		{
			self::$exceptionType = M_MODULENOTFOUND;
		}
		elseif(!self::checkM('c'))
		{
			self::$exceptionType = C_MODULENOTFOUND;
		}
		elseif(!self::checkC('m'))
		{
			self::$exceptionType = M_CONTRALLERNOTFOUND;
		}
		elseif(!self::checkC('c'))
		{
			self::$exceptionType = C_CONTRALLERNOTFOUND;
		}
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
	private static function checkE()
	{
		$filename = ENTRY_PATH . Z_DS . self::$e . '.php';
		if(is_file($filename))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 检查模块是否存在
	 * @param: string $which [m/v/c]
	 * @return bool
	 */
	private static function checkM($which = 'c')
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
		if(is_dir($filename))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 检查控制器/对应逻辑模型/对应模板组是否存在
	 * @param: string $which [m/v/c]
	 * @return bool
	 */
	private static function checkC($which = 'c')
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
		if(($which == 'v' && is_dir($filename)) || ($which != 'v' && is_file($filename)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// 检查模板文件是否存在
	private static function checkTemplate()
	{
		$filename = APP_PATH . 'views' . Z_DS . self::$m . Z_DS . self::$c . Z_DS . self::$a . '.php';
		if(is_file($filename))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 
	 */
	private static function asyncRun()
	{
		// TODO 此处添加对于GET请求的处理（目前尚未出现需要通过GET请求去处理的操作）
		if(empty($_POST['data']))
		{
			return;
		}
		$data = unserialize($_POST['data']);
		foreach($data as $act)
		{
			$object = new $act['objectName']();
			call_user_func_array(array($object, $act['methodName']), $act['args']);
			unset($object);
		}
	}
}
