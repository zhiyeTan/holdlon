<?php

namespace z\basic;

use \z\core\Medoo as Model;

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
	
	public static function hasAccount($account)
	{
		self::checkDB();
		return self::$db->count(array('account' => $account));
	}
	
	public static function chkPassword($account, $password)
	{
		$password = md5($password);
		self::checkDB();
		echo self::$db->count(array('account' => $account, 'password' => $password));
		exit;
		return self::$db->count(array('account' => $account, 'password' => $password));
	}
}
