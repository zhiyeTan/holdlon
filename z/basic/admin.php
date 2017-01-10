<?php

namespace z\basic;

use \z\core\Model as Model;

class admin
{
	private static $db;
	private static function chkDB()
	{
		if(!isset(self::$db))
		{
			self::$db = Model::init()->table('admin');
		}
	}
	
	public static function hasAccount($account)
	{
		self::chkDB();
		return self::$db->where(array('account', '=', $account))->has();
	}
	
	public static function chkPassword($account, $password)
	{
		self::chkDB();
		return self::$db->where(array(array('account', '=', $account), array('password', '=', md5($password))))->has();
	}
}
