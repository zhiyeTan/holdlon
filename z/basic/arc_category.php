<?php

namespace z\basic;

use \z\core\Model as Model;

class arc_category
{
	private static $db;
	private static $_instance;
	private function __construct()
	{
		if(!isset(self::$db))
		{
			self::$db = Model::init()->table('arc_category');
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
	
	// 获取指定id的所有子分类
	public function getAllCategory($parent_id = 0, $level = 0)
	{
		$res = self::getNextCategory($parent_id, $level);
		if($res)
		{
			$level++;
			foreach($res as $k => $v)
			{
				$res[$k]['children'] = self::getAllCategory($v['id'], $level);
			}
		}
		return $res;
	}
	
	// 获取指定id的直属子分类
	public function getNextCategory($parent_id = 0, $level = 0)
	{
		$res = self::$db->field(array('id', 'name', 'status', 'sort'))->where(array('parent_id', '=', $parent_id))->getAll();
		foreach($res as $k => $v)
		{
			$res[$k]['level'] = $level;
		}
		return $res;
	}
	
	// 获取指定分类信息
	public function getInfo($id)
	{
		return self::$db->where(array('id', '=', $id))->getRow();
	}
	
	// 查询状态
	public function getStatus($id)
	{
		return self::$db->field('status')->where(array('id', '=', $id))->getOne();
	}
	
	// 添加分类
	public function add($data)
	{
		return self::$db->field(array_keys($data))->data(array_values($data))->insert();
	}
	
	// 修改分类
	public function edit($id, $data)
	{
		return self::$db->field(array_keys($data))->data(array_values($data))->where(array('id', '=', $id))->update();
	}
	
	// 删除分类
	public function delete($id)
	{
		return self::$db->field('status')->data(2)->where(array('id', '=', $id))->update();
	}
	
	// 真实删除
	public function realDelete($id)
	{
		return self::$db->where(array('id', '=', $id))->delete();
	}
}
