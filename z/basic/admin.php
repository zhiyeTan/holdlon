<?php

namespace z\basic;

use \z\core\Model as Model;

class admin
{
	private static $db;
	private static $_instance;
	private function __construct()
	{
		if(!isset(self::$db))
		{
			self::$db = Model::init()->table('admin');
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
	
	public static function hasAccount($account)
	{
		return self::$db->where(array('account', '=', $account))->has();
	}
	
	public static function chkPassword($account, $password)
	{
		return self::$db->where(array(array('account', '=', $account), array('password', '=', md5($password))))->has();
	}
}
