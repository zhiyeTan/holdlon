<?php

namespace z\core;

use z\lib\Core as Core;

/**
 * 路由策略
 * 使用URL重写规则后，到达php-fpm的URL将形如："协议名://主机名/index.php?s=..."
 * 包括以下4种路由模式：
 * 0、协议名://主机名/入口名.php?m=模块名称&c=控制器名称&其他参数...
 * 1、协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value....html
 * 2、协议名://主机名/模块名称(index时省略)/六位字符串.html
 * 3、协议名://主机名/入口名/模块名称/控制器名称/key/value/key/value...
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 */
class Router
{
	/**
	 * 路由模式
	 */
	private static $pattern;
	/**
	 * 主机名
	 */
	private static $domain;
	/**
	 * 模式2短地址存放位置
	 */
	private static $urlMaps;
	/**
	 * 作者密钥
	 */
	private static $authorKey = 'zhiyeTan';
    /**
	 * 基本字符
	 */
    private static $baseChar = "0aAbBcC1dDeEfF2gGhHiI3jJkKlL4mMnNoO5pPqQrR6sStTuU7vVwWxX8yYzZ9";
	/**
	 * 保存实例在此属性中
	 */
	private static $_instance;
	
	/**
	 * 私有构造函数
	 */
	private function __construct()
	{
		self::$pattern = ROUTE_PATTERN;
		self::$domain = Request::domain();
		// 若路由为短地址模式，设置路径并检查
		if(ROUTE_PATTERN == 2)
		{
			self::$urlMaps = APP_PATH . Z_DS . 'shortUrlMaps' . Z_DS;
			// 检查文件夹
			Core::chkFolder(self::$urlMaps);
		}
	}
	
	/**
	 * 单例构造方法
	 * @access public
	 * @return this
	 */
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	/**
	 * 禁止用户复制对象实例
	 */
	public function __clone()
	{
		trigger_error('Clone is not allow' , E_USER_ERROR);
	}
	
	/**
	 * 创建url
	 * @access  public
	 * @param   mixed    $mixed   可能是数组或url参数
	 * @return  url
	 */
	public static function create($mixed)
	{
		if(empty($mixed))
		{
			return false;
		}
		// 先赋值主机名
		$url = self::$domain;
		// 确保拿到数组形式的参数
		$queryArr = array();
		if(is_array($mixed))
		{
			$queryArr = $mixed;
		}
		else
		{
			parse_str($mixed, $queryArr);
		}
		// 确保包含必要参数
		$queryArr['e'] = empty($queryArr['e']) ? empty($_GET['e']) ? 'index' : $_GET['e'] : $queryArr['e'];
		$queryArr['m'] = empty($queryArr['m']) ? empty($_GET['m']) ? 'index' : $_GET['m'] : $queryArr['m'];
		$queryArr['c'] = empty($queryArr['c']) ? empty($_GET['c']) ? 'index' : $_GET['c'] : $queryArr['c'];
		// 根据路由模式构造URL
		// 将API/Async请求强制使用路由模式3解析
		if(isset($queryArr['e']) && ($queryArr['e'] == API_ENTRY || $queryArr['e'] == 'async'))
		{
			self::$pattern = 3;
		}
		switch(self::$pattern)
		{
			case 1:
				// URL形式 [协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value....html]
				// 最佳三层结构，强烈建议使用此模式
				$url .= '/' . ($queryArr['m'] == 'index' ? '' : $queryArr['m'] . '/');
				$url .= $queryArr['e'] . '-' . $queryArr['c'];
				unset($queryArr['m'], $queryArr['e'], $queryArr['c']);
				$elseQueryStr = preg_replace('[=|&]', '-', http_build_query($queryArr));
				$url .= ($elseQueryStr ? '-' . $elseQueryStr : '') . '.html';
				break;
			case 2:
				// URL形式 [协议名://主机名/模块名称(index时省略)/六位字符串.html]
				// 此模式产生额外的文件读写消耗，建议仅在对地址长度有强烈需求的时候使用
				// 转成字符串后再加密一下
				$queryStr = http_build_query($queryArr);
				$hashStr = md5(self::$authorKey . $queryStr);
				// 将加密串分成4段计算
				for($i = 0; $i < 4; ++$i)
				{
					// 将截取每段字符并转为10进制数组，再与0x3fffffff做位与运算（即把30位以后的字符归零）
					$idx = hexdec(substr($hashStr, $i << 2, 4)) & 0x3fffffff;
					// 生成6位短链接
					$tmp_str = '';
					for($j = 0; $j < 6; ++$j)
					{
						// 与$basechar的最大下标0x0000003d（即61）做位与运算得到新的数组下标后取得对应的值
						$tmp_str .= self::$baseChar[$idx & 0x0000003d];
						// 右移处理
						$idx = $idx >> 5;
					}
					// 判断映射是否有效
					$tmpFileName = self::$urlMaps . $tmp_str;
					if(!is_file($tmpFileName) || (is_file($tmpFileName) && Core::fastReadFile($tmpFileName) === $queryStr))
					{
						$url .= '/' . ($queryArr['m'] == 'index' ? '' : $queryArr['m'] . '/') . $tmp_str . '.html';
						// 如果未存在映射关系则建立映射
						Core::fastWriteFile($tmpFileName, $queryStr, true, false);
						break;
					}
				}
				break;
			case 3:
				// URL形式 [协议名://主机名/入口文件/模块名称/控制器名称/key/value/key/value...]
				// 强制API使用此模式，且不建议非API入口使用此模式
				$url .= '/' . $queryArr['e'] . '/' . $queryArr['m'] . '/' . $queryArr['c'] . '/';
				unset($queryArr['e'], $queryArr['m'], $queryArr['c']);
				$url .= strtr(http_build_query($queryArr), '=&', '//');
				break;
			default:
				$url .= '/index.php?' . http_build_query($queryArr);
		}
		return $url;
	}
	
