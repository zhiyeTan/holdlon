<?php

namespace z\basic;

use \z\core\Model as Model;

class admin
{
	private static $db;
	private static function checkDB()
	{
		if(!isset(self::$db))
		{
			self::$db = Model::setTable('admin');
		}
	}
	
	// C U R D
	public static function C()
	{
		self::checkDB();
	}
	
	public static function U()
	{
		self::checkDB();
	}
	
	public static function R()
	{
		self::checkDB();
		return self::$db->get(array('account', 'name', 'tel'));
	}
	
	public static function D()
	{
		self::checkDB();
	}
}
