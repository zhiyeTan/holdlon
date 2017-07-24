<?php

namespace z\core;

/**
 * 部件管理
 * 
 * 直接使用模板机制去加载部件数据的，需要模板类作为一个单例
 * 否则部件之间以及部件与模板之间的数据是不通的，因为每一次初始化都是一个新的对象
 * 当然也可以使用超全局变量来保存模板类对象，以保证对象的一致性
 * 这里使用静态类处理这个问题
 * 以上提到的方法并未详细考虑过其各自优缺，如有异议，烦请告知作者
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class Widget
{
	// 禁止创建对象
	private function __construct(){}
	// 数据栈
	private static $data = array();
	/**
	 * 赋值到数据栈中
	 * @access public
	 * @param  string  $key    键名或键值对数组
	 * @param  string  $value  键值（$key为非数组时有效）
	 */
	public static function assign($key, $value = null)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				self::$data[$k] = $v;
			}
		}
		else
		{
			self::$data[$key] = $value;
		}
	}
	/**
	 * 检查数据栈中是否存在指定键名
	 * 主要用在调用多个部件且部件间使用同一数据源的时候作为判断，以避免重复的读取操作
	 * @access public
	 * @param  string  $key  键名
	 * @return boolean
	 */
	public static function chkAssign($key)
	{
		return isset(self::$data[$key]) ? true : false;
	}
	/**
	 * 获得部件数据
	 * @access public
	 * @return array
	 */
	public static function getWidgetData()
	{
		return self::$data;
	}
}
