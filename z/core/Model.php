<?php

namespace z\core;
use mysqli;

class Model
{
	/*
	选择：select * from table1 where 范围 order by field1 desc/asc limit begin, length
	
	插入：insert into table1(field1,field2) values(value1,value2)
	多条：insert into table1(field1,field2) values(value1,value2), (value3,value4)...
	
	删除：delete from table1 where 范围
	更新：update table1 set field1=value1 where 范围
	查找：select * from table1 where field1 like ’%value1%’ ---like的语法很精妙，查资料!
	排序：select * from table1 order by field1,field2 [desc]
	总数：select count as totalcount from table1
	求和：select sum(field1) as sumvalue from table1
	平均：select avg(field1) as avgvalue from table1
	最大：select max(field1) as maxvalue from table1
	最小：select min(field1) as minvalue from table1
	去重：select distinct field1 from table1 where 范围
	
	
	切换：use dbname
	
	
	内联：select a.a, b.c from a inner join b on a.a = b.c
	等同：select a.a, b.c from a,b where a.a=b.c
	
	左联：select a.a, b.c from a left join b on a.a = b.c
	右联：select a.a, b.c from a right join b on a.a = b.c
	
	
	
	内联：两表都相互匹配的数据
	左联：所有匹配条件的左表数据，右表不存在则为null
	右联：所有匹配条件的右表数据，左表不存在则为null
	
	WHERE逻辑：>,=,<,<>,!=,!>,!<,=>,=<,in,not in,like,between a and b
	ORDER BY逻辑：desc,asc
	LIMIT逻辑：begin, length
	
	//*/
	
	// 语句嵌套
	
	private static $conn;
	private static $table = array();
	private static $field = array();
	private static $where = array();
	private static $order = array();
	private static $group = array();
	private static $data = array();
	private static $prefix = '';
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
	}
	// 单例方法，初始化对象
	public static function init($options = null)
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c($options);
		}
		return self::$_instance;
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
	 * 设置字段名
	 * @param: mixed $mixed 字段名或字段名数组[字段名=>action(count、sum、avg、max、min等)]
	 * @return: 当前对象
	 */
	public static function field($mixed)
	{
		if(is_array($mixed))
		{
			// 判断是否索引数组
			$keys = array_keys($mixed);
			if($keys != array_keys($keys))
			{
				foreach($mixed as $k => $v)
				{
					self::$field[$k] = $v;
				}
			}
			else
			{
				foreach($mixed as $v)
				{
					self::$field[$v] = '';
				}
			}
		}
		else
		{
			self::$field[$mixed] = '';
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
		$actionRule = array('>', '=', '<', '<>', '!=', '!>', '!<', '=>', '=<', '>=', '<=', 'in', 'not in', 'like', 'IN', 'NOT IN', 'LIKE');
		foreach($array as $v)
		{
			// 二维数组且长度大于2，即字段名、操作符、值是必须的
			if(is_array($v) && count($v) > 2)
			{
				// 判断是否合法的操作符
				if(in_array($v[1], $actionRule))
				{
					// 修正操作符为大写标准
					$v[1] = strtoupper($v[1]);
					// 修正模糊查询的值
					$v[2] = $v[1] === 'LIKE' ? "'" . $v[2] . "'" : $v[2];
					// 修正逻辑部分
					$v[3] = isset($v[3]) ? strtoupper($v[3]) : '';
					$v[3] = strtr($v[3], array('||'=>'OR','&&'=>'AND'));
					// 添加到数组
					self::$where[] = $v;
				}
			}
		}
		return self::$_instance;
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
			self::$group = $mixed;
		}
		else
		{
			self::$group = array($mixed);
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
						self::$order[] = $k . ' ' . strtoupper($v);
					}
				}
			}
			else
			{
				foreach($mixed as $v)
				{
					self::$order[] = $v . ' DESC';
				}
			}
		}
		else
		{
			self::$order[] = $mixed . ' DESC';
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
	 * 绑定数据
	 * @param: array $mixed 数据数组
	 * @return: 当前对象
	 */
	public static function data($array)
	{
		foreach($array as $key => $val)
		{
			// 二维数据
			if(is_array($val))
			{
				foreach($val as $k => $v)
				{
					// 修正字符串
					if(is_string($v))
					{
						$array[$key][$k] = "'" . $v . "'";
					}
				}
			}
			else
			{
				// 修正字符串
				if(is_string($val))
				{
					$array[$key] = "'" . $val . "'";
				}
			}
		}
		self::$data = $array;
		return self::$_instance;
	}
	
	// 拼接查询字段
	private static function fieldToStr()
	{
		$str = '';
		foreach(self::$field as $k => $v)
		{
			$str .= ($v ? strtoupper($v) . '(`' . $k . '`)' : '`' . $k . '`') . ',';
		}
		$str = rtrim($str, ',');
		return $str;
	}
	
	// 拼接表名
	private static function tableToStr()
	{
		$str = '';
		foreach(self::$table as $v)
		{
			$str .= '`' . self::$prefix . $v . '`,';
		}
		$str = rtrim($str, ',');
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
				$str .= $v[0] . ' ' . $v[1] . ' ' . $v[2] . ' ' . $v[3] . ' ';
			}
			// 去掉可能存在的多余的与或逻辑
			$str = rtrim($str);
			$str = rtrim($str, 'AND');
			$str = rtrim($str, 'OR');
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
		$str = rtrim($str, ',');
		return $str;
	}
	
	// 拼接需要更新的数据
	private static function updateDateToStr()
	{
		$str = '';
		
	}
	
	// 拼接分组、排序、限制
	private static function elseToStr()
	{
		$str = '';
		// 拼接分组条件
		if(!empty(self::$group))
		{
			$str .= ' GROUP BY ' . implode(',', self::$group);
		}
		// 拼接排序条件
		if(!empty(self::$order))
		{
			$str .= ' ORDER BY ' . implode(',', self::$order);
		}
		// 拼接查询限制
		if(!empty(self::$limit))
		{
			$str .= ' LIMIT ' . self::$limit;
		}
		return $str;
	}
	
	/**
	 * 执行查询
	 */
	public static function select()
	{
		$sql = 'SELECT ' . self::fieldToStr() . ' FROM ' . self::tableToStr() . self::whereToStr() . self::elseToStr();
		self::query($sql);
	}
	
	/**
	 * 执行新增
	 */
	public static function insert()
	{
		$sql = 'INSERT INTO ' . self::tableToStr() . '(' . self::fieldToStr() . ') VALUES ' . self::insertDataToStr();
		self::query($sql);
	}
	
	/**
	 * 执行更新
	 */
	public static function update()
	{
		
	}
	
	/**
	 * 执行删除
	 */
	public static function delete()
	{
		$sql = 'DELETE FROM ' . self::tableToStr() . self::whereToStr();
		self::query($sql);
	}
	
	// 执行语句查询
	public static function query($sql)
	{
		die($sql);
		self::$conn->query($sql);
	}
	
	/**
	 * 清除上一个查询 (包括字段、条件、分组、排序、限制)
	 */
	public static function clean()
	{
		self::$field = array();
		self::$where = array();
		self::$group = array();
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
