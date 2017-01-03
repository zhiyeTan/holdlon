<?php

namespace z\core;

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
	// 保存例实例在此属性中
	private static $_instance;
	// 构造函数声明为private,防止直接创建对象
	private function __construct($options)
	{
		self::$conn = new mysqli($host, $user, $pwd, $dbname, $post, $socket);
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
	 * @return: $conn
	 */
	public static function use($dbname)
	{
		self::$conn->query('USE ' . $dbname);
		return self::$conn;
	}
	
	/**
	 * 设置表名
	 * @param: mixed $mixed 表名或表名数组
	 * @return: $conn
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
		return self::$conn;
	}
	
	/**
	 * 设置字段名
	 * @param: mixed $mixed 字段名或字段名数组[字段名=>action(count、sum、avg、max、min)]
	 * @return: $conn
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
		return self::$conn;
	}
	
	/**
	 * 设置条件（不支持between）
	 * @param: array $array 由字段名、操作符、值组成的数组，如：[['id', '>', 3]]
	 * @param: 操作符 >,=,<,<>,!=,!>,!<,=>,=<,in,not in,like
	 * @return: $conn
	 */
	public static function where($array)
	{
		self::$where = $array;
		return self::$conn;
	}
	
	/**
	 * 设置排序
	 * @param: mixed $mixed 字段或字段数组或字段与排序方式值对的数组
	 * @return: $conn
	 */
	public static function order($mixed)
	{
		if(is_array($mixed))
		{
			// 判断是否索引数组
			$keys = array_keys($mixed);
			if($keys != array_keys($keys))
			{
				foreach($mixed as $k => $v)
				{
					self::$order[$k] = $v;
				}
			}
			else
			{
				foreach($mixed as $v)
				{
					self::$order[$v] = 'desc';
				}
			}
		}
		else
		{
			self::$order[$mixed] = 'desc';
		}
		return self::$conn;
	}
	
	
	// 清除查询记录
	public static function clean()
	{
		
	}
	
	// 关闭mysql连接
	public static function close()
	{
		self::$conn->close();
	}
}
