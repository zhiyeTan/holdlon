<?php

namespace contrallers\article;

use z\core\Router as router;
use z\core\Session as session;
use z\core\Validate as validate;
use z\core\Upload as upload;

use z\basic\arc_category as arcc;
use z\basic\articles as arcs;

class article extends \admin\adminContraller
{
	public function arc_list()
	{
		$model = array();
		$model['colName'] = self::getColName();
		$res = arcs::init()->getAdmList(0);
		$urlData = array(
			'e'		=> $_GET['e'],
			'm'		=> 'article',
			'c'		=> 'article'
		);
		foreach($res as $k => $v)
		{
			$res[$k]['status'] = $v['status'] ? ($v['status'] == 1 ? '显示' : '已删除') : '隐藏';
			$urlData['id'] = $v['id'];
			$urlData['a'] = 'arc_edit';
			$res[$k]['urlEdit'] = router::create($urlData);
			$urlData['a'] = 'arc_delete';
			$res[$k]['urlDelete'] = router::create($urlData);
		}
		$model['list'] = $res;
		return self::render('arc_list', $model);
	}
	public function arc_add()
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
				if($model['title'] && $model['cat_id'])
				{
					// 获取数据
					$newData = $model;
					unset($newData['token']);
					// 转换HTML实体
					$newData['content'] = htmlspecialchars($newData['content']);
					// 赋值时间
					$newData['addtime'] = time();
					$imgs = upload::uploadFileBatch($_FILES, true, true);
					$newData['imgurl'] = $imgs['imgurl'][0];
					// hasimg 是否有封面  由是否有上传图片判断
					if($newData['imgurl'])
					{
						$newData['hasimg'] = 1;
					}
					if(arcs::init()->add($newData))
					{
						zHeader(self::tipsUrl());
					}
				}
				else
				{
					// TODO 验证失败
					zHeader(self::tipsUrl(1));
				}
			}
			else
			{
				// 这里是重复提交了
				zHeader(self::tipsUrl(2));
			}
		}
		else
		{
			// 加载表单初始化数据
			$model['token'] = session::getToken();
		}
		// 栏目名称
		$model['colName'] = self::getColName();
		// 获得分类列表
		$model['category'] = zInfiniteClass(arcc::init()->getAllCategory());
		return self::render('arc_addNedit', $model);
	}
	public function arc_edit()
	{
		$arcs = arcs::init();
		$model = array();
		// 验证表单是否合法
		if(validate::is('form') && validate::is('formToken'))
		{
			$model = $_POST['form'];
			// 验证令牌
			if(validate::is('token'))
			{
				// hasimg 是否有封面  由是否有上传图片判断
				// 这里继续验证表单元素的合法性
				if($model['title'] && $model['cat_id'])
				{
					// 获取数据
					$newData = $model;
					unset($newData['token']);
					// 检查勾选项
					$newData['status'] = isset($newData['status']) ? $newData['status'] : 0;
					$newData['comment'] = isset($newData['comment']) ? $newData['comment'] : 0;
					$newData['is_new'] = isset($newData['is_new']) ? $newData['is_new'] : 0;
					$newData['is_hot'] = isset($newData['is_hot']) ? $newData['is_hot'] : 0;
					$newData['is_top'] = isset($newData['is_top']) ? $newData['is_top'] : 0;
					$newData['is_push'] = isset($newData['is_push']) ? $newData['is_push'] : 0;
					$newData['is_best'] = isset($newData['is_best']) ? $newData['is_best'] : 0;
					// 赋值时间
					$newData['edittime'] = time();
					// 转换HTML实体
					$newData['content'] = htmlspecialchars($newData['content']);
					// 检查是否有上传
					if(!empty($_FILES['imgurl']))
					{
						$imgs = upload::uploadFileBatch($_FILES, true, true);
						$newData['imgurl'] = $imgs['imgurl'][0];
						$newData['hasimg'] = 1;
					}
					else
					{
						$newData['imgurl'] = 0;
					}
					if($arcs->edit($_GET['id'], $newData))
					{
						zHeader(self::tipsUrl());
					}
				}
				else
				{
					// TODO 验证失败
					zHeader(self::tipsUrl(1));
				}
			}
			else
			{
				zHeader(self::tipsUrl(2));
			}
		}
		else
		{
			// 加载表单初始化数据
			$model = $arcs->getInfo($_GET['id']);
			$model['token'] = session::getToken();
		}
		// 栏目名称
		$model['colName'] = self::getColName();
		// 获得分类列表
		$model['category'] = zInfiniteClass(arcc::init()->getAllCategory());
		return self::render('arc_addNedit', $model);
	}
	public function arc_delete()
	{
		$arcs = arcs::init();
		// 当状态软删除时则进行真实删除，否则软删除
		$res = $arcs->getStatus($_GET['id']) != 2 ? $arcs->delete($_GET['id']) : $arcs->realDelete($_GET['id']);
		$s = $res ? '0' : '1';
		zHeader(self::tipsUrl($s));
	}
}
