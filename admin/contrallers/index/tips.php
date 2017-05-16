<?php

namespace contrallers\index;

use z\core\Router as router;

class tips extends \admin\adminContraller
{
	// 操作成功
	public function success()
	{
		return self::render('success');
	}
	
	// 操作失败
	public function fail()
	{
		return self::render('fail');
	}
	
	// 重复提交
	public function repeat()
	{
		return self::render('repeat');
	}
}
