<?php

namespace z\core;

/**
 * 模板机制
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Template
{
	// 是否使用动态缓存
	private static $noCache = true;
	// 是否使用实时的部件数据
	private static $dynainfo = true;
	// 模板文件的后缀名
	private static $templateSuffix = '.tpl';
	// 部件视图的后缀名
	private static $widgetSuffix = '.mdl';
	// 错误信息
	private static $errstr;
	// 数据栈
	private $data = array();
	/**
	 * 赋值到数据栈中
	 * @access public
	 * @param  string  $key    键名或键值对数组
	 * @param  string  $value  键值（$key为非数组时有效）
	 */
	public function assign($key, $value = null)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				$this->data[$k] = $v;
			}
		}
		else
		{
			$this->data[$key] = $value;
		}
	}
	/**
	 * 渲染模板
	 */
	public function render($name)
	{
		$realTime = false;
		// 获取动态缓存
		$dynamic = Cache::get(false);
		// 读取模板文件
		$tplPath = APP_PATH . 'views' . Z_DS . $_GET['m'] . Z_DS . $name . self::$templateSuffix;
		// 若设置不使用缓存则强制读取并重新编译模板
		if(!self::$noCache || !$dynamic)
		{
			// 读取模板内容
			$content = file_get_contents($tplPath);
			// 编译模板内容
			$content = $this->complie($content);
			// 保存动态缓存并获得其路径
			$dynamic = Cache::save($content, false);
			// 标记已经是实时的部件数据
			$realTime = true;
		}
		// 若设置使用实时数据且尚未实时读取时，获得模板中包含的部件并获取数据
		if(self::$dynainfo && !$realTime)
		{
			$this->getRealTimeData(file_get_contents($tplPath));
		}
		// 将数组变量导入到当前的符号表
		extract($this->data);
		// 打开缓冲区
		ob_start();
		// 载入模板
		require $dynamic;
		// 返回缓冲内容并清空
		//return ob_get_clean();
		Response::setContent(ob_get_clean());
	}
	/**
	 * 编译模板内容
	 * @access public
	 * @param  sting   $content  模板内容
	 * @return string
	 */
	public function complie($content)
	{
		$content = $this->parseWidget($content);
		$content = $this->parseLanguage('var', $content);
		$content = $this->parseLanguage('foreach', $content);
		$content = $this->parseLanguage('if', $content);
		$content = $this->parseLanguage('elseif', $content);
		$content = $this->parseLanguage('else', $content);
		$content = $this->parseLanguage('end', $content);
		return $content;
	}
	/**
	 * 解析部件
	 * @access private
	 * @param  sting    $content  模板内容
	 * @return string
	 */
	private function parseWidget($content)
	{
		$pattern = array();
		$widgetPath = APP_PATH . 'widget' . Z_DS;
		preg_match_all('/\{\\$widgetView_([a-zA-Z]*)\}/', $content, $res);
		foreach($res[1] as $k => $v)
		{
			$tmpContrallerPath = $widgetPath . 'contrallers' . Z_DS . $v . '.php';
			$tmpViewPath = $widgetPath . 'views' . Z_DS . $v . self::$widgetSuffix;
			// 允许部件控制器为空
			if(is_file($tmpContrallerPath))
			{
				include $tmpContrallerPath;
			}
			$pattern[$res[0][$k]] = file_get_contents($tmpViewPath);
		}
		// 取出部件数据并赋值到数据栈中
		$this->assign(Widget::getWidgetData());
		return strtr($content, $pattern);
	}
	/**
	 * 解析模板语言
	 * @access private
	 * @param  sting    $type     语言类型
	 * @param  sting    $content  模板内容
	 * @return string
	 */
	private function parseLanguage($type, $content)
	{
		switch($type)
		{
			case 'var':
				$target = '/\{\\$.*?\}/';
				$action = 'replaceVar';
				break;
			case 'foreach':
				$target = '/\{foreach.*?\}/i';
				$action = 'replaceForeach';
				break;
			case 'if':
				$target = '/\{if.*?\}/i';
				$action = 'replaceIf';
				break;
			case 'elseif':
				$target = '/\{elseif.*?\}/i';
				$action = 'replaceElseif';
				break;
			case 'else':
				$target = '/\{else.*?\}/i';
				$action = 'replaceElse';
				break;
			case 'end':
				$target = '/\{\/.*?\}/i';
				$action = 'replaceEnd';
				break;
		}
		preg_match_all($target, $content, $res);
		$pattern = array();
		if(!empty($res[0]))
		{
			foreach($res[0] as $k => $v)
			{
				// 组成替换数组
				$pattern[$v] = $this->$action($v);
			}
		}
		return strtr($content, $pattern);
	}
	/**
	 * 替换变量
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function replaceVar($content)
	{
		// 接着替换其中的变量
		$content = $this->publicReplaceVar($content);
		// 替换为php代码
		$content = preg_replace('/\{(.*?)\}/', '<?php echo \\1; ?>', $content);
		return $content;
	}
	/**
	 * 替换foeach语句
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function replaceForeach($content)
	{
		preg_match('/from=(\\$\S*)/i', $content, $from);
		preg_match('/key=([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $key);
		preg_match('/item=([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $item);
		// 修正获得的字符串
		$from = $this->publicReplaceVar($from[1]);
		$key = trim(trim($key[1], '"'), "'");
		$item = trim(trim($item[1], '"'), "'");
		// 组成替换数组
		return '<?php foreach(' . $from . ' as $' . $key . '=>$' . $item . ') { ?>';
	}
	/**
	 * 替换if语句
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function replaceIf($content)
	{
		// 替换为php代码
		$content = preg_replace('/\{if(.*?)\}/i', '<?php if(\\1) { ?>', $content);
		return $this->publicReplaceVar($content);
	}
	/**
	 * 替换elseif语句
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function replaceElseif($content)
	{
		// 替换为php代码
		$content = preg_replace('/\{elseif(.*?)\}/i', '<?php } elseif(\\1) { ?>', $content);
		return $this->publicReplaceVar($content);
	}
	/**
	 * 替换elseif语句
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function replaceElse($content)
	{
		return preg_replace('/\{else(.*?)\}/i', '<?php } else { ?>', $content);
	}
	/**
	 * 替换结束语句
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function replaceEnd($content)
	{
		// 替换为php代码
		return preg_replace('/\{\/.*?\}/i', '<?php } ?>', $content);
	}
	/**
	 * 替换变量
	 * @access private
	 * @param  sting    $content  内容
	 * @return string
	 */
	private function publicReplaceVar($content)
	{
		// {$value.key}
		$content = preg_replace('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)/', "\$\\1['\\2']", $content);
		// {$value.number}
		$content = preg_replace('/\\$([a-zA-Z_][a-zA-Z0-9_]*)\.(\d*)/', "\$\\1[\\2]", $content);
		return $content;
	}
	/**
	 * 获取实时的部件数据
	 * @access private
	 * @param  sting  $content  模板内容
	 */
	private function getRealTimeData($content)
	{
		$widgetPath = APP_PATH . 'widget' . Z_DS;
		preg_match_all('/\{\\$widgetView_([a-zA-Z]*)\}/', $content, $res);
		foreach($res[1] as $k => $v)
		{
			$tmpContrallerPath = $widgetPath . 'contrallers' . Z_DS . $v . '.php';
			// 允许部件控制器为空
			if(is_file($tmpContrallerPath))
			{
				include $tmpContrallerPath;
			}
		}
		// 取出部件数据并赋值到数据栈中
		$this->assign(Widget::getWidgetData());
	}
}
