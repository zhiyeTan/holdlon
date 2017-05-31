<?php

namespace z\basic;

use \z\core\Model as Model;

class articles
{
	private static $db;
	private static $_instance;
	// 定义主表的字段名
	private static $mainFieldKey = array('title', 'brief_title', 'source', 'author', 'imgurl', 'abstract', 'content', 'keywords', 'description');
	private function __construct()
	{
		if(!isset(self::$db))
		{
			self::$db = Model::init()->table('articles');
		}
	}
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	// 获得指定分类的状态
	public function getStatus($id)
	{
		$res = self::$db->table('arc_status')->field('status')->where(array('arc_id', '=', $id))->getOne();
		// 重置表名
		self::$db->table('articles');
		return $res;
	}
	
	// 获取指定分类信息
	public function getInfo($id)
	{
		return self::$db
				 ->from('articles', 'a')
				 ->join(array('inner', 'arc_status', 'b', array('a.id', 'b.arc_id')))
				 ->where(array('a.id', '=', $id))
				 ->getRow();
	}
	
	// 后台获取分类列表
	public function getAdmList($id, $num = 10, $page = 1, $isDesc = true)
	{
		// 获取最大id并修正id
		$maxId = self::$db->max('id');
		$id = $id == 0 && $isDesc ? $maxId + 1 : $id;
		// 获得总记录数以及最大页数
		$count = self::$db->count();
		$maxPage = ceil($count / $num);
		// 修正page
		$page = $page < 1 ? 1 : $page;
		$page = $page > $maxPage ? $maxPage : $page;
		// 计算当前页可获取的数量
		$currNum = $page == $maxPage ? $count % $num : $num;
		// 尽量用最小的资源取得满足当前页数量的最大id
		$tmpId = $id;
		$res = 0;
		$step = $num + ceil($num / 5);
		do
		{
			if($isDesc)
			{
				$tmpId -= $step;
				$tmpId = $tmpId < 0 ? 0 : $tmpId;
				$condition = array(array('id', '>', $tmpId), array('id', '<', $id));
			}
			else
			{
				$tmpId += $step;
				$condition = array(array('id', '>', $id), array('id', '<', $tmpId));
			}
			$res = self::$db->where($condition)->count();
		}
		while($res < $currNum);
		return self::$db->field(array('a.id', 'a.title', 'c.name', 'b.status'))
				 ->from('articles', 'a')
				 ->join(array('inner', 'arc_status', 'b', array('a.id', 'b.arc_id')))
				 ->join(array('left', 'arc_category', 'c', array('b.cat_id', 'c.id')))
				 ->getAll();
		//\z\core\Model::debug();
	}
	
	// 添加文章
	public function add($data)
	{
		// 这里分为了2个表，需要分别插入
		$mainData = array();
		$elseData = array();
		foreach($data as $k => $v)
		{
			if(in_array($k, self::$mainFieldKey))
			{
				$mainData[$k] = $v;
			}
			else
			{
				$elseData[$k] = $v;
			}
		}
		$res1 = self::$db->field(array_keys($mainData))->data(array_values($mainData))->insert();
		$res2 = false;
		if($res1)
		{
			$elseData['arc_id'] = $res1;
			$res2 = self::$db->table('arc_status')->field(array_keys($elseData))->data(array_values($elseData))->insert();
		}
		// 重置表名
		self::$db->table('articles');
		return $res1 && $res2;
	}
	
	// 修改文章
	public function edit($id, $data)
	{
		// 这里分为了2个表，需要分别插入
		$mainData = array();
		$elseData = array();
		foreach($data as $k => $v)
		{
			if(in_array($k, self::$mainFieldKey))
			{
				$mainData[$k] = $v;
			}
			else
			{
				$elseData[$k] = $v;
			}
		}
		$res1 = self::$db->field(array_keys($mainData))->data(array_values($mainData))->where(array('id', '=', $id))->update();
		$res2 = self::$db->table('arc_status')->field(array_keys($elseData))->data(array_values($elseData))->where(array('arc_id', '=', $id))->update();
		// 重置表名
		self::$db->table('articles');
		return $res1 && $res2;
	}
	
	// 软删除
	public function delete($id)
	{
		$res = self::$db->table('arc_status')->field('status')->data(2)->where(array('arc_id', '=', $id))->update();
		// 重置表名
		self::$db->table('articles');
		return $res;
	}
	
	// 真实删除
	public function realDelete($id)
	{
		return self::$db->field(array('a.*', 'b.*'))->from('articles', 'a')->join(array('left', 'arc_status', 'b', array('a.id', 'b.arc_id')))->where(array('a.id', '=', $id))->delete();
	}
}
