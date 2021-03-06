<?php

namespace controllers\index;

use z\core\model as model;
use z\core\response as response;

class indexApi extends \z\core\controller
{
	/**
	 * 主方法（固定）
	 */
	public function main()
	{
		//$list = model::init()->table('articles')->getAll();
		//*
		$list = array(
			0 => array(0=>'a1', 1=>'b1', 's'=>'c1', 'child'=>array()),
			1 => array(0=>'a2', 1=>'b2', 's'=>'c2', 'child'=>array('aaa', 'bbb', 'ccc')),
			2 => array(0=>'a3', 1=>'b3', 's'=>'c3', 'child'=>array()),
			4 => array(0=>'a4', 1=>'b4', 's'=>'c4', 'child'=>array())
		);
		//*/
		response::setContentType('json')->setContent($list);
	}
	
	/**
	 * 延迟执行（可移除）
	 * 
	 * 主要目的是把不涉及响应数据的操作延后执行，以提高响应速度
	 * 同时、由于使用了静态缓存，一些统计类操作也应该放到此方法中
	 */
	public function delay(){}
}