	/**
	 * 解析url把参数放到$_GET中
	 * @access public
	 */
	public static function parse()
	{
		// 取得完整的URL参数
		$queryString = strtr($_SERVER['QUERY_STRING'], array('.'=>'@@'));
		// 修正s参数为e参数
		$queryString = strtr($queryString, array('s='=>'e='));
		// 把URL参数处理成数组
		if($queryString)
		{
			if(strpos($queryString, '/'))
			{
				$queryArr = explode('/', trim($queryString, '/'));
			}
			else
			{
				parse_str($queryString, $queryArr);
			}
			$queryArr = array_map(
				function($v)
				{
					if(strpos($v, '@@') !== false)
					{
						$tmp = explode('@@', $v);
						$v = $tmp[0];
					}
					if(strpos($v, '=') !== false)
					{
						$tmp = explode('=', $v);
						$v = $tmp[1];
					}
					return $v;
				}, $queryArr
			);
			// 重置非默认路由模式的键名
			$queryArr = self::$pattern ? array_values($queryArr) : $queryArr;
		}
		// 没有URL参数时
		else
		{
			$tmps = explode('.', basename($_SERVER['SCRIPT_NAME']));
			$queryArr = array($tmps[0]);
		}
		// 把不合法的get参数清空
		if(self::$pattern)
		{
			$_GET = array();
		}
		// 将API请求强制使用路由模式3解析
		if(isset($queryArr[0]) && $queryArr[0] == API_ENTRY)
		{
			self::$pattern = 3;
		}
		// 根据路由模式进行对应的URL解析
		switch(self::$pattern)
		{
			case 1:
				// 协议名://主机名/模块名称(index时省略)/入口名-控制器名称-key-value-key-value....html
				$queryStr = isset($queryArr[1]) ? $queryArr[1] : $queryArr[0];
				$_GET['m'] = isset($queryArr[1]) ? $queryArr[0] : 'index';
				// 拆解为数组
				$secArr = explode('-', $queryStr);
				$_GET['e'] = isset($secArr[0]) ? $secArr[0] : 'index';
				$_GET['c'] = isset($secArr[1]) ? $secArr[1] : 'index';
				// 把奇数元素作为$_GET的键名，把偶数元素作为$_GET的值
				foreach($secArr as $k => $v)
				{
					if($k > 1 && $k % 2 == 0)
					{
						$_GET[$v] = isset($secArr[$k+1]) ? $secArr[$k+1] : '';
					}
				}
				break;
			case 2:
				// 协议名://主机名/模块名称(index时省略)/六位字符串.html
				$queryStr = isset($queryArr[1]) ? $queryArr[1] : $queryArr[0];
				$fileName = self::$urlMaps . $queryStr;
				// 如存在短地址地图，取出数据并合并到$_GET中
				$data = Core::fastReadFile($fileName);
				if($data !== false)
				{
					parse_str($data, $query_arr);
					foreach($query_arr as $kk => $vv)
					{
						$_GET[$kk] = $vv;
					}
				}
				// 不存在映射则认为是一个入口文件名
				else
				{
					$_GET['e'] = $queryStr;
				}
				break;
			case 3:
				// 协议名://主机名/入口文件/模块名称/控制器名称/key/value/key/value...
				$_GET['e'] = isset($queryArr[0]) ? $queryArr[0] : 'index';
				$_GET['m'] = isset($queryArr[1]) ? $queryArr[1] : 'index';
				$_GET['c'] = isset($queryArr[2]) ? $queryArr[2] : 'index';
				unset($queryArr[0], $queryArr[1], $queryArr[2]);
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
			default:
				$_GET = array_merge($queryArr, $_GET);
		}
		// 确保必要参数有效
		$_GET['e'] = !empty($_GET['e']) ? $_GET['e'] : 'index';
		$_GET['m'] = !empty($_GET['m']) ? $_GET['m'] : 'index';
		$_GET['c'] = !empty($_GET['c']) ? $_GET['c'] : 'index';
		// 加载入口与应用位置映射
		$entryMaps = unserialize(ENTRY_MAPS);
		// 修正入口对应的应用位置
		if($_GET['e'] == API_ENTRY)
		{
			$appPath = dirname(ENTRY_PATH) . Z_DS . API_DIR . Z_DS;
		}
		// 当建立一个专门的应用目录来处理异步操作时，会覆盖下一个 elseif，也就是说不再默认用当前应用目录来处理
		elseif(!empty($entryMaps[$_GET['e']]))
		{
			$appPath = dirname(ENTRY_PATH) . Z_DS . $entryMaps[$_GET['e']] . Z_DS;
		}
		else
		{
			// 渲染404视图
			$controller = new Controller();
			$controller->displayError(404, '入口异常！');
		}
		define('APP_PATH', $appPath);
		// 设置当前请求的唯一缓存标识
		Cache::init();
		// 设置静态缓存文件名
		Cache::setCacheName(Cache::formatCacheTag($_GET));
		// 设置动态缓存文件名
		Cache::setCacheName(Cache::formatCacheTag($_GET, false), false);
	}
	
}
