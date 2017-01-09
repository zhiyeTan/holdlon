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
			self::$db = Model::table('admin');
		}
	}
	
	public static function hasAccount($account)
	{
		self::checkDB();
		return self::$db->where(array('account', '=', $account))->has();
	}
	
	public static function chkPassword($account, $password)
	{
		self::checkDB();
		return self::$db->where(array(array('account', '=', $account), array('password', '=', md5($password))))->has();
	}
}
