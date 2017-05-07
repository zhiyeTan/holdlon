<?php

namespace contrallers\article;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;

use z\basic\arc_category as arcc;

class arc_cls extends \admin\adminContraller
{
	public function arc_cls_list()
	{
		$model = array();
		$model['colName'] = self::getColName();
		return self::render('arc_cls_list', $model);
	}
	public function arc_cls_add()
	{
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			$model = $_POST['form'];
			// 验证令牌
			if(validate::is('token'))
			{
				// 这里继续验证表单元素的合法性
				echo 1;exit;
			}
			else
			{
				// 这里是重复提交了
				$model['message'] = '请勿重复提交!';
			}
		}
		else
		{
			// 加载表单初始化数据
			$model['token'] = session::getToken();
		}
		
		// 获得分类列表
		$cls = arcc::getAllCategory();
		/*/
		echo '<pre>';
		print_r($cls);
		exit;
		//*/
		
		// 栏目名称
		$model['colName'] = self::getColName();
		
		return self::render('arc_cls_addNedit', $model);
	}
	public function arc_cls_edit()
	{
		$model = array();
		$model['colName'] = self::getColName();
		return self::render('arc_cls_addNedit', $model);
	}
	public function arc_cls_delete()
	{
		
	}
}
