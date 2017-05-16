<?php

namespace z\core;
use mysqli;
use z;

class Model
{
	private static $conn;
	private static $debug = array();
	private static $table = array();
	private static $from  = array();
	private static $join  = array();
	private static $field = array();
	private static $where = array();
	private static $order = array();
	private static $group = array();
	private static $having= array();
	private static $data  = array();
	private static $prefix= '';
	private static $limit = '';
	
	// 保存例实例在此属性中
	private static $_instance;
	
	// 构造函数声明为private,防止直接创建对象
	private function __construct($options)
	{
		// 配置表前缀
		if(isset($options['prefix']))
		{
			self::$prefix = $options['prefix'];
		}
		// 修正端口
		if(isset($options['port']) && !is_int($options['port']))
		{
			$options['port'] = (int) $options['port'];
			if(!$options['port'])
			{
				$options['port'] = 3306;
			}
		}
		self::$conn = new mysqli($options['server'], $options['username'], $options['password'], $options['dbname'], $options['port']);
		if(self::$conn->connect_error)
		{
			die("连接失败: " . $conn->connect_error);
		}
		self::$conn->set_charset($options['charset']);
	}
	
	// 单例方法，初始化对象
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c(z::$dbconfig);
		}
		return self::$_instance;
	}
	
	/**
	 * 获得MySQL版本号
	 */
	public static function version()
	{
		$info = explode('-', @self::$conn->get_server_info());
		return $info[0];
	}
	
	/**
	 * 切换数据库
	 * @param: string $dbname 数据库名
	 * @return: 当前对象
	 */
	public static function useDB($dbname)
	{
		self::$conn->query('USE ' . $dbname);
		return self::$_instance;
	}
	
	/**
	 * 设置表名
	 * @param: mixed $mixed 表名或表名数组
	 * @return: 当前对象
	 */
	public static function table($mixed)
	{
		if(is_array($mixed))
		{
			self::$table = $mixed;
		}
		else
		{
			self::$table = array($mixed);
		}
		return self::$_instance;
	}
	
	/**
	 * 设置联表查询中from后紧接的表名
	 * @param: string $table 表名
	 * @param: string $alias 别名
	 * @return: 当前对象
	 */
	public static function from($table, $alias = '')
	{
		self::$from = array($table, $alias);
		return self::$_instance;
	}
	
	/**
	 * 设置字段名
	 * @param: mixed $mixed 字段名或数组
	 * @return: 当前对象
	 */
	public static function field($mixed)
	{
		$funcRule = array('DISTINCT', 'UPPER', 'LOWER', 'DATE', 'TIME', 'YEAR', 'MONTH', 'DAY', 'HOUR', 'MINUTE', 
		                  'SECOND', 'DAYOFWEEK', 'ABS', 'RAND', 'AVG', 'COUNT', 'MAX', 'MIN', 'SUM');
		if(is_array($mixed))
		{
			// 判断是否索引数组
			$keys = array_keys($mixed);
			// 关联数组
			if($keys != array_keys($keys))
			{
				foreach($mixed as $k => $v)
				{
					if(in_array(strtoupper($v), $funcRule))
					{
						self::$field[] = array($k, $v, '');
					}
					else
					{
						self::$field[] = array($k, '', $v);
					}
				}
			}
			// 索引数组
			else
			{
				// 二维数组
				if(is_array(reset($mixed)))
				{
					foreach($mixed as $v)
					{
						$v[1] = isset($v[1]) ? $v[1] : '';
						if(in_array(strtoupper($v[1]), $funcRule))
						{
							self::$field[] = array($v[0], $v[1], isset($v[2]) ? $v[2] : '');
						}
						else
						{
							self::$field[] = isset($v[2]) ? array($v[0], '', $v[2]) : array($v[0], '', $v[1]);
						}
					}
				}
				// 一维数组
				else
				{
					foreach($mixed as $v)
					{
						self::$field[] = array($v, '', '');
					}
				}
			}
		}
		else
		{
			// 单字段
			self::$field[] = array($mixed, '', '');
		}
		return self::$_instance;
	}
	
	/**
	 * 设置联表
	 * @param: array $array
	 * @return: 当前对象
	 */
	public static function join($array)
	{
		$joinRule = array('inner', 'left', 'right', 'INNER', 'LEFT', 'RIGHT');
		if(is_array($array))
		{
			foreach($array as $v)
			{
				// 0联表方式、1表名、2别名、3条件[字段, 字段]
				if(is_array($v) && in_array($v[0], $joinRule) && !empty($v[1]) && !empty($v[3]))
				{
					if(!is_array(reset($v[3])))
					{
						$v[3] = array($v[3]);
					}
					self::$join[] = $v;
				}
			}
		}
		return self::$_instance;
	}
	
	/**
	 * 设置条件（不支持between）
	 * @param: array $array 由字段名、操作符、值、逻辑组成的数组，如：[['id', '>', 3,'and']], [['id', '>', 3,'&&(']], [['id', '>', 3,')']]
	 * @return: 当前对象
	 */
	public static function where($array)
	{
		// 判断二位数组
		if(is_array(reset($array)))
		{
			self::$where = array_map('self::fixCondition', $array);
		}
		else
		{
			self::$where = array(self::fixCondition($array));
		}
		return self::$_instance;
	}
	
	// 修正条件
	private static function fixCondition($arr)
	{
		$actionRule = array('>', '=', '<', '<>', '!=', '!>', '!<', '=>', '=<', '>=', '<=', 'in', 'not in', 'like', 'regexp', 'IN', 'NOT IN', 'LIKE', 'REGEXP');
		// 二维数组且长度大于2，即字段名、操作符、值是必须的，以及操作符的合法性
		if(is_array($arr) && count($arr) > 2 && in_array($arr[1], $actionRule))
		{
			// 修正操作符为大写标准
			$arr[1] = strtoupper($arr[1]);
			// 修正字符串类型的值
			$arr[2] = is_string($arr[2]) ? "'" . $arr[2] . "'" : $arr[2];
			// 修正逻辑部分
			$arr[3] = isset($arr[3]) ? strtoupper($arr[3]) : '';
			$arr[3] = strtr($arr[3], array('||'=>'OR','&&'=>'AND'));
			$arr[3] = empty($arr[3]) ? 'AND' : $arr[3];
			// 添加到数组
			return $arr;
		}
		return false;
	}
	
	/**
	 * 设置分组
	 * @param: mixed $mixed 字段或字段数组
	 * @return: 当前对象
	 */
	public static function group($mixed)
	{
		if(is_array($mixed))
		{
			foreach($mixed as $v)
			{
				self::$group[] = '`' . $v . '`';
			}
		}
		else
		{
			self::$group = array('`' . $mixed . '`');
		}
		return self::$_instance;
	}
	
	/**
	 * 设置分组条件（不支持between）
	 * @param: array $array 由字段名、操作符、值、逻辑（可空）组成的数组
	 *         如：[['id', '>', 3,'and']], [['id', '>', 3,'&&(']], [['id', '>', 3,')']]
	 * @return: 当前对象
	 */
	public static function having($array)
	{
		// 判断二位数组
		if(is_array(reset($array)))
		{
			self::$having = array_map('self::fixCondition', $array);
		}
		else
		{
			self::$having = array(self::fixCondition($array));
		}
		return self::$_instance;
	}
	
	/**
	 * 设置排序
	 * @param: mixed $mixed 字段或字段数组或字段与排序方式值对的数组
	 * @return: 当前对象
	 */
	public static function order($mixed)
	{
		$rule = array('desc', 'DESC', 'asc', 'ASC');
		if(is_array($mixed))
		{
			// 判断是否索引数组
			$keys = array_keys($mixed);
			if($keys != array_keys($keys))
			{
				foreach($mixed as $k => $v)
				{
					if(in_array($v, $rule))
					{
						self::$order[] = '`' . $k . '` ' . strtoupper($v);
					}
				}
			}
			else
			{
				foreach($mixed as $v)
				{
					self::$order[] = '`' . $v . '` DESC';
				}
			}
		}
		else
		{
			self::$order[] = '`' . $mixed . '` DESC';
		}
		return self::$_instance;
	}
	
	/**
	 * 设置查询限制
	 * @param: mixed $mixed 字段或字段数组或字段与排序方式值对的数组
	 * @return: 当前对象
	 */
	public static function limit($first, $second = null)
	{
		if(!$second)
		{
			self::$limit = '0,' . (int)$first;
		}
		else
		{
			self::$limit = (int)$first . ',' . (int)$second;
		}
		return self::$_instance;
	}
	
	/**
	 * 绑定数据（并将原始数据转为二维数组）
	 * @param: array $mixed 数据
	 * @return: 当前对象
	 */
	public static function data($mixed)
	{
		$newData = array();
		$mixed = is_array($mixed) ? $mixed : array($mixed);
		foreach($mixed as $key => $val)
		{
			// 二维数据
			if(is_array($val))
			{
				foreach($val as $k => $v)
				{
					// 修正字符串
					$newData[$key][$k] = is_numeric($v) ? $v : "'" . $v . "'";
				}
			}
			else
			{
				// 修正字符串
				$newData[0][$key] = is_numeric($val) ? $val : "'" . $val . "'";
			}
		}
		self::$data = $newData;
		return self::$_instance;
	}
	
	// 拼接查询字段
	private static function fieldToStr()
	{
		// 空值则认为是获取全部字段
		if(!self::$field)
		{
			return '*';
		}
		$str = '';
		foreach(self::$field as $v)
		{
			// 0字段名、1操作名、2别名
			$v[0] = $v[0] === '*' ? $v[0] : '`' . $v[0] . '`';
			$str .= empty($v[1]) ? $v[0] : strtoupper($v[1]) . '(' . $v[0] . ')';
			$str .= empty($v[2]) ? ',' : ' AS ' . $v[2] . ',';
		}
		return rtrim($str, ',');
	}
	
	// 拼接表名
	private static function tableToStr()
	{
		$str = '';
		foreach(self::$table as $v)
		{
			$str .= '`' . self::$prefix . $v . '`,';
		}
		return rtrim($str, ',');
	}
	
	// 拼接联表查询的一个表名
	private static function fromToStr()
	{
		return '`' . self::$prefix . self::$from[0] . '`' . (empty(self::$from[1]) ? '' : ' AS ' . self::$from[1]) . ' ';
	}
	
	// 拼接联表及条件
	private static function joinToStr()
	{
		$str = '';
		foreach(self::$join as $v)
		{
			// 0联表方式、1表名、2别名、3条件[字段, 字段]
			$str .= strtoupper($v[0]) . ' JOIN ' . $v[1] . (empty($v[2]) ? '' : ' AS ' . $v[2]) . ' ON ';
			foreach($v[3] as $vv)
			{
				$str .= $vv[0] . '=' . $vv[1] . ' AND ';
			}
			$str = rtrim(rtrim($str), 'AND') . ' ';
		}
		return $str;
	}
	
	// 拼接查询条件
	private static function whereToStr()
	{
		$str = '';
		if(!empty(self::$where))
		{
			$str .= ' WHERE ';
			foreach(self::$where as $v)
			{
				if($v)
				{
					$str .= '`' . $v[0] . '` ' . $v[1] . ' ' . $v[2] . ' ' . $v[3] . ' ';
				}
			}
			// 去掉可能存在的多余的与或逻辑
			$str = rtrim(rtrim(rtrim($str), 'AND'), 'OR');
		}
		return $str;
	}
	
	// 拼接需要插入的数据
	private static function insertDataToStr()
	{
		$str = '';
		foreach(self::$data as $v)
		{
			$str .= '(' . implode(',', $v) . '),';
		}
		return rtrim($str, ',');
	}
	
	// 拼接需要更新的数据
	private static function updateDateToStr()
	{
		$str = '';
		foreach(self::$data as $v)
		{
			foreach(self::$field as $kk => $vv)
			{
				// 处理数据
				$data = !empty($v[$kk]) ? $v[$kk] : '\'\'';
				if(!empty($data) && preg_match('/[\+\-\*\/\!\%]/', $data))
				{
					$data = '`' . $vv[0] . '`' . trim($data, ',');
				}
				$str .= '`' . $vv[0] . '`=' . $data . ',';
			}
		}
		return rtrim($str, ',');
	}
	
	// 拼接分组
	private static function groupToStr()
	{
		$str ='';
		if(!empty(self::$group))
		{
			$str .= ' GROUP BY ' . implode(',', self::$group);
			if(!empty(self::$having))
			{
				$str .= ' HAVING ';
				foreach(self::$having as $v)
				{
					if($v)
					{
						// having可以使用集合函数，因此需作对应处理
						if(strpos($v[0], '(') !== false )
						{
							$str .= strpos($v[0], '*') ? $v[0] : strtr($v[0], array('('=>'(`',')'=>'`)'));
						}
						else
						{
							$str .= '`' . $v[0] . '`';
						}
						$str .= ' ' . $v[1] . ' ' . $v[2] . ' ' . $v[3] . ' ';
					}
				}
				// 去掉可能存在的多余的与或逻辑
				$str = rtrim(rtrim(rtrim($str), 'AND'), 'OR');
			}
		}
		return $str;
	}
	
	// 拼接排序方式
	private static function orderToStr()
	{
		$str = '';
		if(!empty(self::$order))
		{
			$str .= ' ORDER BY ' . implode(',', self::$order);
		}
		return $str;
	}
	
	// 拼接查询限制
	private static function limitToStr()
	{
		$str = '';
		if(!empty(self::$limit))
		{
			$str .= ' LIMIT ' . self::$limit;
		}
		return $str;
	}
	
	// 执行语句查询
	private static function query($sql)
	{
		if(Z_DEBUG)
		{
			self::$debug[] = $sql;
		}
		self::clean();
		return self::$conn->query($sql);
	}
	
	/**
	 * 执行查询
	 */
	public static function select()
	{
		if(!empty(self::$from) && !empty(self::$join))
		{
			$sql = 'SELECT ' . self::fieldToStr() . ' FROM ' . self::fromToStr() . self::joinToStr();
		}
		else
		{
			$sql = 'SELECT ' . self::fieldToStr() . ' FROM ' . self::tableToStr();
		}
		$sql .= self::whereToStr() . self::groupToStr() . self::orderToStr() . self::limitToStr();
		return self::query($sql);
	}
	
	/**
	 * 执行新增
	 */
	public static function insert()
	{
		$sql = 'INSERT INTO ' . self::tableToStr() . '(' . self::fieldToStr() . ') VALUES ' . self::insertDataToStr();
		return self::query($sql);
	}
	
	/**
	 * 执行更新
	 */
	public static function update()
	{
		$sql = 'UPDATE ' . self::tableToStr() . ' SET ' . self::updateDateToStr() . self::whereToStr();
		return self::query($sql);
	}
	
	/**
	 * 执行删除
	 */
	public static function delete()
	{
		$sql = 'DELETE FROM ' . self::tableToStr() . self::whereToStr();
		return self::query($sql);
	}
	
	/**
	 * 取得一个数据
	 * @param: string $sql 可选，执行指定查询语句
	 * @return: string或false
	 */
	public static function getOne($sql = null)
	{
		$result = $sql ? self::query($sql) : self::select();
		if($result !== false)
		{
			$row = $result->fetch_row();
			return $row !== false ? $row[0] : '';
		}
		return false;
	}
	
	/**
	 * 取得一列数据
	 * @param: string $sql 可选，执行指定查询语句
	 * @return: 一维数组或false
	 */
	public static function getCol($sql = null)
	{
		$result = $sql ? self::query($sql) : self::select();
		if($result !== false)
		{
			$array = array();
			while($row = $result->fetch_row())
			{
				$array[] = $row[0];
			}
			return $array;
		}
		return false;
	}
	
	/**
	 * 取得一行数据
	 * @param: string $sql 可选，执行指定查询语句
	 * @return: 一维数组或false
	 */
	public static function getRow($sql = null)
	{
		$result = $sql ? self::query($sql) : self::select();
		if($result !== false)
		{
			return $result->fetch_assoc();
		}
		return false;
	}
	
	/**
	 * 取得全部数据
	 * @param: string $sql 可选，执行指定查询语句
	 * @return: 二维数组或false
	 */
	public static function getAll($sql = null)
	{
		$result = $sql ? self::query($sql) : self::select();
		if($result !== false)
		{
			$array = array();
			while($row = $result->fetch_assoc())
			{
				$array[] = $row;
			}
			return $array;
		}
		return false;
	}
	
	// 聚合查询
	private static function gether($type, $field = '*')
	{
		$sql = 'SELECT ' . strtoupper($type) . '(' . $field . ') FROM ' . self::tableToStr() . self::whereToStr() . self::groupToStr();
		return self::getOne($sql);
	}
	
	/**
	 * 取得数量
	 * @return: number
	 */
	public static function count()
	{
		return self::gether('count');
	}
	
	/**
	 * 判断是否存在
	 * @return: bool
	 */
	public static function has()
	{
		return !!self::gether('count');
	}
	
	/**
	 * 取得最大值
	 * @return: number
	 */
	public static function max($field)
	{
		return self::gether('max', $field);
	}
	
	/**
	 * 取得最小值
	 * @return: number
	 */
	public static function min($field)
	{
		return self::gether('min', $field);
	}
	
	/**
	 * 取得平均数
	 * @return: number
	 */
	public static function avg($field)
	{
		return self::gether('avg', $field);
	}
	
	/**
	 * 取得总和
	 * @return: number
	 */
	public static function sum($field)
	{
		return self::gether('sum', $field);
	}
	
	/**
	 * 取得上一个INSERT操作产生的ID
	 * @return: number
	 */
	public static function getInsertId()
	{
		return self::$conn->insert_id();
	}
	
	// 调试查询语句
	public static function debug()
	{
		echo '<pre>';
		print_r(self::$debug);
		exit(0);
	}
	
	// 清空查询记录 (包括联表、字段、条件、分组、排序、数据、限制)
	private static function clean()
	{
		self::$from  = array();
		self::$join  = array();
		self::$field = array();
		self::$where = array();
		self::$group = array();
		self::$having= array();
		self::$order = array();
		self::$data  = array();
		self::$limit = '';
	}
	
	// 关闭mysql连接
	public static function close()
	{
		self::$conn->close();
	}
}
