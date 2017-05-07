<?php

namespace z\basic;

use \z\core\Model as Model;

class arc_category
{
	private static $db;
	private static function chkDB()
	{
		if(!isset(self::$db))
		{
			self::$db = Model::init()->table('arc_category');
		}
	}
	
	// 获取指定id的所有子分类
	public static function getAllCategory($parent_id = 0)
	{
		self::chkDB();
		$res = self::getNextCategory($parent_id);
		if($res)
		{
			foreach($res as $k => $v)
			{
				$res[$k]['children'] = self::getAllCategory($v['id']);
			}
		}
		return $res;
	}
	
	// 获取指定id的直属子分类
	public static function getNextCategory($parent_id = 0)
	{
		self::chkDB();
		return self::$db->field(array('id', 'name', 'static', 'sort'))->where(array('parent_id', '=', $parent_id))->select();
	}
}
