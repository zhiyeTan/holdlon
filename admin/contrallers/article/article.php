<?php

namespace contrallers\article;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;

use z\basic\admin as admin;

class article extends \admin\adminContraller
{
	public function arc_list()
	{
		$model = array();
		$model['colName'] = self::getColName();
		return self::render('arc_list', $model);
	}
	public function arc_add()
	{
		$model = array();
		$model['colName'] = self::getColName();
		return self::render('arc_list', $model);
	}
	public function arc_edit()
	{
		$model = array();
		$model['colName'] = self::getColName();
		return self::render('arc_list', $model);
	}
	public function arc_delete()
	{
		
	}
}
