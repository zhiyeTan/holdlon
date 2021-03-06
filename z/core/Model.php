<?php

namespace z\core;

use z;
use PDO;

/**
 * 数据库管理类
 * 
 * 基于mysql
 * 
 * @author 谈治烨<594557148@qq.com>
 * @copyright 使用或改进本代码请注明原作者
 * 
 */
class model
{
	private static $conn;
	private static $debug		= array();
	private static $table		= array();
	private static $from		= array();
	private static $join		= array();
	private static $field		= array();
	private static $where		= array();
	private static $order		= array();
	private static $group		= array();
	private static $having		= array();
	private static $data		= array();
	private static $dbname		= '';
	private static $prefix		= '';
	private static $limit		= '';
	private static $useExplain	= true;
	// 查询耗时，如果超过这个时间则考虑写入慢查询日志中
	private static $maxTime		= 1;
	
	private static $_instance;
	
	// 禁止直接创建对象
	private function __construct($options)
	{
		self::$conn = connection::dbConnect($options, z::$configure['pdo_connect']);
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
			$dbconf = array(
				'dbtype'	=> z::$configure['dbtype'],
				'server'	=> z::$configure['server'],
				'username'	=> z::$configure['username'],
				'password'	=> z::$configure['password'],
				'charset'	=> z::$configure['charset'],
				'port'		=> z::$configure['port']
			);
			self::$_instance = new $c($dbconf);
			self::$prefix = z::$configure['prefix'];
			self::$dbname = z::$configure['dbname'];
		}
		return self::$_instance;
	}
	
	/**
	 * 获得MySQL版本号
	 * @access public
	 * @return string
	 */
	public function version()
	{
		if(z::$configure['pdo_connect'])
		{
			return self::$conn->getAttribute(PDO::ATTR_SERVER_VERSION);
		}
		else
		{
			$info = explode('-', @self::$conn->get_server_info());
			return $info[0];
		}
	}
	
	/**
	 * 切换数据库
	 * @access public
	 * @param  string  $dbname  数据库名
	 * @return this
	 */
	public function useDb($dbname)
	{
		self::$dbname = $dbname;
		self::$conn->query('USE ' . $dbname);
		return self::$_instance;
	}
	
	/**
	 * 设置表名
	 * 
	 * 用法如下：
	 * ->table('admin')
	 * ->table(array('admin', 'user'))
	 * 
	 * @access public
	 * @param  mixed   $mixed  表名或表名数组
	 * @return this
	 */
	public function table($mixed)
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
	 * 
	 * 用法如下：
	 * ->from('admin')
	 * ->from('admin', 'a')
	 * 
	 * @access public
	 * @param  string  $table  表名
	 * @param  string  $alias  别名
	 * @return this
	 */
	public function from($table, $alias = '')
	{
		self::$from = array($table, $alias);
		return self::$_instance;
	}
	
	/**
	 * 设置字段名
	 * 
	 * 不设置任何值时默认读取全部
	 * 用法如下：
	 * ->field('a.id')
	 * ->field(array('account', 'password'))
	 * ->field(array('account'=>'count'))
	 * ->field(array('account'=>'a', 'id'=>'max'))
	 * ->field(array('*'=>'count', 'id'=>'count'))
	 * ->field(array(array('account','count','a')))
	 * ->field(array(array('account','','a'), array('*','count','b')))
	 * 
	 * @access public
	 * @param  mixed   $mixed  字段名或数组
	 * @return this
	 */
	public function field($mixed)
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
	 * 
	 * 用法如下：
	 * ->join(array('inner', 'user', 'u', array('u.uid', 'a.uid')))
	 * ->join(array('left', 'user', 'u', array(array('u.uid', 'a.uid'), array('u.uid', 'b.uid'))))
	 * 
	 * @access public
	 * @param  array   $array  关联表、方式及关系数组
	 * @return this
	 */
	public function join($array)
	{
		$joinRule = array('inner', 'left', 'right', 'and', 'INNER', 'LEFT', 'RIGHT', 'AND');
		// 0联表方式、1表名、2别名、3条件[字段, 字段]
		if(is_array($array) && in_array($array[0], $joinRule) && !empty($array[1]) && !empty($array[3]))
		{
			// 修正表名
			$array[1] = '`' . self::$prefix . $array[1] . '`';
			// 修正条件部分
			if(!is_array(reset($array[3])))
			{
				$array[3] = array($array[3]);
			}
			self::$join[] = $array;
		}
		return self::$_instance;
	}
	
	/**
	 * 设置条件
	 * 
	 * 不支持between
	 * 用法如下：
	 * ->where(array('uid', '=', 1))
	 * ->where(array('uid', '=', 1, 'and'))
	 * ->where(array(array('uid', '=', 1), array('id', '>=', 12)))
	 * ->where(array(array('uid', '=', 1, '&&'), array('id', '>=', 12)))
	 * 
	 * @access public
	 * @param  array   $array  由字段名、操作符、值、逻辑组成的数组
	 * @return this
	 */
	public function where($array)
	{
		if($array)
		{
			// 判断二位数组
			if(is_array(reset($array)))
			{
				$tmpCondition = array_map(array(__CLASS__, 'fixCondition'), $array);
			}
			else
			{
				$tmpCondition = array(self::fixCondition($array));
			}
			self::$where = array_merge(self::$where, $tmpCondition);
		}
		return self::$_instance;
	}
	
	/**
	 * 设置分组
	 * 
	 * 用法如下：
	 * ->group('age')
	 * ->group(array('age', 'familyName'))
	 * 
	 * @access public
	 * @param  mixed   $mixed  字段或字段数组
	 * @return this
	 */
	public function group($mixed)
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
	 * 设置分组条件
	 * 
	 * 不支持between
	 * 用法如下：
	 * ->having(array('id', '>', 0))
	 * ->having(array(array('id', '>', 0, '||'), array('sum(id)', '>', 1)))
	 * ->having(array(array('sum(id)', '>', 1, 'and'), array('count(*)', '>', 1)))
	 * 
	 * @access public
	 * @param  array   $array  由字段名、操作符、值、逻辑（可空）组成的数组
	 * @return this
	 */
	public function having($array)
	{
		// 判断二位数组
		if(is_array(reset($array)))
		{
			$tmpCondition = array_map(array(__CLASS__, 'fixCondition'), $array);
		}
		else
		{
			$tmpCondition = array(self::fixCondition($array));
		}
		self::$having = array_merge(self::$having, $tmpCondition);
		return self::$_instance;
	}
	
	/**
	 * 设置排序
	 * 
	 * 用法如下：
	 * ->order('age')
	 * ->order(array('age', 'sort'))
	 * ->order(array('age'=>'asc', 'sort'=>'desc'))
	 * 
	 * @access public
	 * @param  mixed   $mixed  字段或字段数组或字段与排序方式值对的数组
	 * @return this
	 */
	public function order($mixed)
	{
		$rule = array('desc', 'DESC', 'asc', 'ASC');
		if(is_array($mixed))
		{
			foreach($mixed as $k => $v)
			{
				self::$order[] = is_numeric($k) ?
								(self::fixField($v) . ' DESC') :
								(self::fixField($k) . (in_array($v, $rule) ? strtoupper($v) : 'DESC'));
			}
		}
		else
		{
			self::$order[] = self::fixField($mixed) . ' DESC';
		}
		return self::$_instance;
	}
	
	/**
	 * 设置查询限制
	 * 
	 * 用法如下：
	 * ->limit(5)
	 * ->limit(0, 5)
	 * 
	 * @access public
	 * @param  mixed   $mixed  字段或字段数组或字段与排序方式值对的数组
	 * @return this
	 */
	public function limit($first, $second = null)
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
	 * 绑定数据（同时将原始数据转为二维数组）
	 * 
	 * 用法如下：
	 * ->data(1)
	 * ->data(array(1, 'admin', 'passwordkey'))
	 * ->data(array(array(1, 'admin', 'passwordkey'), array(2, 'admin2', 'passwordkey2')))
	 * 
	 * @access public
	 * @param  array   $mixed  数据
	 * @return this
	 */
	public function data($mixed)
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
	
	/**
	 * 拼接查询字段
	 * @access private
	 * @return string
	 */
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
			// 修正字段名
			self::fixField($v[0]);
			// 修正操作名
			$str .= empty($v[1]) ? $v[0] : strtoupper($v[1]) . '(' . $v[0] . ')';
			// 修正别名
			$str .= empty($v[2]) ? ',' : ' AS ' . $v[2] . ',';
		}
		return rtrim($str, ',');
	}
	
	/**
	 * 拼接表名
	 * @access private
	 * @return string
	 */
	private static function tableToStr()
	{
		$str = '';
		foreach(self::$table as $v)
		{
			$str .= '`' . self::$prefix . $v . '`,';
		}
		return rtrim($str, ',');
	}
	
	/**
	 * 拼接联表查询的一个表名
	 * @access private
	 * @return string
	 */
	private static function fromToStr()
	{
		return '`' . self::$prefix . self::$from[0] . '`' . (empty(self::$from[1]) ? '' : ' AS ' . self::$from[1]) . ' ';
	}
	
	/**
	 * 拼接联表及条件
	 * @access private
	 * @return string
	 */
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
	
	/**
	 * 拼接查询条件
	 * @access private
	 * @return string
	 */
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
					$str .= $v[0] . ' ' . $v[1] . ' ' . $v[2] . ' ' . $v[3] . ' ';
				}
			}
			// 去掉可能存在的多余的与或逻辑
			$str = rtrim(rtrim(rtrim($str), 'AND'), 'OR');
		}
		return $str;
	}
	
	/**
	 * 拼接需要插入的数据
	 * @access private
	 * @return string
	 */
	private static function insertDataToStr()
	{
		$str = '';
		foreach(self::$data as $v)
		{
			$str .= '(' . implode(',', $v) . '),';
		}
		return rtrim($str, ',');
	}
	
	/**
	 * 拼接需要更新的数据
	 * @access private
	 * @return string
	 */
	private static function updateDateToStr()
	{
		$str = '';
		foreach(self::$data as $v)
		{
			foreach(self::$field as $kk => $vv)
			{
				// 处理数据
				$data = !empty($v[$kk]) ? $v[$kk] : '\'\'';
				if(!empty($data) && self::chkOperation($data))
				{
					$data = '`' . $vv[0] . '`' . trim($data, ',');
				}
				$str .= '`' . $vv[0] . '`=' . $data . ',';
			}
		}
		return rtrim($str, ',');
	}
	
	/**
	 * 判断是否带运算符
	 * @access private
	 * @return string
	 */
	private static function chkOperation($str)
	{
		return strpos($str, '+') === 0 || strpos($str, '-') === 0 || strpos($str, '*') === 0 || strpos($str, '/') === 0 || strpos($str, '%') === 0 ? true : false;
	}
	
	/**
	 * 拼接分组
	 * @access private
	 * @return string
	 */
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
	
	/**
	 * 拼接排序方式
	 * @access private
	 * @return string
	 */
	private static function orderToStr()
	{
		$str = '';
		if(!empty(self::$order))
		{
			$str .= ' ORDER BY ' . implode(',', self::$order);
		}
		return $str;
	}
	
	/**
	 * 拼接查询限制
	 * @access private
	 * @return string
	 */
	private static function limitToStr()
	{
		$str = '';
		if(!empty(self::$limit))
		{
			$str .= ' LIMIT ' . self::$limit;
		}
		return $str;
	}
	
	/**
	 * 执行语句查询
	 * @access private
	 * @param  string    $sql   执行指定查询语句
	 * @return string
	 */
	private static function query($sql)
	{
		if(z::$configure['app_debug'])
		{
			self::$debug[] = $sql;
		}
		self::clean();
		// 不启用查询分析或SQL语句不是查询语句时，直接返回查询结果
		if(!self::$useExplain || strpos(strtoupper(trim($sql)), 'SELECT') !== 0)
		{
			return self::$conn->query($sql);
		}
		// 启用查询分析时，优先分析执行时间，若小于
		$startTime = microtime(true);
		$result = self::$conn->query($sql);
		$usedTime = sprintf('%.5f', microtime(true) - $startTime);
		if($usedTime > self::$maxTime)
		{
			$esql = 'EXPLAIN ' . $sql;
			$explain = self::$conn->query($esql);
			if($explain !== false)
			{
				$row = z::$configure['pdo_connect'] ? $explain->fetch(PDO::FETCH_ASSOC) : $explain->fetch_assoc();
				if(isset($row['type']) && in_array(strtolower($row['type']), array('index', 'all')))
				{
			    	$content  = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ';
					$content .= $row['type'] . ' ';
					$content .= 'times:' . $usedTime . ' ';
					$content .= $sql;
					logs::init()->save('slowQueryLog', $content);
				}
			}
		}
		return $result;
	}
	
	/**
	 * 执行事务处理
	 * @access private
	 * @param  string    $sql   执行指定查询语句
	 * @return string
	 */
	private static function transaction($sql)
	{
		$status = true;
		if(z::$configure['app_debug'])
		{
			self::$debug[] = $sql;
		}
		self::clean();
		if(z::$configure['pdo_connect'])
		{
			try
			{
				// 开启事务，关闭自动提交
				$conn->beginTransaction();
				// 执行预处理语句，返回受影响行数
				$conn->exec($sql);
				// 提交事务并回到自动提交模式
				$conn->commit();
			}
			catch (Exception $e)
			{
				$status = false;
				// 回滚
				$conn->rollBack();
				// 关闭事务
				$conn->setAttribute(PDO::ATTR_AUTOCOMMIT,0);
			}
		}
		else
		{
			// 关闭自动提交
			$conn->autocommit(0);
			$result = $conn->query($sql);
			// 获得受影响函数
			$lines = $conn->affected_rows;
			if($result && $lines)
			{
				// 提交事务
				$conn->commit();
			}
			else
			{
				$status = false;
				// 回滚
				$conn->rollback();
			}
			// 开启自动提交
			$conn->autocommit(1);
		}
		return $status;
	}
	
	/**
	 * 执行查询
	 * @access public
	 * @return result
	 */
	public function select()
	{
		// 支持联表查询
		$fragment = !empty(self::$from) && !empty(self::$join) ? self::fromToStr() . self::joinToStr() : self::tableToStr();
		$sql = 'SELECT ' . self::fieldToStr() . ' FROM ' . $fragment . self::whereToStr() . self::groupToStr() . self::orderToStr() . self::limitToStr();
		return self::query($sql);
	}
	
	/**
	 * 执行新增
	 * @access public
	 * @param  boolean  $useTransaction  是否使用事务处理
	 * @return 最近添加的自增ID/boolean
	 */
	public function insert($useTransaction = true)
	{
		$sql = 'INSERT INTO ' . self::tableToStr() . '(' . self::fieldToStr() . ') VALUES ' . self::insertDataToStr();
		$result = $useTransaction ? self::transaction($sql) : self::query($sql);
		if($result)
		{
			$tmpId = z::$configure['pdo_connect'] ? self::$conn->lastInsertId() : self::$conn->insert_id;
			// 某些表不存在自增ID的情况下返回true
			return $tmpId ? $tmpId : true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 执行更新
	 * @access public
	 * @param  boolean  $useTransaction  是否使用事务处理
	 * @return result
	 */
	public function update($useTransaction = true)
	{
		$sql = 'UPDATE ' . self::tableToStr() . ' SET ' . self::updateDateToStr() . self::whereToStr();
		return $useTransaction ? self::transaction($sql) : self::query($sql);
	}
	
	/**
	 * 执行删除
	 * @access public
	 * @param  boolean  $useTransaction  是否使用事务处理
	 * @return result
	 */
	public function delete($useTransaction = true)
	{
		// 支持联表删除
		$fragment1 = !empty(self::$from) && !empty(self::$join) ? self::fieldToStr() : '';
		$fragment2 = !empty(self::$from) && !empty(self::$join) ? self::fromToStr() . self::joinToStr() : self::tableToStr();
		$sql = 'DELETE ' . $fragment1 . ' FROM ' . $fragment2 . self::whereToStr();
		return $useTransaction ? self::transaction($sql) : self::query($sql);
	}
	
	/**
	 * 取得一个数据
	 * @access public
	 * @param  string          $sql   可选，执行指定查询语句
	 * @return string/boolean
	 */
	public function getOne($sql = null)
	{
		$result = $sql ? self::query($sql) : $this->select();
		if($result !== false)
		{
			$row = z::$configure['pdo_connect'] ? $result->fetch(PDO::FETCH_NUM) : $result->fetch_row();
			return $row !== false ? $row[0] : '';
		}
		return false;
	}
	
	/**
	 * 取得一列数据
	 * @access public
	 * @param  string         $sql   可选，执行指定查询语句
	 * @return array/boolean
	 */
	public function getCol($sql = null)
	{
		$result = $sql ? self::query($sql) : $this->select();
		if($result !== false)
		{
			$array = array();
			while($row = z::$configure['pdo_connect'] ? $result->fetch(PDO::FETCH_NUM) : $result->fetch_row())
			{
				$array[] = $row[0];
			}
			return $array;
		}
		return false;
	}
	
	/**
	 * 取得一行数据
	 * @access public
	 * @param  string         $sql   可选，执行指定查询语句
	 * @return array/boolean
	 */
	public function getRow($sql = null)
	{
		$result = $sql ? self::query($sql) : $this->select();
		if($result !== false)
		{
			return z::$configure['pdo_connect'] ? $result->fetch(PDO::FETCH_ASSOC) : $result->fetch_assoc();
		}
		return false;
	}
	
	/**
	 * 取得全部数据
	 * @access public
	 * @param  string         $sql   可选，执行指定查询语句
	 * @return array/boolean
	 */
	public function getAll($sql = null)
	{
		$result = $sql ? self::query($sql) : $this->select();
		if($result !== false)
		{
			$array = array();
			if(z::$configure['pdo_connect'])
			{
				$array = $result->fetchAll(PDO::FETCH_ASSOC);
			}
			else
			{
				while($row = $result->fetch_assoc())
				{
					$array[] = $row;
				}
			}
			return $array;
		}
		return false;
	}
	
	/**
	 * 读取指定数据库下的表的字段名以及类型
	 * @access public
	 * @param  string  $table   表名
	 * @param  string  $dbname  数据库名（NULL时使用类属性中保存的数据库名）
	 * @return array
	 */
	public function getFieldNameAndType($table, $dbname = null)
	{
		$dbname = $dbname ? $dbname : self::$dbname;
		$sql = "SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='" . $dbname . "' AND TABLE_NAME='" . $table . "'";
		return $this->getRow($sql);
	}
	
	/**
	 * 聚合查询
	 * @access private
	 * @return number/boolean
	 */
	private function gether($type, $field = '*')
	{
		// 支持联表查询
		$fragment = !empty(self::$from) && !empty(self::$join) ? self::fromToStr() . self::joinToStr() : self::tableToStr();
		$sql = 'SELECT ' . strtoupper($type) . '(' . $field . ') FROM ' . $fragment . self::whereToStr() . self::groupToStr();
		return $this->getOne($sql);
	}
	
	/**
	 * 取得数量
	 * @access public
	 * @return number
	 */
	public function count()
	{
		return $this->gether('count');
	}
	
	/**
	 * 判断是否存在
	 * @access public
	 * @return boolean
	 */
	public function has()
	{
		return !!$this->gether('count');
	}
	
	/**
	 * 取得最大值
	 * @access public
	 * @return number
	 */
	public function max($field)
	{
		return $this->gether('max', $field);
	}
	
	/**
	 * 取得最小值
	 * @access public
	 * @return number
	 */
	public function min($field)
	{
		return $this->gether('min', $field);
	}
	
	/**
	 * 取得平均数
	 * @access public
	 * @return number
	 */
	public function avg($field)
	{
		return $this->gether('avg', $field);
	}
	
	/**
	 * 取得总和
	 * @access public
	 * @return number
	 */
	public function sum($field)
	{
		return $this->gether('sum', $field);
	}
	
	/**
	 * 调试查询语句
	 * @access public
	 */
	public static function debug()
	{
		echo '<pre>';
		print_r(self::$debug);
		exit(0);
	}
	
	/**
	 * 清空查询记录 (包括表名、联表、字段、条件、分组、排序、数据、限制)
	 */
	private static function clean()
	{
		self::$table = array();
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
	
	/**
	 * 释放连接
	 * @access public
	 */
	public function close()
	{
		if(z::$configure['pdo_connect'])
		{
			self::$conn = null;
		}
		else
		{
			self::$conn->close();
		}
	}
	
	/**
	 * 修正条件
	 * @access private
	 * @param  array   $arr   条件数组
	 * @return array/false
	 */
	private static function fixCondition($arr)
	{
		$actionRule = array('>', '=', '<', '<>', '!=', '!>', '!<', '=>', '=<', '>=', '<=', 'in', 'not in', 'like', 'regexp', 'IN', 'NOT IN', 'LIKE', 'REGEXP');
		$speRule = array('in', 'not in', 'IN', 'NOT IN');
		// 二维数组且长度大于2，即字段名、操作符、值是必须的，以及操作符的合法性
		if(is_array($arr) && count($arr) > 2 && in_array($arr[1], $actionRule))
		{
			// 修正字段名
			fixField($arr[0]);
			// 修正操作符为大写标准
			$arr[1] = strtoupper($arr[1]);
			// 修正字符串类型的值
			$arr[2] = in_array($arr[1], $speRule) ? ('(' . $arr[2] .  ')') : (is_numeric($arr[2]) ? $arr[2] : "'" . $arr[2] . "'");
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
	 * 修正字段名
	 * @access private
	 * @param  string    $field    字段名
	 * @return string
	 */
	private static function fixField($field)
	{
		if(strpos($field, '.') !== false)
		{
			$tmp = explode('.', $field);
			$field = $tmp[0] . '.' . ($tmp[1] !== '*' ? '`' . $tmp[1] . '`' : $tmp[1]);
		}
		elseif($field !== '*')
		{
			$field = '`' . $field . '`';
		}
		return $field;
	}
}
