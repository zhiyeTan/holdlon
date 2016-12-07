<?php

namespace z\core;

/**
 * 路由器
 * 到达php-fpm的URL如下：
 * 协议名://主机名/index.php?s=*
 */
class Router
{
	// 路由模式
	private static $pattern;
	
	// 主机名
	private static $domain;
	
	// 静态主机
	private static $staticDomain;
	
	// 当前脚本名
	private static $selfScript;
	
	// 保存例实例在此属性中
	private static $_instance;
	
	// 模式2短地址存放位置
	private static $urlMaps;
	
	// 作者密钥
	private static $authorKey = 'zhiyeTan';
	
    // 基本字符
    private static $baseChar = "0aAbBcC1dDeEfF2gGhHiI3jJkKlL4mMnNoO5pPqQrR6sStTuU7vVwWxX8yYzZ9";
	
	// 是否api接口
	private static $isAPI = false;
	
	// 保存唯一缓存标识
	private static $cacheKey;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct()
	{
		self::$pattern = ROUTE_PATTERN;
		self::$domain = Request::domain();
		self::$staticDomain = Request::staticDomain();
		self::$selfScript = basename($_SERVER['SCRIPT_NAME']);
		self::$urlMaps = Z_PATH . Z_DS . 'maps' . Z_DS;
		// cache文件夹不存在则创建并赋值权限
		if(!is_dir(self::$urlMaps))
		{
			@mkdir(self::$urlMaps);
			@chmod(self::$urlMaps, 0777);
		}
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
	
	// 返回唯一缓存标识
	public static function getCacheKey()
	{
		return self::$cacheKey;
	}
	
	// 是否API请求
	public static function isAPI()
	{
		return self::$isAPI;
	}
	
	/**
	 * 创建url
	 * @param: mixed $mixed 可能是数组或url参数
	 * @param: number $static 是否静态文件
	 * @return url
	 */
	public function create($mixed, $static = 0)
	{
		if(empty($mixed)) return false;
		
		// 先赋值主机名
		$url = $static ? self::$staticDomain : self::$domain;
		
		// 确保拿到数组形式的参数
		if(is_array($mixed)) $query_arr = $mixed;
		else parse_str($mixed, $query_arr);
		
		// 确保非默认模式的参数中包含入口文件
		if(self::$pattern != 0 && !isset($query_arr['e']))
		{
			$query_arr['e'] = isset($_GET['e']) ? $_GET['e'] : 'index';
		}
		
		switch(self::$pattern)
		{
			case 1:
				// URL形式 [协议名://主机名/模块名称(index时省略)/入口文件名-控制器名称-操作名称-key-value-key-value....html]
				// 最佳三层结构，强烈建议使用此模式
				$url .= '/' . ($query_arr['m'] == 'index' ? '' : $query_arr['m'] . '/');
				$url .= $query_arr['e'] . '-' . $query_arr['c'] . '-' . $query_arr['a'];
				unset($query_arr['m'], $query_arr['e'], $query_arr['c'], $query_arr['a']);
				$elseQueryStr = preg_replace('[=|&]', '-', http_build_query($query_arr));
				$url .= ($elseQueryStr ? '-' . $elseQueryStr : '') . '.html';
				break;
			case 2:
				// URL形式 [协议名://主机名/模块名称(index时省略)/六位字符串.html]
				// 此模式产生额外的文件读写消耗，建议仅在对地址长度有强烈需求的时候使用
				// 转成字符串后再加密一下
				$queryStr = http_build_query($query_arr);
				$hashStr = md5(self::$authorKey . $queryStr);
				// 将加密串分成4段计算
				for($i = 0; $i < 4; $i++)
				{
					// 将截取每段字符并转为10进制数组，再与0x3fffffff做位与运算（即把30位以后的字符归零）
					$idx = hexdec(substr($hashStr, $i << 2, 4)) & 0x3fffffff;
					// 生成6位短链接
					$tmp_str = '';
					for($j = 0; $j < 6; $j++)
					{
						// 与$basechar的最大下标0x0000003d（即61）做位与运算得到新的数组下标后取得对应的值
						$tmp_str .= self::$baseChar[$idx & 0x0000003d];
						// 右移处理
						$idx = $idx >> 5;
					}
					// 判断映射是否有效
					$tmpFileName = self::$urlMaps . $tmp_str;
					if(!is_file($tmpFileName) || (is_file($tmpFileName) && self::read($tmpFileName) === $queryStr))
					{
						$url .= '/' . ($query_arr['m'] == 'index' ? '' : $query_arr['m'] . '/') . $tmp_str . '.html';
						// 如果未存在映射关系则建立映射
						if(!is_file($tmpFileName))
						{
							$file = fopen($tmpFileName, "w");
							if(flock($file, LOCK_EX))
							{
								fwrite($file, serialize($queryStr));
								flock($file, LOCK_UN);
								fclose($file);
							}
						}
						break;
					}
				}
				break;
			case 3:
				// URL形式 [协议名://主机名/入口文件/模块名称/控制器名称/操作名称/key/value/key/value...]
				// 强制API使用此模式，且不建议非API入口使用此模式
				$url .= '/' . $query_arr['e'] . '/' . $query_arr['m'] . '/' . $query_arr['c'] . '/' . $query_arr['a'] . '/';
				unset($query_arr['e'], $query_arr['m'], $query_arr['c'], $query_arr['a']);
				$url .= preg_replace('[=|&]', '/', http_build_query($query_arr));
				break;
			default:
				$url .= '/' . (isset($query_arr['e']) ? $query_arr['e'] . '.php' : self::$selfScript) . '?' . http_build_query($query_arr);
		}
		return $url;
	}
	
	/**
	 * 解析url把参数放到$_GET中
	 * 
	 */
	public function parse()
	{
		// 把s参数处理成数组
		if(isset($_GET['s']))
		{
			$queryArr = explode('/', trim(str_replace('.html', '', $_GET['s']), '/'));
		}
		
		// 将API请求强制重置为路由1模式并标记为API接口
		if(isset($queryArr[0]) && $queryArr[0] == 'api')
		{
			self::$pattern = 3;
			self::$isAPI = true;
		}
		
		// 把不合法的get参数清空
		if(self::$pattern) $_GET = array();
		self::$pattern = 2;
		// 根据路由模式进行对应的URL解析
		switch(self::$pattern)
		{
			case 1:
				$queryStr = isset($queryArr[1]) ? $queryArr[1] : $queryArr[0];
				$_GET['m'] = isset($queryArr[1]) ? $queryArr[0] : 'index';
				// 拆解为数组
				$secArr = explode('-', $queryStr);
				$_GET['e'] = isset($secArr[0]) ? $secArr[0] : 'index';
				$_GET['c'] = isset($secArr[1]) ? $secArr[1] : 'index';
				$_GET['a'] = isset($secArr[2]) ? $secArr[2] : 'index';
				// 把奇数元素作为$_GET的键名，把偶数元素作为$_GET的值
				foreach($secArr as $k => $v)
				{
					if($k > 2 && $k % 2 == 1)
					{
						$_GET[$v] = isset($secArr[$k+1]) ? $secArr[$k+1] : '';
					}
				}
				break;
			case 2:
				$queryStr = isset($queryArr[1]) ? $queryArr[1] : $queryArr[0];
				$fileName = self::$urlMaps . $queryStr;
				// 如存在短地址地图，取出数据并合并到$_GET中
				if(is_file($fileName))
				{
					$data = self::read($fileName);
					if($data !== false)
					{
						parse_str($data, $query_arr);
						foreach($query_arr as $kk => $vv)
						{
							$_GET[$kk] = $vv;
						}
					}
				}
				// 不存在映射则认为是一个入口文件名
				else $_GET['e'] = $queryStr;
				break;
			case 3:
				$_GET['e'] = isset($queryArr[0]) ? $queryArr[0] : 'index';
				$_GET['m'] = isset($queryArr[1]) ? $queryArr[1] : 'index';
				$_GET['c'] = isset($queryArr[2]) ? $queryArr[2] : 'index';
				$_GET['a'] = isset($queryArr[3]) ? $queryArr[3] : 'index';
				unset($queryArr[0], $queryArr[1], $queryArr[2], $queryArr[3]);
				// 重置索引后把偶数元素作为$_GET的键名，把奇数元素作为$_GET的值
				$queryArr = array_values($queryArr);
				foreach($queryArr as $k => $v)
				{
					if($k % 2 == 0)
					{
						$_GET[$v] = isset($queryArr[$k+1]) ? $queryArr[$k+1] : '';
					}
				}
				break;
			default: ;
		}
		self::setDefaultEMCA();
		// 设置当前请求的唯一标识
		self::$cacheKey = http_build_query($_GET);
	}
	
	/**
	 * 读取短地址对应的url参数
	 * @return string
	 */
	public static function read($name)
	{
		$file = fopen($name, "r");
		if(flock($file, LOCK_SH))
		{
			$data = unserialize(fread($file, filesize($name)));
			flock($file, LOCK_UN);
			fclose($file);
			return $data;
		}
		return false;
	}
	
	/**
	 * 赋值默认的emca参数
	 */
	private static function setDefaultEMCA()
	{
		$_GET['e'] = isset($_GET['e']) ? $_GET['e'] : 'index';
		$_GET['m'] = isset($_GET['m']) ? $_GET['m'] : 'index';
		$_GET['c'] = isset($_GET['c']) ? $_GET['c'] : 'index';
		$_GET['a'] = isset($_GET['a']) ? $_GET['a'] : 'index';
	}
	
}
